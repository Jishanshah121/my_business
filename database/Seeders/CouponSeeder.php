<?php

declare(strict_types=1);

namespace Database\Seeders;

use Database\Seeder;

/**
 * DEMO coupons, one per validation path the CouponEngine has to handle:
 * percentage with a cap, flat amount, free shipping, first order, B2B-only,
 * and category-restricted. Every one of these is validated server-side.
 */
final class CouponSeeder extends Seeder
{
    public function order(): int
    {
        return 135;
    }

    public function run(): void
    {
        $now = date('Y-m-d H:i:s');
        $ends = date('Y-m-d H:i:s', strtotime('+1 year'));

        $coupons = [
            ['WELCOME10', 'Welcome offer — 10% off your first order',
             'percentage', 10.00, 500.00, 1000.00, 'first_order', null, 1],
            ['BULK500', 'Rs 500 off orders above Rs 10,000',
             'fixed_amount', 500.00, null, 10000.00, 'all', null, 3],
            ['FREESHIP', 'Free shipping on orders above Rs 1,500',
             'free_shipping', 0.00, null, 1500.00, 'all', null, 5],
            ['BIZ5', 'Business accounts — 5% off, up to Rs 2,000',
             'percentage', 5.00, 2000.00, 5000.00, 'b2b_only', null, 10],
            ['GOGREEN', '12% off the eco range',
             'percentage', 12.00, 1500.00, 2000.00, 'all', 'eco', 5],
        ];

        $ecoCategories = [];
        foreach ($this->pdo->query(
            "SELECT DISTINCT c.`id` FROM `categories` c
             JOIN `products` p ON p.`category_id` = c.`id`
             WHERE p.`is_eco` = 1"
        ) as $row) {
            $ecoCategories[] = (int) $row['id'];
        }

        foreach ($coupons as [$code, $name, $type, $value, $maxDiscount, $minCart, $audience, $restrictTo, $perUser]) {
            $couponId = $this->upsert('coupons', ['code' => $code], [
                'name'              => $name,
                'description'       => $name . ' (DEMO coupon)',
                'discount_type'     => $type,
                'discount_value'    => number_format($value, 2, '.', ''),
                'max_discount'      => $maxDiscount !== null ? number_format($maxDiscount, 2, '.', '') : null,
                'min_cart_value'    => number_format($minCart, 2, '.', ''),
                'applies_to'        => $restrictTo === 'eco' ? 'categories' : 'cart',
                'audience'          => $audience,
                'usage_limit_total' => null,
                'usage_limit_user'  => $perUser,
                'stackable'         => 0,
                'starts_at'         => $now,
                'ends_at'           => $ends,
                'is_active'         => 1,
            ]);

            if ($restrictTo === 'eco') {
                $this->pdo->prepare('DELETE FROM `coupon_conditions` WHERE `coupon_id` = ?')->execute([$couponId]);
                foreach ($ecoCategories as $categoryId) {
                    $this->upsert('coupon_conditions', [
                        'coupon_id'   => $couponId,
                        'target_type' => 'category',
                        'target_id'   => $categoryId,
                        'mode'        => 'include',
                    ], []);
                }
            }
        }

        $this->info(count($coupons) . ' DEMO coupons covering every validation path');
    }
}
