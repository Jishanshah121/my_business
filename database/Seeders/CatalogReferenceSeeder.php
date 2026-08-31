<?php

declare(strict_types=1);

namespace Database\Seeders;

use Database\Seeder;

/**
 * Brands, product attributes and tags — the reference data the catalog seeder
 * depends on.
 */
final class CatalogReferenceSeeder extends Seeder
{
    public function order(): int
    {
        return 75;
    }

    public function run(): void
    {
        // --- Brands. PLACEHOLDER house brands; replace with real suppliers. ---
        $brands = [
            ['SupplyKaro Essentials', 'Everyday value range across every category.'],
            ['SupplyKaro Pro',        'Heavier grades built for high-volume kitchens.'],
            ['SupplyKaro Earth',      'Compostable and plant-based range.'],
            ['Generic',               'Unbranded stock.'],
        ];
        foreach ($brands as $i => [$name, $description]) {
            $this->upsert('brands', ['slug' => $this->slug($name)], [
                'name'        => $name,
                'description' => $description,
                'is_active'   => 1,
                'sort_order'  => $i * 10,
            ]);
        }

        // --- Attributes. Filterable ones become facets on listing pages. ---
        $attributes = [
            ['capacity',      'Capacity',        'dimensions', 'number',  'ml',  1, 1],
            ['diameter',      'Diameter',        'dimensions', 'number',  'mm',  1, 1],
            ['sheet_size',    'Sheet Size',      'dimensions', 'text',    null,  1, 1],
            ['ply',           'Ply',             'material',   'number',  null,  1, 1],
            ['gsm',           'Paper Weight',    'material',   'number',  'gsm', 1, 1],
            ['material_type', 'Material',        'material',   'select',  null,  1, 1],
            ['colour',        'Colour',          'appearance', 'select',  null,  1, 0],
            ['microwave_safe','Microwave Safe',  'usage',      'boolean', null,  1, 1],
            ['freezer_safe',  'Freezer Safe',    'usage',      'boolean', null,  1, 1],
            ['leak_resistant','Leak Resistant',  'usage',      'boolean', null,  1, 1],
            ['oil_resistant', 'Oil Resistant',   'usage',      'boolean', null,  1, 1],
            ['food_grade',    'Food Grade',      'usage',      'boolean', null,  0, 1],
            ['compostable',   'Compostable',     'sustainability', 'boolean', null, 1, 1],
            ['recyclable',    'Recyclable',      'sustainability', 'boolean', null, 1, 1],
            ['reusable',      'Reusable',        'sustainability', 'boolean', null, 1, 0],
        ];
        foreach ($attributes as $i => [$code, $name, $group, $type, $unit, $filterable, $comparable]) {
            $attributeId = $this->upsert('attributes', ['code' => $code], [
                'name'          => $name,
                'group'         => $group,
                'input_type'    => $type,
                'unit'          => $unit,
                'is_filterable' => $filterable,
                'is_comparable' => $comparable,
                'show_on_page'  => 1,
                'sort_order'    => $i * 10,
            ]);

            if ($code === 'material_type') {
                foreach (['Paper', 'Bagasse', 'Areca Leaf', 'Birchwood', 'Bamboo', 'Plastic',
                          'Aluminium', 'Non-woven', 'Corrugated Board'] as $j => $value) {
                    $this->upsert('attribute_values',
                        ['attribute_id' => $attributeId, 'value' => $this->slug($value)],
                        ['label' => $value, 'sort_order' => $j * 10]);
                }
            }
            if ($code === 'colour') {
                foreach (['White', 'Kraft Brown', 'Natural', 'Silver', 'Black', 'Printed'] as $j => $value) {
                    $this->upsert('attribute_values',
                        ['attribute_id' => $attributeId, 'value' => $this->slug($value)],
                        ['label' => $value, 'sort_order' => $j * 10]);
                }
            }
        }

        // --- Tags. Eco lives here rather than in the category tree. ---
        $tags = [
            'general'  => ['Eco Friendly', 'Compostable', 'Biodegradable', 'Recyclable',
                           'Microwave Safe', 'Leak Proof', 'Bulk Pack', 'Best Value', 'New Arrival'],
            'occasion' => ['Wedding', 'Birthday', 'Party', 'Corporate', 'Catering', 'Pooja',
                           'Festival', 'Picnic', 'Food Stall'],
            'use_case' => ['Takeaway', 'Delivery', 'Dine In', 'Housekeeping', 'Pantry',
                           'Bakery', 'Beverage Service', 'Buffet'],
            'material' => ['Bagasse', 'Areca Leaf', 'Birchwood', 'Bamboo', 'Kraft Paper', 'Aluminium'],
        ];
        $tagCount = 0;
        foreach ($tags as $type => $names) {
            foreach ($names as $name) {
                $this->upsert('tags', ['slug' => $this->slug($name)], [
                    'name' => $name, 'type' => $type, 'is_active' => 1,
                ]);
                $tagCount++;
            }
        }

        $this->info(count($brands) . ' brands, ' . count($attributes) . " attributes, {$tagCount} tags");
    }
}
