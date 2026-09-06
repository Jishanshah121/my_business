<?php 
/** 
 * @var App\Support\View $view 
 * @var array $categories
 * @var array $products
 * @var string $searchQuery
 * @var string $material
 * @var bool $ecoOnly
 */ 
$view->extend('layouts/app'); 
$view->start('content'); 
?>

<div class="wrap" style="padding-top: 32px; padding-bottom: 64px;">
  <!-- Breadcrumb & Header -->
  <div class="flex items-center gap-2 small muted" style="margin-bottom: 12px;">
    <a href="/">Home</a> <span>/</span> <span>Catalog</span>
    <?php if (!empty($searchQuery)): ?>
      <span>/</span> <span style="color: var(--ink);">Search: "<?= $view->e($searchQuery) ?>"</span>
    <?php endif; ?>
  </div>

  <div class="flex items-end justify-between flex-wrap gap-4" style="margin-bottom: 28px;">
    <div>
      <h1><?= !empty($searchQuery) ? 'Search Results: "' . $view->e($searchQuery) . '"' : 'All Packaging &amp; Tableware Catalog' ?></h1>
      <p class="muted small"><?= count($products) ?> sellable products found across India warehouse stock.</p>
    </div>

    <!-- Quick Eco Filter & Sort -->
    <div class="flex items-center gap-2">
      <a href="/categories?eco=1" class="btn <?= $ecoOnly ? 'btn-primary' : 'btn-secondary' ?> btn-sm flex items-center gap-1">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/><path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/>
        </svg>
        <span>Eco-Certified Only</span>
      </a>
    </div>
  </div>

  <!-- Category Quick Badges (Primary Top-Level Categories) -->
  <div class="flex items-center gap-2 flex-wrap" style="margin-bottom: 32px;">
    <a href="/categories" class="badge <?= empty($material) && !$ecoOnly ? 'badge-pine' : 'badge-neutral' ?>" style="padding: 6px 14px; font-size: 0.82rem;">
      All Products
    </a>
    <?php foreach ($categories as $cat): ?>
      <?php if (empty($cat['parent_id']) || $cat['parent_id'] == 0): ?>
        <a href="/category/<?= $view->e($cat['slug']) ?>" class="badge badge-neutral" style="padding: 6px 14px; font-size: 0.82rem;">
          <?= $view->e($cat['name']) ?>
        </a>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>

  <!-- Main Grid of Products -->
  <?php if (empty($products)): ?>
    <div class="card center" style="padding: 64px 20px;">
      <div style="margin-bottom: 16px;">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--border-dark)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
        </svg>
      </div>
      <h3>No products matched your search</h3>
      <p class="muted" style="margin-bottom: 20px;">Try searching for generic terms like "cups", "plates", "boxes", or "napkins".</p>
      <a href="/categories" class="btn btn-primary">View All Products</a>
    </div>
  <?php else: ?>
    <div class="grid grid-4">
      <?php foreach ($products as $product): ?>
        <?= $view->include('components/product-card', [
          'product' => $product,
          'view'    => $view,
        ]) ?>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php $view->stop(); ?>
