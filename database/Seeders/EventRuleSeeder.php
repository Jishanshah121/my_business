<?php

declare(strict_types=1);

namespace Database\Seeders;

use Database\Seeder;

/**
 * The event calculator's rules — as data, so an admin retunes them without a
 * deploy (architecture section 4.4).
 *
 *     base   = guests x qty_per_guest
 *     padded = base x (1 + safety_margin_pct / 100)
 *     qty    = max( ceil_to_step(padded, rounding_step), min_qty )
 *
 * The wedding numbers below are calibrated to reproduce the worked example in
 * the brief exactly. For 500 guests:
 *
 *     dinner plates  500 x 1.0  x 1.20 = 600     -> 600
 *     side plates    500 x 1.0  x 1.20 = 600     -> 600
 *     bowls          500 x 1.0  x 1.20 = 600     -> 600
 *     spoons         500 x 1.2  x 1.08 = 648     -> 650  (step 10)
 *     forks          500 x 1.2  x 1.08 = 648     -> 650  (step 10)
 *     glasses        500 x 1.4  x 1.07 = 749     -> 750  (step 10)
 *     napkins        500 x 3.5  x 1.14 = 1,995   -> 2,000 (step 50)
 *     garbage bags   500 x 0.09 x 1.11 = 49.95   -> 50   (step 10)
 *
 * EventPlanServiceTest asserts these numbers, so changing them here without
 * updating that test is a deliberate act, not an accident.
 */
final class EventRuleSeeder extends Seeder
{
    public function order(): int
    {
        return 110;
    }

    /**
     * role => [qty_per_guest, safety_margin_pct, rounding_step, min_qty, is_required]
     *
     * @var array<string,array<string,array{0:float,1:float,2:int,3:int,4:bool}>>
     */
    private const RULES = [
        'wedding' => [
            'DINNER_PLATE'    => [1.00,  20.0, 10,  50, true],
            'SIDE_PLATE'      => [1.00,  20.0, 10,  50, true],
            'BOWL'            => [1.00,  20.0, 10,  50, true],
            'SPOON'           => [1.20,   8.0, 10,  50, true],
            'FORK'            => [1.20,   8.0, 10,  50, false],
            'GLASS'           => [1.40,   7.0, 10,  50, true],
            'CUP'             => [1.00,  10.0, 10,  50, false],
            'NAPKIN'          => [3.50,  14.0, 50, 100, true],
            'TISSUE'          => [0.02,  20.0,  1,   2, false],
            'GARBAGE_BAG'     => [0.09,  11.0, 10,  10, true],
            'SERVING_TRAY'    => [0.06,  15.0, 10,  10, false],
            'GLOVES'          => [0.10,  20.0, 10,  20, false],
        ],
        'birthday' => [
            'DINNER_PLATE'    => [1.00,  20.0, 10,  20, true],
            'SIDE_PLATE'      => [1.00,  25.0, 10,  20, false],
            'BOWL'            => [0.80,  20.0, 10,  20, true],
            'SPOON'           => [1.20,  10.0, 10,  20, true],
            'FORK'            => [0.80,  10.0, 10,  20, false],
            'GLASS'           => [1.60,  10.0, 10,  20, true],
            'NAPKIN'          => [4.00,  15.0, 50,  50, true],
            'STRAW'           => [1.50,  10.0, 50,  50, false],
            'ICE_CREAM_SPOON' => [1.10,  10.0, 10,  20, false],
            'GARBAGE_BAG'     => [0.12,  15.0, 10,  10, true],
        ],
        'corporate' => [
            'CUP'             => [3.00,  12.0, 50,  50, true],
            'GLASS'           => [1.60,  10.0, 10,  50, true],
            'SIDE_PLATE'      => [1.20,  15.0, 10,  50, true],
            'NAPKIN'          => [4.00,  12.0, 50, 100, true],
            'TISSUE'          => [0.04,  20.0,  1,   2, true],
            'SPOON'           => [1.00,  10.0, 10,  50, false],
            'GARBAGE_BAG'     => [0.08,  12.0, 10,  10, true],
        ],
        'catering' => [
            'DINNER_PLATE'    => [1.05,  18.0, 10,  50, true],
            'SIDE_PLATE'      => [1.00,  18.0, 10,  50, true],
            'BOWL'            => [1.20,  18.0, 10,  50, true],
            'SERVING_TRAY'    => [0.10,  15.0, 10,  10, false],
            'SPOON'           => [1.30,  10.0, 10,  50, true],
            'FORK'            => [1.10,  10.0, 10,  50, false],
            'GLASS'           => [1.50,   8.0, 10,  50, true],
            'NAPKIN'          => [3.50,  14.0, 50, 100, true],
            'GARBAGE_BAG'     => [0.12,  12.0, 10,  10, true],
            'GLOVES'          => [0.15,  20.0, 10,  20, false],
        ],
        'party' => [
            'DINNER_PLATE'    => [1.10,  20.0, 10,  20, true],
            'BOWL'            => [0.90,  20.0, 10,  20, false],
            'SPOON'           => [1.20,  10.0, 10,  20, true],
            'GLASS'           => [1.80,  10.0, 10,  20, true],
            'NAPKIN'          => [4.50,  15.0, 50,  50, true],
            'STRAW'           => [1.50,  10.0, 50,  50, false],
            'GARBAGE_BAG'     => [0.12,  15.0, 10,  10, true],
        ],
        'pooja' => [
            'DONA'            => [2.00,  20.0, 50,  50, true],
            'SIDE_PLATE'      => [1.00,  20.0, 10,  50, true],
            'BOWL'            => [0.80,  20.0, 10,  50, false],
            'GLASS'           => [1.20,  10.0, 10,  50, true],
            'SPOON'           => [1.00,  10.0, 10,  50, false],
            'NAPKIN'          => [2.50,  15.0, 50, 100, true],
            'GARBAGE_BAG'     => [0.08,  12.0, 10,  10, true],
        ],
        'festival' => [
            'DINNER_PLATE'    => [1.10,  20.0, 10,  50, true],
            'DONA'            => [1.50,  20.0, 50,  50, false],
            'BOWL'            => [1.00,  20.0, 10,  50, true],
            'SPOON'           => [1.20,  10.0, 10,  50, true],
            'GLASS'           => [1.50,  10.0, 10,  50, true],
            'NAPKIN'          => [3.00,  15.0, 50, 100, true],
            'GARBAGE_BAG'     => [0.12,  15.0, 10,  10, true],
        ],
        'picnic' => [
            'DINNER_PLATE'    => [1.00,  25.0, 10,  20, true],
            'SPOON'           => [1.10,  15.0, 10,  20, true],
            'GLASS'           => [1.50,  15.0, 10,  20, true],
            'NAPKIN'          => [3.00,  20.0, 50,  50, true],
            'GARBAGE_BAG'     => [0.20,  20.0, 10,  10, true],
        ],
        'other' => [
            'DINNER_PLATE'    => [1.00,  20.0, 10,  20, true],
            'BOWL'            => [0.80,  20.0, 10,  20, false],
            'SPOON'           => [1.10,  10.0, 10,  20, true],
            'GLASS'           => [1.40,  10.0, 10,  20, true],
            'NAPKIN'          => [3.00,  15.0, 50,  50, true],
            'GARBAGE_BAG'     => [0.10,  15.0, 10,  10, true],
        ],
    ];

    public function run(): void
    {
        $roles = [];
        foreach ($this->pdo->query('SELECT `id`, `code` FROM `item_roles`') as $row) {
            $roles[$row['code']] = (int) $row['id'];
        }

        $ruleCount = 0;
        foreach (self::RULES as $eventType => $rules) {
            // meal_type and serving_style are 'any' at version 1: one rule set
            // covers every meal and every tier. Adding a dinner-specific set
            // later is a new row with a more specific match, not a migration.
            $setId = $this->upsert(
                'event_rule_sets',
                ['event_type' => $eventType, 'meal_type' => 'any', 'serving_style' => 'any', 'version' => 1],
                [
                    'name'      => ucfirst(str_replace('_', ' ', $eventType)) . ' — standard quantities',
                    'is_active' => 1,
                    'notes'     => 'DEMO defaults. Tune per role in Admin > Event Kits once you have real event data.',
                ]
            );

            $priority = 0;
            foreach ($rules as $roleCode => [$perGuest, $margin, $step, $minQty, $required]) {
                if (!isset($roles[$roleCode])) {
                    continue;
                }
                $this->upsert(
                    'event_rules',
                    ['rule_set_id' => $setId, 'item_role_id' => $roles[$roleCode]],
                    [
                        'qty_per_guest'     => number_format($perGuest, 3, '.', ''),
                        'safety_margin_pct' => number_format($margin, 2, '.', ''),
                        'min_qty'           => $minQty,
                        'rounding_step'     => $step,
                        'is_required'       => (int) $required,
                        'priority'          => $priority,
                        'is_active'         => 1,
                    ]
                );
                $priority += 10;
                $ruleCount++;
            }
        }

        $this->info(count(self::RULES) . " rule sets, {$ruleCount} per-role rules (version 1)");
        $this->info('Wedding set reproduces the brief\'s 500-guest example exactly');
    }
}
