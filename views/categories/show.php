<?php 
/** 
 * @var App\Support\View $view 
 * @var array|null $category
 * @var string $categorySlug
 * @var string $categoryName
 * @var array $categories
 * @var array $products
 * @var string $material
 * @var bool $ecoOnly
 */ 
$view->extend('layouts/app'); 
$view->start('content'); 
?>

<div class="wrap" style="padding-top: 32px; padding-bottom: 64px;">
  <!-- Breadcrumb -->
  <div class="flex items-center gap-2 small muted" style="margin-bottom: 16px;">
    <a href="/">Home</a> <span>/</span> <a href="/categories">Catalog</a> <span>/</span> <span style="color: var(--ink);"><?= $view->e($categoryName) ?></span>
  </div>

  <!-- Category Title & Header -->
  <div class="flex items-end justify-between flex-wrap gap-4" style="margin-bottom: 28px; padding-bottom: 20px; border-bottom: 1px solid var(--line);">
    <div>
      <div class="badge badge-pine tiny" style="margin-bottom: 6px;">Category Hub</div>
      <h1><?= $view->e($categoryName) ?></h1>
      <p class="muted small"><?= $view->e($category['desc'] ?? 'Commercial quality disposables and consumables in wholesale & sleeve packs.') ?></p>
    </div>

    <!-- Quick Stats -->
    <div class="flex items-center gap-3">
      <div class="stat" style="padding: 6px 14px; background: var(--surface); border: 1px solid var(--line); border-radius: var(--radius-sm);">
        <span class="small muted">Available SKUs:</span>
        <strong class="tnum mono" style="color: var(--pine); margin-left: 4px;"><?= count($products) ?></strong>
      </div>
    </div>
  </div>

  <!-- Layout: Faceted Filter Sidebar + Products Grid -->
  <div style="display: grid; grid-template-columns: 240px 1fr; gap: 32px; align-items: start;">
    
    <!-- Left Facet Filters -->
    <aside class="card" style="padding: 20px;">
      <div class="flex items-center justify-between" style="margin-bottom: 16px; padding-bottom: 8px; border-bottom: 1px solid var(--line);">
        <h4 style="font-size: 0.95rem;">Filter by Facets</h4>
        <a href="/category/<?= $view->e($categorySlug) ?>" class="tiny" style="color: var(--clay);">Reset</a>
      </div>

      <!-- Eco Filter -->
      <div style="margin-bottom: 20px;">
        <label class="flex items-center gap-2 small" style="cursor: pointer;">
          <input type="checkbox" onchange="window.location.href='/category/<?= $view->e($categorySlug) ?>?eco=' + (this.checked ? '1' : '0')" <?= $ecoOnly ? 'checked' : '' ?>>
          <span>Compostable / Eco-Only</span>
        </label>
      </div>

      <!-- Material Facets -->
      <div style="margin-bottom: 20px;">
        <div class="tiny bold muted" style="text-transform: uppercase; margin-bottom: 8px;">Material Type</div>
        <div class="flex flex-col gap-2 small">
          <a href="/category/<?= $view->e($categorySlug) ?>?material=kraft" class="<?= $material === 'kraft' ? 'bold' : 'muted' ?>" style="color: <?= $material === 'kraft' ? 'var(--pine)' : 'var(--ink-2)' ?>;">
            • Brown Kraft Paper
          </a>
          <a href="/category/<?= $view->e($categorySlug) ?>?material=areca" class="<?= $material === 'areca' ? 'bold' : 'muted' ?>" style="color: <?= $material === 'areca' ? 'var(--pine)' : 'var(--ink-2)' ?>;">
            • Areca Palm Leaf
          </a>
          <a href="/category/<?= $view->e($categorySlug) ?>?material=bagasse" class="<?= $material === 'bagasse' ? 'bold' : 'muted' ?>" style="color: <?= $material === 'bagasse' ? 'var(--pine)' : 'var(--ink-2)' ?>;">
            • Sugarcane Bagasse
          </a>
          <a href="/category/<?= $view->e($categorySlug) ?>?material=birchwood" class="<?= $material === 'birchwood' ? 'bold' : 'muted' ?>" style="color: <?= $material === 'birchwood' ? 'var(--pine)' : 'var(--ink-2)' ?>;">
            • Natural Birchwood
          </a>
        </div>
      </div>

      <!-- Pack Size Formats -->
      <div style="margin-bottom: 20px;">
        <div class="tiny bold muted" style="text-transform: uppercase; margin-bottom: 8px;">Pack Format</div>
        <div class="flex flex-col gap-2 small muted">
          <label class="flex items-center gap-2"><input type="checkbox" checked disabled> Retail Sleeves (25–100 pcs)</label>
          <label class="flex items-center gap-2"><input type="checkbox" checked disabled> Wholesale Cartons (500–5000 pcs)</label>
        </div>
      </div>

      <!-- Certifications -->
      <div style="background-color: var(--pine-wash); padding: 12px; border-radius: var(--radius-sm); font-size: 0.78rem; color: var(--pine-dark);">
        <div class="flex items-center gap-1 bold" style="margin-bottom: 4px;">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/>
          </svg>
          <span>Certified Quality</span>
        </div>
        All food-contact paper products are CPCB &amp; FDA tested virgin pulp.
      </div>
    </aside>

    <!-- Right Products Grid -->
    <div>
      <?php if (empty($products)): ?>
        <div class="card center" style="padding: 48px 20px;">
          <div style="margin-bottom: 12px;">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="var(--border-dark)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
              <path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>
            </svg>
          </div>
          <h4>No products found in this filter</h4>
          <p class="muted small" style="margin-bottom: 16px;">Try unchecking filters to see all available products.</p>
          <a href="/category/<?= $view->e($categorySlug) ?>" class="btn btn-primary btn-sm">Clear Filters</a>
        </div>
      <?php else: ?>
        <div class="grid grid-3">
          <?php foreach ($products as $product): ?>
            <?= $view->include('components/product-card', [
              'product' => $product,
              'view'    => $view,
            ]) ?>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<?php $view->stop(); ?>
