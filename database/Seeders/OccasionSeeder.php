<?php

declare(strict_types=1);

namespace Database\Seeders;

use Database\Seeder;

final class OccasionSeeder extends Seeder
{
    public function order(): int
    {
        return 80;
    }

    public function run(): void
    {
        $occasions = [
            ['wedding',       'Wedding',        'From the mehendi to the reception, counted for you', 500],
            ['birthday',      'Birthday',       'Party supplies sized to the guest list',               30],
            ['house_party',   'House Party',    'Enough for the evening, nothing left over',            25],
            ['corporate',     'Corporate Event','Clean, professional service at any headcount',        100],
            ['catering',      'Catering',       'Bulk quantities for the kitchen that serves everyone', 250],
            ['pooja',         'Pooja & Prayer', 'Traditional dona, pattal and leaf plates',            100],
            ['festival',      'Festival',       'Diwali, Ganpati, Eid and everything in between',      200],
            ['picnic',        'Picnic',         'Light, packable and easy to carry out',                20],
            ['office_party',  'Office Party',   'Pantry-friendly quantities for the whole floor',       50],
            ['school_event',  'School Event',   'Safe, simple serving for large groups of children',   300],
            ['college_event', 'College Event',  'High volume, low cost, fast delivery',                400],
            ['food_stall',    'Food Stall',     'Fast-moving packaging for a stall or pop-up',         200],
        ];

        foreach ($occasions as $i => [$code, $name, $tagline, $guests]) {
            $this->upsert('occasions', ['code' => $code], [
                'name'                => $name,
                'slug'                => $this->slug($name),
                'tagline'             => $tagline,
                'description'         => $tagline,
                'default_guest_count' => $guests,
                'seo_title'           => "{$name} Disposable Supplies & Party Essentials | SupplyKaro",
                'seo_description'     => $tagline,
                'sort_order'          => $i * 10,
                'is_active'           => 1,
                'show_in_nav'         => $i < 6 ? 1 : 0,
            ]);
        }

        $this->info(count($occasions) . ' occasions');
    }
}
