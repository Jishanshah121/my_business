<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Support\Config;
use Database\Seeder;
use RuntimeException;

/**
 * Development accounts.
 *
 * REFUSES TO RUN when APP_ENV=production. The passwords below are published in
 * the README and are for local development only — never promote this seeder to
 * a live environment.
 */
final class AdminUserSeeder extends Seeder
{
    public function order(): int
    {
        return 130;
    }

    private const DEV_PASSWORD = 'SupplyKaro#Dev2026';

    public function run(): void
    {
        if (Config::get('app.env') === 'production') {
            throw new RuntimeException(
                'AdminUserSeeder must never run with APP_ENV=production — it creates accounts with a published password.'
            );
        }

        $groups = [];
        foreach ($this->pdo->query('SELECT `id`, `code` FROM `customer_groups`') as $row) {
            $groups[$row['code']] = (int) $row['id'];
        }
        $roles = [];
        foreach ($this->pdo->query('SELECT `id`, `code` FROM `roles`') as $row) {
            $roles[$row['code']] = (int) $row['id'];
        }
        $businessTypes = [];
        foreach ($this->pdo->query('SELECT `id`, `code` FROM `business_types`') as $row) {
            $businessTypes[$row['code']] = (int) $row['id'];
        }

        $hash = password_hash(
            self::DEV_PASSWORD,
            Config::get('security.password.algo'),
            Config::get('security.password.options')
        );

        $accounts = [
            ['admin@supplykaro.test',     'Jishan',  'Shah',    'super_admin',       'retail'],
            ['ops@supplykaro.test',       'Ops',     'Manager', 'order_manager',     'retail'],
            ['sales@supplykaro.test',     'Sales',   'Manager', 'sales_manager',     'retail'],
            ['stock@supplykaro.test',     'Stock',   'Manager', 'inventory_manager', 'retail'],
            ['customer@supplykaro.test',  'Demo',    'Customer','customer',          'retail'],
            ['cafe@supplykaro.test',      'Demo',    'Cafe',    'customer',          'b2b_standard'],
        ];

        foreach ($accounts as $i => [$email, $first, $last, $roleCode, $groupCode]) {
            $userId = $this->upsert('users', ['email' => $email], [
                'uuid'              => sprintf('%s-0000-4000-8000-%012d', substr(md5($email), 0, 8), $i + 1),
                'customer_group_id' => $groups[$groupCode],
                'first_name'        => $first,
                'last_name'         => $last,
                'phone'             => '9199000000' . str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT),
                'password_hash'     => $hash,
                'status'            => 'active',
                'email_verified_at' => date('Y-m-d H:i:s'),
                'marketing_opt_in'  => 0,
                'notes'             => 'DEMO ACCOUNT — development only',
            ]);

            if (isset($roles[$roleCode])) {
                $this->upsert('user_roles', ['user_id' => $userId, 'role_id' => $roles[$roleCode]], []);
            }

            // The demo cafe is an APPROVED business account, so B2B pricing,
            // quotes and the business dashboard can be exercised end to end.
            if ($email === 'cafe@supplykaro.test') {
                $this->upsert('business_profiles', ['user_id' => $userId], [
                    'business_type_id'       => $businessTypes['cafe'] ?? null,
                    'company_name'           => 'Demo Cafe Pvt Ltd',
                    'contact_person'         => 'Demo Cafe',
                    'contact_phone'          => '919900000006',
                    'contact_email'          => $email,
                    'gstin'                  => '27AAACD1234E1ZK',
                    'pan'                    => 'AAACD1234E',
                    'expected_monthly_spend' => 45000.00,
                    'status'                 => 'approved',
                    'approved_at'            => date('Y-m-d H:i:s'),
                    'credit_enabled'         => 0,
                    'credit_limit'           => 0.00,
                    'credit_days'            => 0,
                ]);

                $this->upsert('addresses', ['user_id' => $userId, 'label' => 'Cafe'], [
                    'type'                => 'both',
                    'contact_name'        => 'Demo Cafe',
                    'contact_phone'       => '919900000006',
                    'company_name'        => 'Demo Cafe Pvt Ltd',
                    'gstin'               => '27AAACD1234E1ZK',
                    'line1'               => 'Shop 4, PLACEHOLDER Building',
                    'line2'               => 'Linking Road',
                    'city'                => 'Mumbai',
                    'state_code'          => '27',
                    'state_name'          => 'Maharashtra',
                    'pincode'             => '400050',
                    'is_default_billing'  => 1,
                    'is_default_shipping' => 1,
                ]);
            }
        }

        $this->info(count($accounts) . ' DEMO accounts — password: ' . self::DEV_PASSWORD);
        $this->info('Development only. Delete these before any deployment.');
    }
}
