<?php
/**
 * Reusable Product Card Component
 * @var array $product Product data dictionary
 * @var App\Support\View $view
 */
$slug = $product['slug'] ?? 'item';
$skuRoot = $product['sku_root'] ?? $slug;
$name = $product['name'] ?? 'Product Name';
$category = $product['category'] ?? 'Category';
$image = $product['image'] ?? 'https://images.unsplash.com/photo-1577937927133-66ef06acdf18?w=600&auto=format&fit=crop&q=80';
$material = $product['material'] ?? 'Food-Grade Virgin Pulp';
$isEco = !empty($product['is_eco']);
$flags = $product['flags'] ?? [];

$variants = $product['variants'] ?? [];
$firstVariant = $variants[0] ?? ['price' => 1.5, 'mrp' => 2.2, 'packs' => [[50, 'Pack of 50 pcs']]];
$packs = $firstVariant['packs'] ?? [[50, 'Pack of 50 pcs']];

$unitPrice = (float) ($firstVariant['price'] ?? 1.5);
$mrpUnit = (float) ($firstVariant['mrp'] ?? $unitPrice * 1.4);

$packQty = (int) $packs[0][0];
$packLabel = (string) $packs[0][1];
$packPrice = round($unitPrice * $packQty, 2);
$packMrp = round($mrpUnit * $packQty, 2);
$savings = $packMrp > $packPrice ? round((($packMrp - $packPrice) / $packMrp) * 100) : 0;
?>
<div class="product-card" data-id="<?= $view->e($skuRoot) ?>">
  
  <!-- Product Image (1:1 Ratio) -->
  <div class="product-img-box">
    <a href="/products/<?= $view->e($slug) ?>">
      <img src="<?= $view->e($image) ?>" alt="<?= $view->e($name) ?>" loading="lazy">
    </a>

    <div class="product-badge-pill">
      <?php if (in_array('bestseller', $flags)): ?>
        <span class="badge badge-primary">BESTSELLER</span>
      <?php elseif ($isEco): ?>
        <span class="badge badge-eco">ECO CERTIFIED</span>
      <?php elseif (in_array('featured', $flags)): ?>
        <span class="badge badge-warning">FEATURED</span>
      <?php else: ?>
        <span class="badge badge-neutral">WHOLESALE</span>
      <?php endif; ?>
    </div>
  </div>

  <!-- Product Content -->
  <div class="product-card-content">
    <div class="product-spec-line">
      ★ 4.8 (120+) · <?= $view->e($material) ?>
    </div>
    
    <h3 class="product-card-title">
      <a href="/products/<?= $view->e($slug) ?>"><?= $view->e($name) ?></a>
    </h3>
    
    <div class="product-pack-desc">
      <?= $view->e($packLabel) ?>
    </div>

    <!-- Pricing Footer & Fast Add Stepper -->
    <div class="product-card-footer">
      <div class="product-price-block">
        <div class="flex items-baseline">
          <span class="price-main tnum">₹<?= number_format($packPrice) ?></span>
          <span class="price-mrp-strike tnum">₹<?= number_format($packMrp) ?></span>
        </div>
        <div class="price-unit-micro tnum">
          ₹<?= number_format($unitPrice, 2) ?> / piece
        </div>
      </div>

      <!-- Fast Add Button Container -->
      <div 
        class="fast-add-container" 
        data-id="<?= $view->e($skuRoot) ?>"
        data-name="<?= $view->e($name) ?>"
        data-price="<?= $packPrice ?>"
        data-pack="<?= $view->e($packLabel) ?>"
        data-pcs="<?= $packQty ?>"
        data-unit-price="<?= $unitPrice ?>"
        data-image="<?= $view->e($image) ?>"
      >
        <button 
          type="button" 
          class="fast-add-btn"
          data-id="<?= $view->e($skuRoot) ?>"
          data-name="<?= $view->e($name) ?>"
          data-price="<?= $packPrice ?>"
          data-pack="<?= $view->e($packLabel) ?>"
          data-pcs="<?= $packQty ?>"
          data-unit-price="<?= $unitPrice ?>"
          data-image="<?= $view->e($image) ?>"
          aria-label="Add <?= $view->e($name) ?> to cart"
        >
          + ADD
        </button>
      </div>
    </div>
  </div>

</div>
