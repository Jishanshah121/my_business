# DEMO / PLACEHOLDER CATALOG DATA

Everything in this directory is **placeholder seed data for development**.

* Prices are realistic Indian-market figures, quoted **exclusive of GST** (the
  storage convention — see ADR-004). They are not quotes from any supplier.
* HSN codes and GST rates reflect commonly applied classifications. **Have your
  CA confirm every HSN and rate before you invoice a real customer.**
* Product images are generated placeholders, not photography.

Every database row created from these files is written with `is_demo_data = 1`.
Remove the entire demo catalog with:

```
php bin/console db:wipe-demo
```

Replacing this with your real catalog requires **no application changes** —
only new rows. Keep the same array shape and the seeder will handle the rest.

## Array shape

```php
[
    'sku_root'  => 'PC-PLN',        // unique; child SKUs are SKU-ROOT-VARIANT-PACK
    'name'      => 'Plain White Paper Cup',
    'category'  => 'paper-cups',    // category slug, must already exist
    'hsn'       => '4823',
    'brand'     => 'supplykaro-essentials',
    'material'  => 'Food-grade paper',
    'unit_type' => 'piece',         // piece | roll | sheet | box | pack | set | canister
    'keywords'  => '...',           // search synonyms, including Hinglish trade terms
    'short'     => '...',
    'long'      => '...',
    'flags'     => ['bestseller', 'eco', ...],
    'tags'      => ['takeaway', ...],   // tag slugs
    'attrs'     => ['material_type' => 'paper', 'food_grade' => true, 'gsm' => 38],
    'variants'  => [
        [
            'name' => '90 ml', 'code' => '090',
            'price' => 0.38,            // LIST PRICE PER BASE UNIT, ex-GST
            'mrp'   => 0.60,
            'capacity' => 90, 'diameter' => 58, 'weight' => 1.9,
            'default' => true,
            'packs' => [[100, 'Pack of 100'], [1000, 'Carton of 1,000']],
        ],
    ],
]
```

Pack prices are **derived** as `pieces x price`. Volume discounts come from the
generated quantity tiers, never from a second discount baked into the pack —
otherwise a bulk buyer would be discounted twice.
