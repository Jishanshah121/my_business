<?php

declare(strict_types=1);

namespace Database\Seeders;

use Database\Seeder;

/**
 * Roles and permissions. Authorisation is always checked against a permission
 * code, never a role name, so an admin can reshape roles without a deploy.
 */
final class RbacSeeder extends Seeder
{
    public function order(): int
    {
        return 20;
    }

    /** @var array<string,array<string,string>> group => code => label */
    private const PERMISSIONS = [
        'catalog' => [
            'products.view' => 'View products', 'products.create' => 'Create products',
            'products.edit' => 'Edit products', 'products.delete' => 'Archive products',
            'products.publish' => 'Publish and unpublish products',
            'categories.manage' => 'Manage categories', 'brands.manage' => 'Manage brands',
            'attributes.manage' => 'Manage attributes and tags',
        ],
        'pricing' => [
            'pricing.view' => 'View pricing rules', 'pricing.tiers' => 'Manage quantity price tiers',
            'pricing.overrides' => 'Manage customer contract prices',
            'pricing.campaigns' => 'Manage campaign price rules',
            'pricing.inspect' => 'Use the price inspector',
            'coupons.manage' => 'Manage coupons',
        ],
        'inventory' => [
            'inventory.view' => 'View stock levels', 'inventory.adjust' => 'Adjust stock',
            'inventory.transfer' => 'Transfer between warehouses',
            'warehouses.manage' => 'Manage warehouses',
        ],
        'orders' => [
            'orders.view' => 'View orders', 'orders.edit' => 'Edit orders',
            'orders.fulfil' => 'Pack and ship orders', 'orders.cancel' => 'Cancel orders',
            'orders.refund' => 'Issue refunds', 'orders.invoice' => 'Generate invoices',
        ],
        'customers' => [
            'customers.view' => 'View customers', 'customers.edit' => 'Edit customers',
            'customers.impersonate' => 'Sign in as a customer',
            'b2b.view' => 'View business accounts', 'b2b.approve' => 'Approve business accounts',
            'b2b.credit' => 'Set credit limits and terms',
        ],
        'quotes' => [
            'quotes.view' => 'View quotes', 'quotes.create' => 'Create quotes',
            'quotes.price' => 'Change quoted prices', 'quotes.send' => 'Send quotes to customers',
            'quotes.convert' => 'Convert a quote to an order',
        ],
        'merchandising' => [
            'bundles.manage' => 'Manage bundles and kits',
            'occasions.manage' => 'Manage occasions and business pages',
            'events.manage' => 'Manage event calculator rules',
            'reviews.moderate' => 'Moderate reviews',
            'homepage.manage' => 'Manage homepage sections',
        ],
        'system' => [
            'reports.view' => 'View reports and analytics',
            'settings.manage' => 'Change settings',
            'users.manage' => 'Manage staff accounts',
            'roles.manage' => 'Manage roles and permissions',
            'logs.view' => 'View admin and system logs',
        ],
    ];

    /** @var array<string,array{0:string,1:string,2:bool,3:list<string>|string}> */
    private const ROLES = [
        'super_admin' => ['Super Admin', 'Unrestricted access to everything', true, '*'],
        'admin' => ['Admin', 'Full operational access, no role or settings changes', true, [
            'catalog.*', 'pricing.*', 'inventory.*', 'orders.*', 'customers.*', 'quotes.*',
            'merchandising.*', 'reports.view', 'logs.view',
        ]],
        'inventory_manager' => ['Inventory Manager', 'Stock, warehouses and product data', true, [
            'products.view', 'products.edit', 'inventory.*', 'reports.view',
        ]],
        'order_manager' => ['Order Manager', 'Order processing and fulfilment', true, [
            'orders.*', 'customers.view', 'inventory.view', 'products.view', 'reports.view',
        ]],
        'sales_manager' => ['Sales Manager', 'Quotes, pricing and customer relationships', true, [
            'quotes.*', 'pricing.view', 'pricing.tiers', 'pricing.overrides', 'pricing.inspect',
            'coupons.manage', 'customers.view', 'customers.edit', 'b2b.view', 'orders.view',
            'products.view', 'reports.view',
        ]],
        'b2b_manager' => ['B2B Manager', 'Business account approval and credit', true, [
            'b2b.*', 'customers.view', 'customers.edit', 'quotes.view', 'quotes.create',
            'quotes.price', 'quotes.send', 'orders.view', 'reports.view',
        ]],
        'customer' => ['Customer', 'Storefront customer — no admin access', false, []],
    ];

    public function run(): void
    {
        $permissionIds = [];
        foreach (self::PERMISSIONS as $group => $permissions) {
            foreach ($permissions as $code => $name) {
                $permissionIds[$code] = $this->upsert(
                    'permissions',
                    ['code' => $code],
                    ['group' => $group, 'name' => $name]
                );
            }
        }

        foreach (self::ROLES as $code => [$name, $description, $isStaff, $grants]) {
            $roleId = $this->upsert(
                'roles',
                ['code' => $code],
                ['name' => $name, 'description' => $description, 'is_staff' => (int) $isStaff, 'is_system' => 1]
            );

            $codes = $grants === '*' ? array_keys($permissionIds) : $this->expand($grants);

            $this->pdo->prepare('DELETE FROM `role_permissions` WHERE `role_id` = ?')->execute([$roleId]);

            foreach ($codes as $permissionCode) {
                if (!isset($permissionIds[$permissionCode])) {
                    continue;
                }
                $this->insert('role_permissions', [
                    'role_id'       => $roleId,
                    'permission_id' => $permissionIds[$permissionCode],
                ]);
            }
        }

        $this->info(count($permissionIds) . ' permissions, ' . count(self::ROLES) . ' roles');
    }

    /**
     * Expand "group.*" shorthand into concrete permission codes.
     *
     * @param list<string> $grants
     * @return list<string>
     */
    private function expand(array $grants): array
    {
        $expanded = [];
        foreach ($grants as $grant) {
            if (!str_ends_with($grant, '.*')) {
                $expanded[] = $grant;
                continue;
            }

            $group = substr($grant, 0, -2);
            // "catalog.*" means the catalog group; "orders.*" also matches the
            // orders group. Both forms resolve through the group map.
            foreach (self::PERMISSIONS[$group] ?? [] as $code => $_) {
                $expanded[] = $code;
            }
            // Also allow prefix form, e.g. "b2b.*" -> b2b.view, b2b.approve
            foreach (self::PERMISSIONS as $codes) {
                foreach ($codes as $code => $_) {
                    if (str_starts_with($code, $group . '.')) {
                        $expanded[] = $code;
                    }
                }
            }
        }

        return array_values(array_unique($expanded));
    }
}
