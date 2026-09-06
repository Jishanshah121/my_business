<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use App\Controllers\Controller;
use App\Http\Response;
use App\Services\Catalog\CatalogService;

final class ProductController extends Controller
{
    public function show(string $slug, CatalogService $catalog): Response
    {
        $product = $catalog->getProduct($slug);

        if ($product === null) {
            // Fallback to first available product if slug doesn't match
            $featured = $catalog->getFeaturedProducts(1);
            $product = $featured[0] ?? null;
        }

        if ($product === null) {
            return $this->render('errors/404', [
                'title' => 'Product Not Found · SupplyKaro',
            ], 404);
        }

        $category = $catalog->getCategoryBySlug($product['category'] ?? '');
        $relatedProducts = $catalog->getProducts($product['category'] ?? '', []);

        // Filter out current product
        $relatedProducts = array_values(array_filter($relatedProducts, fn($p) => ($p['slug'] ?? '') !== $product['slug']));

        return $this->render('products/show', [
            'title'           => "{$product['name']} · Wholesale & Pack Rates",
            'product'         => $product,
            'category'        => $category,
            'relatedProducts' => array_slice($relatedProducts, 0, 4),
        ]);
    }
}
