<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Http\Request;
use App\Http\Response;
use App\Services\Auth\AuthService;
use App\Services\Auth\Gate;
use App\Support\Csrf;
use App\Support\Database;
use App\Support\Logger;
use App\Support\Session\Session;
use App\Support\View;
use PDO;
use Throwable;

/**
 * Amazon / Flipkart / Meesho-style Catalog & Product Management Controller.
 * Manages 3-level catalog hierarchy: Products -> Variants -> Variant Packs + Images.
 */
final class ProductManagementController extends Controller
{
    public function __construct(
        View $view,
        Session $session,
        AuthService $auth,
        Gate $gate,
        Csrf $csrf,
        private readonly Logger $logger,
    ) {
        parent::__construct($view, $session, $auth, $gate, $csrf);
    }

    /**
     * Display the master catalog list with filters and search.
     */
    public function index(Request $request): Response
    {
        $pdo = Database::connection();
        $status = (string) $request->query('status', 'all');
        $categoryId = $request->query('category_id') ? (int) $request->query('category_id') : null;
        $search = trim((string) $request->query('q', ''));

        // Query counts
        $totalCount = (int) $pdo->query("SELECT COUNT(*) FROM `products` WHERE `deleted_at` IS NULL")->fetchColumn();
        $publishedCount = (int) $pdo->query("SELECT COUNT(*) FROM `products` WHERE `status` = 'published' AND `deleted_at` IS NULL")->fetchColumn();
        $draftCount = (int) $pdo->query("SELECT COUNT(*) FROM `products` WHERE `status` = 'draft' AND `deleted_at` IS NULL")->fetchColumn();
        $ecoCount = (int) $pdo->query("SELECT COUNT(*) FROM `products` WHERE `is_eco` = 1 AND `deleted_at` IS NULL")->fetchColumn();

        // Build product query with primary image and category name
        $sql = "
            SELECT 
                p.id, p.uuid, p.name, p.slug, p.sku_root, p.material, p.hsn_code, p.unit_type,
                p.is_eco, p.is_bestseller, p.is_featured, p.is_new_arrival, p.status, p.created_at,
                c.name AS category_name, c.slug AS category_slug,
                (SELECT img.path FROM `product_images` img WHERE img.product_id = p.id ORDER BY img.is_primary DESC, img.sort_order ASC LIMIT 1) AS image_path,
                (SELECT COUNT(*) FROM `product_variants` pv WHERE pv.product_id = p.id AND pv.deleted_at IS NULL) AS variant_count,
                (SELECT MIN(pv.base_unit_price) FROM `product_variants` pv WHERE pv.product_id = p.id AND pv.deleted_at IS NULL) AS min_price,
                (SELECT MAX(pv.base_unit_price) FROM `product_variants` pv WHERE pv.product_id = p.id AND pv.deleted_at IS NULL) AS max_price,
                (SELECT MIN(pv.mrp_unit_price) FROM `product_variants` pv WHERE pv.product_id = p.id AND pv.deleted_at IS NULL) AS min_mrp
            FROM `products` p
            LEFT JOIN `categories` c ON p.category_id = c.id
            WHERE p.deleted_at IS NULL
        ";

        $params = [];
        if ($status !== 'all' && in_array($status, ['published', 'draft', 'archived'], true)) {
            $sql .= " AND p.status = :status";
            $params['status'] = $status;
        }

        if ($categoryId !== null && $categoryId > 0) {
            $sql .= " AND p.category_id = :category_id";
            $params['category_id'] = $categoryId;
        }

        if ($search !== '') {
            $sql .= " AND (p.name LIKE :search OR p.sku_root LIKE :search OR p.hsn_code LIKE :search OR p.search_keywords LIKE :search)";
            $params['search'] = "%{$search}%";
        }

        $sql .= " ORDER BY p.id DESC LIMIT 100";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch categories for filter dropdown
        $categories = $pdo->query("SELECT id, name FROM `categories` WHERE `is_active` = 1 ORDER BY `sort_order` ASC")->fetchAll(PDO::FETCH_ASSOC);

        return $this->render('admin/products/index', [
            'title'      => 'Catalog & Products',
            'products'   => $products,
            'categories' => $categories,
            'status'     => $status,
            'selectedCategoryId' => $categoryId,
            'search'     => $search,
            'counts'     => [
                'total'     => $totalCount,
                'published' => $publishedCount,
                'draft'     => $draftCount,
                'eco'       => $ecoCount,
            ],
        ]);
    }

    /**
     * Show the Amazon / Flipkart listing creation studio.
     */
    public function create(): Response
    {
        $pdo = Database::connection();
        $categories = $pdo->query("SELECT id, name, slug FROM `categories` WHERE `is_active` = 1 ORDER BY `sort_order` ASC")->fetchAll(PDO::FETCH_ASSOC);
        $brands = $pdo->query("SELECT id, name FROM `brands` ORDER BY `name` ASC")->fetchAll(PDO::FETCH_ASSOC);

        return $this->render('admin/products/create', [
            'title'      => 'Add New Product Listing',
            'categories' => $categories,
            'brands'     => $brands,
        ]);
    }

    /**
     * Store a newly created product with its primary variant, packs, and gallery images.
     */
    public function store(Request $request): Response
    {
        $pdo = Database::connection();

        $name = trim((string) $request->post('name', ''));
        $categoryId = (int) $request->post('category_id'x, 0);
        $brandId = $request->post('brand_id') ? (int) $request->post('brand_id') : null;
        $skuRoot = strtoupper(trim((string) $request->post('sku_root', '')));
        $hsnCode = trim((string) $request->post('hsn_code', '4823'));
        $material = trim((string) $request->post('material', ''));
        $unitType = (string) $request->post('unit_type', 'piece');
        $shortDesc = trim((string) $request->post('short_description', ''));
        $longDesc = trim((string) $request->post('long_description', ''));
        $searchKeywords = trim((string) $request->post('search_keywords', ''));

        // Merchandising Flags
        $isEco = $request->post('is_eco') ? 1 : 0;
        $isBestseller = $request->post('is_bestseller') ? 1 : 0;
        $isFeatured = $request->post('is_featured') ? 1 : 0;
        $isNewArrival = $request->post('is_new_arrival') ? 1 : 0;
        $status = in_array($request->post('status'), ['published', 'draft'], true) ? $request->post('status') : 'published';

        // Pricing & Variant
        $variantName = trim((string) $request->post('variant_name', 'Standard'));
        $variantCode = strtoupper(trim((string) $request->post('variant_code', 'STD')));
        $baseUnitPrice = max(0.01, (float) $request->post('base_unit_price', 1.0));
        $mrpUnitPrice = (float) $request->post('mrp_unit_price', $baseUnitPrice * 1.5);

        // Pack sizes
        $pack1Qty = max(1, (int) $request->post('pack_1_qty', 50));
        $pack1Label = trim((string) $request->post('pack_1_label', "Pack of {$pack1Qty}"));
        $pack2Qty = (int) $request->post('pack_2_qty', 0);
        $pack2Label = trim((string) $request->post('pack_2_label', "Master Carton of {$pack2Qty}"));

        if ($name === '' || $categoryId <= 0) {
            $this->session->flash('error', 'Product name and category are required.');
            return $this->redirect('/admin/products/create');
        }

        if ($skuRoot === '') {
            $skuRoot = 'SKU-' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 6));
        }

        // Generate slug
        $baseSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
        $slug = $baseSlug;
        $counter = 1;
        while ((int) $pdo->query("SELECT COUNT(*) FROM `products` WHERE `slug` = " . $pdo->quote($slug))->fetchColumn() > 0) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        // Image Handling
        $imagePath = $this->handleImageUpload($slug) ?? '/assets/images/products/butterpaper.png';

        try {
            $pdo->beginTransaction();

            // 1. Insert Level 1 Product
            $uuid = bin2hex(random_bytes(16));
            $stmt = $pdo->prepare("
                INSERT INTO `products` (
                    `uuid`, `sku_root`, `category_id`, `brand_id`, `name`, `slug`,
                    `short_description`, `long_description`, `search_keywords`,
                    `material`, `unit_type`, `hsn_code`,
                    `is_eco`, `is_bestseller`, `is_featured`, `is_new_arrival`,
                    `status`, `published_at`, `created_at`, `updated_at`
                ) VALUES (
                    :uuid, :sku_root, :category_id, :brand_id, :name, :slug,
                    :short_desc, :long_desc, :search_keywords,
                    :material, :unit_type, :hsn_code,
                    :is_eco, :is_bestseller, :is_featured, :is_new_arrival,
                    :status, " . ($status === 'published' ? 'NOW()' : 'NULL') . ", NOW(), NOW()
                )
            ");

            $stmt->execute([
                'uuid'            => $uuid,
                'sku_root'        => $skuRoot,
                'category_id'     => $categoryId,
                'brand_id'        => $brandId,
                'name'            => $name,
                'slug'            => $slug,
                'short_desc'      => $shortDesc,
                'long_desc'       => $longDesc,
                'search_keywords' => $searchKeywords,
                'material'        => $material,
                'unit_type'       => $unitType,
                'hsn_code'        => $hsnCode,
                'is_eco'          => $isEco,
                'is_bestseller'   => $isBestseller,
                'is_featured'     => $isFeatured,
                'is_new_arrival'  => $isNewArrival,
                'status'          => $status,
            ]);

            $productId = (int) $pdo->lastInsertId();

            // 2. Insert Level 2 Variant
            $stmtVar = $pdo->prepare("
                INSERT INTO `product_variants` (
                    `product_id`, `name`, `variant_code`, `base_unit_price`, `mrp_unit_price`, `is_default`, `status`
                ) VALUES (
                    :product_id, :name, :code, :price, :mrp, 1, 'active'
                )
            ");
            $stmtVar->execute([
                'product_id' => $productId,
                'name'       => $variantName,
                'code'       => $variantCode,
                'price'      => $baseUnitPrice,
                'mrp'        => $mrpUnitPrice,
            ]);
            $variantId = (int) $pdo->lastInsertId();

            // 3. Insert Level 3 Variant Packs
            $stmtPack = $pdo->prepare("
                INSERT INTO `variant_packs` (
                    `variant_id`, `sku`, `pack_label`, `pieces_per_pack`, `base_price`, `mrp`, `is_default`, `status`
                ) VALUES (
                    :variant_id, :sku, :pack_label, :pieces, :base_price, :mrp, :is_default, 'active'
                )
            ");

            // Pack 1 (Sleeve / Small Pack)
            $stmtPack->execute([
                'variant_id' => $variantId,
                'sku'        => "{$skuRoot}-{$variantCode}-{$pack1Qty}",
                'pack_label' => $pack1Label,
                'pieces'     => $pack1Qty,
                'base_price' => round($baseUnitPrice * $pack1Qty, 2),
                'mrp'        => round($mrpUnitPrice * $pack1Qty, 2),
                'is_default' => 1,
            ]);

            // Pack 2 (Master Carton / Bulk Pack)
            if ($pack2Qty > 0) {
                $stmtPack->execute([
                    'variant_id' => $variantId,
                    'sku'        => "{$skuRoot}-{$variantCode}-{$pack2Qty}",
                    'pack_label' => $pack2Label,
                    'pieces'     => $pack2Qty,
                    'base_price' => round($baseUnitPrice * $pack2Qty, 2),
                    'mrp'        => round($mrpUnitPrice * $pack2Qty, 2),
                    'is_default' => 0,
                ]);
            }

            // 4. Insert Primary Product Image
            if ($imagePath !== '') {
                $stmtImg = $pdo->prepare("
                    INSERT INTO `product_images` (
                        `product_id`, `variant_id`, `path`, `alt_text`, `is_primary`, `sort_order`
                    ) VALUES (
                        :product_id, :variant_id, :path, :alt_text, 1, 0
                    )
                ");
                $stmtImg->execute([
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                    'path'       => $imagePath,
                    'alt_text'   => $name,
                ]);
            }

            $pdo->commit();
            $this->session->flash('success', "Product \"{$name}\" was successfully published to your catalog!");
        } catch (Throwable $e) {
            $pdo->rollBack();
            $this->logger->error('Failed to create product', ['error' => $e->getMessage()]);
            $this->session->flash('error', 'Error saving product: ' . $e->getMessage());
            return $this->redirect('/admin/products/create');
        }

        return $this->redirect('/admin/products');
    }

    /**
     * Show the edit studio for an existing product.
     */
    public function edit(int|string $id): Response
    {
        $id = (int) $id;
        $pdo = Database::connection();

        $stmt = $pdo->prepare("SELECT * FROM `products` WHERE `id` = :id AND `deleted_at` IS NULL");
        $stmt->execute(['id' => $id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            $this->session->flash('error', 'Product not found.');
            return $this->redirect('/admin/products');
        }

        $categories = $pdo->query("SELECT id, name FROM `categories` WHERE `is_active` = 1 ORDER BY `sort_order` ASC")->fetchAll(PDO::FETCH_ASSOC);
        $brands = $pdo->query("SELECT id, name FROM `brands` ORDER BY `name` ASC")->fetchAll(PDO::FETCH_ASSOC);

        $stmtVar = $pdo->prepare("SELECT * FROM `product_variants` WHERE `product_id` = :id AND `deleted_at` IS NULL ORDER BY `is_default` DESC LIMIT 1");
        $stmtVar->execute(['id' => $id]);
        $variant = $stmtVar->fetch(PDO::FETCH_ASSOC);

        $packs = [];
        if ($variant) {
            $stmtPacks = $pdo->prepare("SELECT * FROM `variant_packs` WHERE `variant_id` = :vid AND `deleted_at` IS NULL ORDER BY `pieces_per_pack` ASC");
            $stmtPacks->execute(['vid' => $variant['id']]);
            $packs = $stmtPacks->fetchAll(PDO::FETCH_ASSOC);
        }

        $stmtImg = $pdo->prepare("SELECT * FROM `product_images` WHERE `product_id` = :id ORDER BY `is_primary` DESC, `sort_order` ASC");
        $stmtImg->execute(['id' => $id]);
        $images = $stmtImg->fetchAll(PDO::FETCH_ASSOC);

        return $this->render('admin/products/edit', [
            'title'      => 'Edit Product · ' . $product['name'],
            'product'    => $product,
            'variant'    => $variant,
            'packs'      => $packs,
            'images'     => $images,
            'categories' => $categories,
            'brands'     => $brands,
        ]);
    }

    /**
     * Update an existing product.
     */
    public function update(int|string $id, Request $request): Response
    {
        $id = (int) $id;
        $pdo = Database::connection();

        $name = trim((string) $request->post('name', ''));
        $categoryId = (int) $request->post('category_id', 0);
        $brandId = $request->post('brand_id') ? (int) $request->post('brand_id') : null;
        $hsnCode = trim((string) $request->post('hsn_code', '4823'));
        $material = trim((string) $request->post('material', ''));
        $shortDesc = trim((string) $request->post('short_description', ''));
        $longDesc = trim((string) $request->post('long_description', ''));
        $searchKeywords = trim((string) $request->post('search_keywords', ''));
        $status = (string) $request->post('status', 'published');

        $isEco = $request->post('is_eco') ? 1 : 0;
        $isBestseller = $request->post('is_bestseller') ? 1 : 0;
        $isFeatured = $request->post('is_featured') ? 1 : 0;
        $isNewArrival = $request->post('is_new_arrival') ? 1 : 0;

        $baseUnitPrice = max(0.01, (float) $request->post('base_unit_price', 1.0));
        $mrpUnitPrice = (float) $request->post('mrp_unit_price', $baseUnitPrice * 1.5);

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                UPDATE `products` SET
                    `name` = :name, `category_id` = :category_id, `brand_id` = :brand_id,
                    `hsn_code` = :hsn_code, `material` = :material,
                    `short_description` = :short_desc, `long_description` = :long_desc,
                    `search_keywords` = :search_keywords, `is_eco` = :is_eco,
                    `is_bestseller` = :is_bestseller, `is_featured` = :is_featured,
                    `is_new_arrival` = :is_new_arrival, `status` = :status,
                    `updated_at` = NOW()
                WHERE `id` = :id
            ");
            $stmt->execute([
                'id'              => $id,
                'name'            => $name,
                'category_id'     => $categoryId,
                'brand_id'        => $brandId,
                'hsn_code'        => $hsnCode,
                'material'        => $material,
                'short_desc'      => $shortDesc,
                'long_desc'       => $longDesc,
                'search_keywords' => $searchKeywords,
                'is_eco'          => $isEco,
                'is_bestseller'   => $isBestseller,
                'is_featured'     => $isFeatured,
                'is_new_arrival'  => $isNewArrival,
                'status'          => $status,
            ]);

            // Update primary variant price
            $stmtVar = $pdo->prepare("
                UPDATE `product_variants` SET
                    `base_unit_price` = :price, `mrp_unit_price` = :mrp, `updated_at` = NOW()
                WHERE `product_id` = :id AND `is_default` = 1
            ");
            $stmtVar->execute([
                'id'    => $id,
                'price' => $baseUnitPrice,
                'mrp'   => $mrpUnitPrice,
            ]);

            // Check for new image upload
            $newImage = $this->handleImageUpload('product-' . $id);
            if ($newImage !== null && $newImage !== '') {
                $pdo->prepare("UPDATE `product_images` SET `is_primary` = 0 WHERE `product_id` = :id")->execute(['id' => $id]);
                $stmtImg = $pdo->prepare("
                    INSERT INTO `product_images` (`product_id`, `path`, `alt_text`, `is_primary`, `sort_order`)
                    VALUES (:id, :path, :alt, 1, 0)
                ");
                $stmtImg->execute(['id' => $id, 'path' => $newImage, 'alt' => $name]);
            }

            $pdo->commit();
            $this->session->flash('success', "Product \"{$name}\" updated successfully.");
        } catch (Throwable $e) {
            $pdo->rollBack();
            $this->logger->error('Failed to update product', ['error' => $e->getMessage()]);
            $this->session->flash('error', 'Update error: ' . $e->getMessage());
        }

        return $this->redirect('/admin/products');
    }

    /**
     * Archive/delete a product.
     */
    public function destroy(int|string $id, Request $request): Response
    {
        $id = (int) $id;
        $pdo = Database::connection();
        $stmt = $pdo->prepare("UPDATE `products` SET `status` = 'archived', `deleted_at` = NOW() WHERE `id` = :id");
        $stmt->execute(['id' => $id]);

        $this->session->flash('success', 'Product has been archived from your active catalog.');
        return $this->redirect('/admin/products');
    }

    /**
     * Helper to process local file upload.
     */
    private function handleImageUpload(string $slug): ?string
    {
        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            $tmpPath = $_FILES['image_file']['tmp_name'];
            $origName = $_FILES['image_file']['name'];
            $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'svg'], true)) {
                $uploadDir = dirname(__DIR__, 3) . '/public/assets/images/products';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $filename = $slug . '-' . time() . '.' . $ext;
                $destPath = $uploadDir . '/' . $filename;

                if (move_uploaded_file($tmpPath, $destPath)) {
                    return '/assets/images/products/' . $filename;
                }
            }
        }

        return null;
    }
}
