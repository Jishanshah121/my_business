<?php

declare(strict_types=1);

namespace App\Services\Catalog;

use App\Support\Database;
use PDO;
use Throwable;

final class CatalogService
{
    private ?array $catalogCache = null;

    /**
     * Get all primary categories with metadata.
     */
    public function getCategories(): array
    {
        try {
            $pdo = Database::connection();
            $stmt = $pdo->query('SELECT * FROM `categories` WHERE `is_active` = 1 ORDER BY `sort_order` ASC');
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($rows)) {
                return $rows;
            }
        } catch (Throwable) {
            // Fallback to static seed data definition
        }

        return $this->getFallbackCategories();
    }

    /**
     * Get top-level primary categories with live product counts.
     */
    public function getPrimaryCategories(): array
    {
        try {
            $pdo = Database::connection();
            $stmt = $pdo->query('
                SELECT c.*, 
                    (SELECT COUNT(*) FROM `products` p WHERE (p.category_id = c.id OR p.category_id IN (SELECT sub.id FROM `categories` sub WHERE sub.parent_id = c.id)) AND p.deleted_at IS NULL AND p.status = "published") AS live_product_count
                FROM `categories` c 
                WHERE c.is_active = 1 AND (c.parent_id IS NULL OR c.parent_id = 0 OR c.depth = 0)
                ORDER BY c.sort_order ASC
            ');
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($rows)) {
                return $rows;
            }
        } catch (Throwable) {
            // Fallback
        }

        return $this->getCategories();
    }

    /**
     * Get a specific category by slug.
     */
    public function getCategoryBySlug(string $slug): ?array
    {
        $categories = $this->getCategories();
        foreach ($categories as $cat) {
            if ($cat['slug'] === $slug) {
                return $cat;
            }
        }
        return null;
    }

    /**
     * Get all products, optionally filtered by category (including subcategories) or flags.
     */
    public function getProducts(?string $categorySlug = null, array $filters = []): array
    {
        $all = $this->loadFullCatalog();

        if ($categorySlug !== null && $categorySlug !== '' && $categorySlug !== 'all') {
            $matchedSlugs = [$categorySlug];
            $targetCategory = $this->getCategoryBySlug($categorySlug);
            if ($targetCategory) {
                $targetId = (int) $targetCategory['id'];
                foreach ($this->getCategories() as $cat) {
                    if ((int)($cat['parent_id'] ?? 0) === $targetId) {
                        $matchedSlugs[] = $cat['slug'];
                    }
                }
            }
            $all = array_values(array_filter($all, fn($p) => in_array($p['category'] ?? '', $matchedSlugs, true)));
        }

        if (!empty($filters['material'])) {
            $mat = strtolower((string) $filters['material']);
            $all = array_values(array_filter($all, fn($p) => str_contains(strtolower($p['material'] ?? ''), $mat)));
        }

        if (!empty($filters['eco'])) {
            $all = array_values(array_filter($all, fn($p) => !empty($p['is_eco']) || in_array('recyclable', $p['flags'] ?? [])));
        }

        if (!empty($filters['search'])) {
            $q = strtolower(trim((string) $filters['search']));
            $all = array_values(array_filter($all, function($p) use ($q) {
                $name = strtolower($p['name'] ?? '');
                $keywords = strtolower($p['keywords'] ?? '');
                $mat = strtolower($p['material'] ?? '');
                $short = strtolower($p['short'] ?? '');
                return str_contains($name, $q) || str_contains($keywords, $q) || str_contains($mat, $q) || str_contains($short, $q);
            }));
        }

        return $all;
    }

    /**
     * Get featured products.
     */
    public function getFeaturedProducts(int $limit = 8): array
    {
        $all = $this->loadFullCatalog();
        $featured = array_filter($all, fn($p) => in_array('featured', $p['flags'] ?? []) || in_array('bestseller', $p['flags'] ?? []));
        return array_slice(array_values($featured), 0, $limit);
    }

    /**
     * Get bestsellers.
     */
    public function getBestsellers(int $limit = 8): array
    {
        $all = $this->loadFullCatalog();
        $bestsellers = array_filter($all, fn($p) => in_array('bestseller', $p['flags'] ?? []));
        if (count($bestsellers) < $limit) {
            $bestsellers = $all;
        }
        return array_slice(array_values($bestsellers), 0, $limit);
    }

    /**
     * Find single product by slug or sku_root.
     */
    public function getProduct(string $slugOrSku): ?array
    {
        $all = $this->loadFullCatalog();
        foreach ($all as $product) {
            if (($product['slug'] ?? '') === $slugOrSku || ($product['sku_root'] ?? '') === $slugOrSku) {
                return $product;
            }
        }
        return null;
    }

    /**
     * Load catalog data from database and compute display attributes.
     */
    public function loadFullCatalog(): array
    {
        if ($this->catalogCache !== null) {
            return $this->catalogCache;
        }

        $products = [];

        try {
            $pdo = Database::connection();
            $stmt = $pdo->query("
                SELECT 
                    p.id, p.uuid, p.name, p.slug, p.sku_root, p.material, p.unit_type, p.hsn_code,
                    p.short_description AS `short`, p.long_description AS `long`, p.search_keywords AS `keywords`,
                    p.is_eco, p.is_bestseller, p.is_featured, p.is_new_arrival, p.status,
                    c.slug AS category, c.name AS category_name,
                    b.name AS brand_name,
                    (SELECT img.path FROM `product_images` img WHERE img.product_id = p.id ORDER BY img.is_primary DESC, img.id DESC LIMIT 1) AS image
                FROM `products` p
                LEFT JOIN `categories` c ON p.category_id = c.id
                LEFT JOIN `brands` b ON p.brand_id = b.id
                WHERE p.deleted_at IS NULL AND p.status = 'published'
                ORDER BY p.sort_order ASC, p.id DESC
            ");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($rows)) {
                foreach ($rows as $row) {
                    $pid = (int) $row['id'];
                    
                    // Fetch variants
                    $varStmt = $pdo->prepare("
                        SELECT id, name, variant_code AS code, base_unit_price AS price, mrp_unit_price AS mrp, is_default
                        FROM `product_variants`
                        WHERE `product_id` = :pid AND `deleted_at` IS NULL
                        ORDER BY `is_default` DESC, `id` ASC
                    ");
                    $varStmt->execute(['pid' => $pid]);
                    $varRows = $varStmt->fetchAll(PDO::FETCH_ASSOC);

                    $variants = [];
                    foreach ($varRows as $vRow) {
                        $vid = (int) $vRow['id'];
                        $packStmt = $pdo->prepare("
                            SELECT pieces_per_pack, pack_label, base_price, mrp
                            FROM `variant_packs`
                            WHERE `variant_id` = :vid AND `deleted_at` IS NULL
                            ORDER BY `pieces_per_pack` ASC
                        ");
                        $packStmt->execute(['vid' => $vid]);
                        $packRows = $packStmt->fetchAll(PDO::FETCH_ASSOC);

                        $packs = [];
                        foreach ($packRows as $pr) {
                            $packs[] = [(int) $pr['pieces_per_pack'], $pr['pack_label']];
                        }

                        $variants[] = [
                            'name' => $vRow['name'],
                            'code' => $vRow['code'],
                            'price' => (float) $vRow['price'],
                            'mrp' => (float) ($vRow['mrp'] ?? ($vRow['price'] * 1.5)),
                            'default' => (bool) $vRow['is_default'],
                            'packs' => $packs,
                        ];
                    }

                    $flags = [];
                    if (!empty($row['is_bestseller'])) { $flags[] = 'bestseller'; }
                    if (!empty($row['is_featured'])) { $flags[] = 'featured'; }
                    if (!empty($row['is_eco'])) { $flags[] = 'eco'; }
                    if (!empty($row['is_new_arrival'])) { $flags[] = 'new'; }

                    $productItem = [
                        'sku_root'  => $row['sku_root'],
                        'name'      => $row['name'],
                        'slug'      => $row['slug'],
                        'category'  => $row['category'] ?? 'general',
                        'brand'     => $row['brand_name'] ?? 'SupplyKaro',
                        'material'  => $row['material'] ?? 'Food-grade',
                        'unit_type' => $row['unit_type'] ?? 'piece',
                        'short'     => $row['short'] ?? '',
                        'long'      => $row['long'] ?? '',
                        'keywords'  => $row['keywords'] ?? '',
                        'flags'     => $flags,
                        'image'     => $row['image'] ?? '/assets/images/products/butterpaper.png',
                        'variants'  => $variants,
                    ];

                    $products[] = $this->hydrateProduct($productItem);
                }
            }
        } catch (Throwable) {
            // DB fallback
        }

        $this->catalogCache = $products;
        return $products;
    }

    private function hydrateProduct(array $item): array
    {
        $skuRoot = $item['sku_root'] ?? 'SKU';
        $slug = !empty($item['slug']) ? (string)$item['slug'] : strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $item['name'] ?? $skuRoot), '-'));

        $variants = $item['variants'] ?? [];
        $defaultVariant = null;
        foreach ($variants as $v) {
            if (!empty($v['default'])) {
                $defaultVariant = $v;
                break;
            }
        }
        if ($defaultVariant === null && !empty($variants)) {
            $defaultVariant = $variants[0];
        }

        $defaultPack = null;
        if ($defaultVariant !== null && !empty($defaultVariant['packs'])) {
            $firstPack = $defaultVariant['packs'][0];
            $packQty = (int) $firstPack[0];
            $packLabel = (string) $firstPack[1];
            $unitPrice = (float) ($defaultVariant['price'] ?? 1.0);
            $mrpUnit = (float) ($defaultVariant['mrp'] ?? $unitPrice * 1.4);
            $packPrice = round($unitPrice * $packQty, 2);
            $packMrp = round($mrpUnit * $packQty, 2);
            $savingsPct = $packMrp > $packPrice ? (int) round((($packMrp - $packPrice) / $packMrp) * 100) : 0;

            $defaultPack = [
                'sku' => "{$skuRoot}-{$defaultVariant['code']}-{$packQty}",
                'qty' => $packQty,
                'label' => $packLabel,
                'unit_price' => $unitPrice,
                'pack_price' => $packPrice,
                'pack_mrp' => $packMrp,
                'savings_pct' => $savingsPct,
            ];
        }

        $isEco = in_array('recyclable', $item['flags'] ?? []) || 
                 in_array('compostable', $item['flags'] ?? []) ||
                 str_contains(strtolower($item['material'] ?? ''), 'kraft') ||
                 str_contains(strtolower($item['material'] ?? ''), 'bagasse') ||
                 str_contains(strtolower($item['material'] ?? ''), 'areca') ||
                 str_contains(strtolower($item['material'] ?? ''), 'birchwood');

        // Image placeholder mapping based on category
        $cat = (string) ($item['category'] ?? 'paper-cups');
        $imageMap = [
            'butter-paper' => '/assets/images/products/butterpaper.png',
            'paper-wrapping' => '/assets/images/products/butterpaper.png',
            'paper-cups' => 'https://images.unsplash.com/photo-1577937927133-66ef06acdf18?w=600&auto=format&fit=crop&q=80',
            'coffee-cups' => 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?w=600&auto=format&fit=crop&q=80',
            'cups-beverage' => 'https://images.unsplash.com/photo-1544787219-7f47ccb76574?w=600&auto=format&fit=crop&q=80',
            'plates' => 'https://images.unsplash.com/photo-1584269600464-37b1b58a9fe7?w=600&auto=format&fit=crop&q=80',
            'tableware' => 'https://images.unsplash.com/photo-1584269600464-37b1b58a9fe7?w=600&auto=format&fit=crop&q=80',
            'cutlery' => 'https://images.unsplash.com/photo-1615865417491-9941019fbc00?w=600&auto=format&fit=crop&q=80',
            'tissues-hygiene' => 'https://images.unsplash.com/photo-1583947215259-38e31be8751f?w=600&auto=format&fit=crop&q=80',
            'food-containers' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600&auto=format&fit=crop&q=80',
            'bags-bakery' => 'https://images.unsplash.com/photo-1530587191325-3db32d826c18?w=600&auto=format&fit=crop&q=80',
            'cleaning' => 'https://images.unsplash.com/photo-1585421514738-01798e348b17?w=600&auto=format&fit=crop&q=80',
            'safety-cleaning' => 'https://images.unsplash.com/photo-1584744982491-665216d95f8b?w=600&auto=format&fit=crop&q=80',
        ];

        $image = $item['image'] ?? ($imageMap[$cat] ?? 'https://images.unsplash.com/photo-1577937927133-66ef06acdf18?w=600&auto=format&fit=crop&q=80');

        return array_merge($item, [
            'slug' => $slug,
            'image' => $image,
            'is_eco' => $isEco,
            'default_variant' => $defaultVariant,
            'default_pack' => $defaultPack,
        ]);
    }

    private function getFallbackCategories(): array
    {
        return [
            ['id' => 1, 'name' => 'Cups & Beverage', 'slug' => 'cups-beverage', 'product_count' => 18, 'icon' => '', 'desc' => 'Tea, coffee, cold drink cups, paper straws & stirrers.'],
            ['id' => 2, 'name' => 'Plates & Tableware', 'slug' => 'tableware', 'product_count' => 14, 'icon' => '', 'desc' => 'Areca palm, sugarcane bagasse, paper plates & dona bowls.'],
            ['id' => 3, 'name' => 'Food Containers & Boxes', 'slug' => 'food-containers', 'product_count' => 16, 'icon' => '', 'desc' => 'Meal boxes, pizza boxes, burger clamshells & foil containers.'],
            ['id' => 4, 'name' => 'Cutlery & Utensils', 'slug' => 'cutlery', 'product_count' => 8, 'icon' => '', 'desc' => 'Birchwood wooden spoons, forks, knives & meal kits.'],
            ['id' => 5, 'name' => 'Tissues & Hygiene', 'slug' => 'tissues-hygiene', 'product_count' => 10, 'icon' => '', 'desc' => 'Pop-up napkins, dispenser tissues, M-fold towels & wet wipes.'],
            ['id' => 6, 'name' => 'Carry Bags & Bakery', 'slug' => 'bags-bakery', 'product_count' => 12, 'icon' => '', 'desc' => 'Brown kraft SOS bags, twisted handle bags & pastry boxes.'],
            ['id' => 7, 'name' => 'Wrapping & Foils', 'slug' => 'paper-wrapping', 'product_count' => 6, 'icon' => '', 'desc' => 'Butter paper rolls, burger wrap sheets & aluminium foil.'],
            ['id' => 8, 'name' => 'Garbage & Cleaning', 'slug' => 'cleaning', 'product_count' => 5, 'icon' => '', 'desc' => 'Heavy duty trash bags, nitrile gloves & chef caps.'],
        ];
    }
}
