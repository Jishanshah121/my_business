<?php

declare(strict_types=1);

namespace Database\Seeders;

use Database\Seeder;

/**
 * The category tree. Note that "Eco Collection" is deliberately NOT a category:
 * eco products live in their real category (a bagasse plate is a plate) and are
 * surfaced by the is_eco flag and the eco tag. Making it a category would force
 * every eco product to be filed in the wrong place or duplicated.
 */
final class CategorySeeder extends Seeder
{
    public function order(): int
    {
        return 70;
    }

    /** @var array<string,array{0:string,1:string,2:array<string,string>}> */
    private const TREE = [
        'Tableware' => [
            'Plates, bowls, trays and everything a meal is served on.',
            'Disposable Plates, Bowls & Trays',
            [
                'Paper Plates'       => 'Coated and uncoated paper plates in every standard size.',
                'Bagasse Plates'     => 'Sugarcane-fibre plates: sturdy, compostable, microwave safe.',
                'Areca Leaf Plates'  => 'Natural fallen areca palm leaf, pressed into plates and bowls.',
                'Disposable Bowls'   => 'Paper and bagasse bowls for curry, dessert and soup.',
                'Dona & Thali'       => 'Traditional dona, pattal and compartment thali.',
                'Serving Trays'      => 'Meal trays and compartment trays for catering and canteens.',
            ],
        ],
        'Cups & Beverage' => [
            'Everything the drink goes in, and everything that goes with it.',
            'Paper Cups, Coffee Cups & Beverage Supplies',
            [
                'Paper Cups'       => 'Plain and printed paper cups from cutting chai to 250 ml.',
                'Coffee Cups'      => 'Ripple wall and double wall cups that stay comfortable to hold.',
                'Cold Drink Cups'  => 'PET and paper cold cups for juice, shakes and iced coffee.',
                'Cup Lids'         => 'Sipper, flat and dome lids matched to every cup diameter.',
                'Straws & Stirrers'=> 'Paper straws, bubble tea straws and wooden stirrers.',
                'Cup Carriers'     => 'Two and four cup carriers for takeaway and delivery.',
            ],
        ],
        'Cutlery' => [
            'Spoons, forks, knives and stirrers in plastic, wood and bamboo.',
            'Disposable Spoons, Forks & Cutlery',
            [
                'Spoons'            => 'Tea, dessert, meal and serving spoons.',
                'Forks & Knives'    => 'Forks, knives and sporks for meals and events.',
                'Wooden Cutlery'    => 'Birchwood cutlery — compostable and splinter free.',
                'Ice Cream Spoons'  => 'Small wooden and plastic spoons for desserts.',
            ],
        ],
        'Food Packaging' => [
            'Boxes and containers built for takeaway and delivery.',
            'Takeaway Boxes, Containers & Food Packaging',
            [
                'Burger & Sandwich Boxes' => 'Kraft clamshells and wedges that keep a burger together.',
                'Pizza Boxes'             => 'Corrugated pizza boxes in every standard diameter.',
                'Meal Containers'         => 'Biryani containers, meal boxes and leak-resistant tubs.',
                'Aluminium Containers'    => 'Foil containers with lids for curry, biryani and bakes.',
                'Clamshell Containers'    => 'Hinged bagasse and plastic clamshells.',
                'Noodle & Momo Boxes'     => 'Chinese takeaway boxes and momo trays.',
            ],
        ],
        'Tissues & Hygiene' => [
            'Napkins, tissues, towels and personal protective supplies.',
            'Napkins, Tissue Paper & Hygiene Supplies',
            [
                'Table Napkins'   => 'One, two and three ply napkins for tables and counters.',
                'Facial Tissues'  => 'Box and soft-pack facial tissue.',
                'Kitchen Towels'  => 'Absorbent two-ply kitchen towel rolls.',
                'Toilet Rolls'    => 'Toilet tissue and jumbo rolls for washrooms.',
                'Wet Wipes'       => 'Sachet and canister wipes, plain and alcohol based.',
                'Gloves & Safety' => 'Gloves, caps, hair nets, aprons and masks.',
            ],
        ],
        'Bags & Wrapping' => [
            'Carry bags, food wrapping, foil, film and garbage bags.',
            'Paper Bags, Butter Paper, Foil & Garbage Bags',
            [
                'Paper Bags'        => 'Kraft carry bags with handles, in every takeaway size.',
                'Butter Paper'      => 'Greaseproof butter paper in sheets and rolls.',
                'Foil & Cling Film' => 'Aluminium foil rolls and food-grade cling film.',
                'Garbage Bags'      => 'Standard and biodegradable garbage bags by size.',
            ],
        ],
        'Bakery Supplies' => [
            'Everything a bakery needs after the oven.',
            'Cake Boxes, Boards & Bakery Packaging',
            [
                'Cake Boxes'        => 'Window and plain cake boxes by weight.',
                'Cake Boards'       => 'Laminated cake boards and drums.',
                'Cupcake Supplies'  => 'Cupcake boxes, inserts and paper liners.',
                'Bakery Bags'       => 'Grease-resistant bags for bread, cookies and snacks.',
            ],
        ],
        'Cleaning & Housekeeping' => [
            'Consumables that keep a kitchen and washroom running.',
            'Cleaning Supplies & Housekeeping Consumables',
            [
                'Cleaning Cloth'      => 'Microfibre cloth, scrub pads and wipes.',
                'Sanitiser & Cleaners'=> 'Hand sanitiser and surface cleaning products.',
                'Dispensers'          => 'Tissue, towel and sanitiser dispensers.',
            ],
        ],
    ];

    public function run(): void
    {
        $count = 0;
        $sort = 0;

        foreach (self::TREE as $parentName => [$description, $seoTitle, $children]) {
            $parentSlug = $this->slug($parentName);
            $parentId = $this->upsert('categories', ['slug' => $parentSlug], [
                'parent_id'       => null,
                'name'            => $parentName,
                'depth'           => 0,
                'description'     => $description,
                'seo_title'       => $seoTitle . ' | SupplyKaro',
                'seo_description' => $description,
                'sort_order'      => $sort,
                'is_active'       => 1,
                'show_in_nav'     => 1,
            ]);
            // Materialised path, so a subtree is one indexed range read.
            $this->pdo->prepare('UPDATE `categories` SET `path` = ? WHERE `id` = ?')
                ->execute(["/{$parentId}/", $parentId]);
            $count++;

            $childSort = 0;
            foreach ($children as $childName => $childDescription) {
                $childSlug = $this->slug($childName);
                $childId = $this->upsert('categories', ['slug' => $childSlug], [
                    'parent_id'       => $parentId,
                    'name'            => $childName,
                    'depth'           => 1,
                    'description'     => $childDescription,
                    'seo_title'       => "{$childName} — Buy Online in Bulk | SupplyKaro",
                    'seo_description' => $childDescription,
                    'sort_order'      => $childSort,
                    'is_active'       => 1,
                    'show_in_nav'     => 1,
                ]);
                $this->pdo->prepare('UPDATE `categories` SET `path` = ? WHERE `id` = ?')
                    ->execute(["/{$parentId}/{$childId}/", $childId]);
                $childSort += 10;
                $count++;
            }

            $sort += 10;
        }

        $this->info("{$count} categories (" . count(self::TREE) . ' top level)');
    }
}
