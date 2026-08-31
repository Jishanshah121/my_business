<?php

declare(strict_types=1);

namespace Database\Seeders;

use Database\Seeder;

/**
 * Doubles as the "Shop by Business" landing pages. `what_you_need` is the copy
 * that explains what this kind of buyer actually orders.
 */
final class BusinessTypeSeeder extends Seeder
{
    public function order(): int
    {
        return 40;
    }

    public function run(): void
    {
        $types = [
            ['restaurant', 'Restaurant', 'Dine-in and takeaway, covered end to end',
             'Meal containers, biryani boxes, cutlery, tissues, foil containers, carry bags and cleaning supplies — everything a full-service kitchen gets through in a week.'],
            ['cafe', 'Cafe', 'Cups, lids, sleeves and everything around the counter',
             'Ripple and double-wall coffee cups, lids, stirrers, carriers, napkins, butter paper and bakery bags, in pack sizes that suit a single counter.'],
            ['cloud_kitchen', 'Cloud Kitchen', 'Delivery-first packaging that survives the ride',
             'Leak-resistant containers, tamper-evident meal boxes, cutlery sets, carry bags and sealing film — chosen for delivery, not for display.'],
            ['caterer', 'Caterer', 'Bulk quantities for events of any size',
             'Plates, bowls, glasses, serving trays, catering spoons, napkins and garbage bags — priced by the thousand, delivered on the date you need them.'],
            ['hotel', 'Hotel', 'Housekeeping, banquet and in-room supplies',
             'Toilet rolls, facial tissue, hand towels, amenity packs, banquet disposables and housekeeping consumables on a monthly cycle.'],
            ['bakery', 'Bakery', 'Packaging that protects what you baked',
             'Cake boxes and boards, cupcake liners, butter paper, bakery bags, dessert containers and food-grade wrapping.'],
            ['office', 'Office', 'Pantry and washroom, restocked without the admin',
             'Paper cups, stirrers, tissues, toilet rolls, hand towels, garbage bags and sanitiser on a repeating monthly order.'],
            ['event_planner', 'Event Planner', 'One supplier for every event on your calendar',
             'Complete event kits sized to guest count, premium and eco serving ranges, and quantities calculated for you.'],
            ['retailer', 'Retailer', 'Shelf-ready stock at trade prices',
             'Fast-moving disposables in retail-friendly pack sizes, with quantity pricing that leaves you a margin.'],
            ['distributor', 'Distributor', 'Carton and pallet quantities',
             'Full-carton and multi-carton pricing, dedicated account management, and quotations for tender-sized orders.'],
            ['other', 'Other', 'Tell us what you need', 'Not sure which fits? Send us a bulk quote request and we will price it.'],
        ];

        foreach ($types as $i => [$code, $name, $tagline, $whatYouNeed]) {
            $this->upsert('business_types', ['code' => $code], [
                'name'            => $name,
                'slug'            => $this->slug($name),
                'tagline'         => $tagline,
                'what_you_need'   => $whatYouNeed,
                'seo_title'       => "Disposable & Packaging Supplies for {$name}s | SupplyKaro",
                'seo_description' => $tagline,
                'sort_order'      => $i * 10,
                'is_active'       => 1,
                'show_in_nav'     => $code === 'other' ? 0 : 1,
            ]);
        }

        $this->info(count($types) . ' business types');
    }
}
