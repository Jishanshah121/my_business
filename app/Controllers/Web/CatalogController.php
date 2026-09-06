<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use App\Controllers\Controller;
use App\Http\Response;
use App\Services\Catalog\CatalogService;

final class CatalogController extends Controller
{
    public function index(CatalogService $catalog): Response
    {
        $searchQuery = $_GET['q'] ?? '';
        $material = $_GET['material'] ?? '';
        $ecoOnly = !empty($_GET['eco']);

        $categories = $catalog->getCategories();
        $products = $catalog->getProducts(null, [
            'search'   => $searchQuery,
            'material' => $material,
            'eco'      => $ecoOnly,
        ]);

        return $this->render('categories/index', [
            'title'       => !empty($searchQuery) ? "Search: {$searchQuery} · SupplyKaro" : 'All Categories & Products · SupplyKaro',
            'categories'  => $categories,
            'products'    => $products,
            'searchQuery' => $searchQuery,
            'material'    => $material,
            'ecoOnly'     => $ecoOnly,
        ]);
    }

    public function show(string $slug, CatalogService $catalog): Response
    {
        $category = $catalog->getCategoryBySlug($slug);
        $categories = $catalog->getCategories();
        $material = $_GET['material'] ?? '';
        $ecoOnly = !empty($_GET['eco']);

        $products = $catalog->getProducts($slug, [
            'material' => $material,
            'eco'      => $ecoOnly,
        ]);

        $categoryName = $category['name'] ?? ucwords(str_replace('-', ' ', $slug));

        return $this->render('categories/show', [
            'title'        => "{$categoryName} · Wholesale Packaging & Tableware",
            'category'     => $category,
            'categorySlug' => $slug,
            'categoryName' => $categoryName,
            'categories'   => $categories,
            'products'     => $products,
            'material'     => $material,
            'ecoOnly'      => $ecoOnly,
        ]);
    }
}
