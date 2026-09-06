<?php 
/** 
 * @var App\Support\View $view 
 * @var array $product
 * @var array|null $category
 * @var array $relatedProducts
 */ 
$view->extend('layouts/app'); 
$view->start('content'); 

$name = $product['name'] ?? 'Product Name';
$skuRoot = $product['sku_root'] ?? 'SKU';
$image = $product['image'] ?? 'https://images.unsplash.com/photo-1577937927133-66ef06acdf18?w=800&auto=format&fit=crop&q=80';
$material = $product['material'] ?? 'Food-Grade Virgin Pulp';
$isEco = !empty($product['is_eco']);
$variants = $product['variants'] ?? [];
$firstVariant = $variants[0] ?? ['name' => 'Standard', 'price' => 2.5, 'mrp' => 3.5, 'packs' => [[50, 'Pack of 50'], [500, 'Carton of 500']]];
$packs = $firstVariant['packs'] ?? [[50, 'Pack of 50']];

$unitPrice = (float) ($firstVariant['price'] ?? 2.5);
$mrpUnit = (float) ($firstVariant['mrp'] ?? $unitPrice * 1.4);

$defaultPackQty = (int) $packs[0][0];
$defaultPackLabel = (string) $packs[0][1];
$defaultPackPrice = round($unitPrice * $defaultPackQty, 2);
$defaultPackMrp = round($mrpUnit * $defaultPackQty, 2);
$savings = $defaultPackMrp > $defaultPackPrice ? round((($defaultPackMrp - $defaultPackPrice) / $defaultPackMrp) * 100) : 0;
?>

<div class="wrap" style="padding-top: 24px; padding-bottom: 80px;">
  <!-- Breadcrumb -->
  <div class="flex items-center gap-2 small muted" style="margin-bottom: 20px;">
    <a href="/">Home</a> <span>/</span>
    <a href="/categories">Catalog</a> <span>/</span>
    <?php if ($category): ?>
      <a href="/category/<?= $view->e($category['slug']) ?>"><?= $view->e($category['name']) ?></a> <span>/</span>
    <?php endif; ?>
    <span style="color: var(--ink);"><?= $view->e($name) ?></span>
  </div>

  <!-- PDP Main Grid -->
  <div class="pdp-grid">
    
    <!-- Left: Gallery & Certifications -->
    <div>
      <div class="pdp-gallery-main">
        <img id="pdp-main-img" src="<?= $view->e($image) ?>" alt="<?= $view->e($name) ?>">
      </div>

      <!-- Trust & Certification Badges -->
      <div class="card" style="margin-top: 20px; padding: 18px;">
        <div class="tiny bold muted flex items-center gap-1" style="text-transform: uppercase; margin-bottom: 10px;">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/>
          </svg>
          <span>Quality &amp; Compliance Guarantee</span>
        </div>
        <div class="grid grid-2 gap-3 small">
          <div class="flex items-center gap-2">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--eco)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/><path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/>
            </svg>
            <span>CPCB Certified Green</span>
          </div>
          <div class="flex items-center gap-2">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--pine)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/>
            </svg>
            <span>100% Leak-Proof Inner Seal</span>
          </div>
          <div class="flex items-center gap-2">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/>
            </svg>
            <span>FDA Food Grade Contact Safe</span>
          </div>
          <div class="flex items-center gap-2">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect width="14" height="18" x="5" y="3" rx="2"/><path d="M9 7h6"/><path d="M9 11h6"/>
            </svg>
            <span>HSN <?= $view->e($product['hsn'] ?? '4823') ?> · 18% GST Credit</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Right: Product Info, Live Pack Selector & Tier Matrix -->
    <div class="pdp-info-panel" data-id="<?= $view->e($skuRoot) ?>">
      
      <!-- Badges -->
      <div class="flex items-center gap-2" style="margin-bottom: 10px;">
        <?php if ($isEco): ?>
          <span class="badge badge-eco">Eco-Certified</span>
        <?php endif; ?>
        <span class="badge badge-pine">Base SKU: <?= $view->e($skuRoot) ?></span>
        <span class="badge badge-gold">★ 4.9 (128 verified reviews)</span>
      </div>

      <h1><?= $view->e($name) ?></h1>
      <p class="muted" style="font-size: 1rem; margin-bottom: 18px;"><?= $view->e($product['short'] ?? 'Premium food-grade commercial packaging & consumables.') ?></p>

      <!-- Live Price Display -->
      <div style="background-color: var(--surface); border: 1px solid var(--line); border-radius: var(--radius); padding: 18px; margin-bottom: 20px;">
        <div class="flex items-baseline justify-between">
          <div>
            <div class="flex items-baseline gap-2">
              <span class="price-headline mono tnum" id="pdp-price-headline" style="font-size: 2rem; color: var(--pine-dark);">
                ₹<?= number_format($defaultPackPrice) ?>
              </span>
              <span class="mrp-strike mono tnum" id="pdp-mrp-strike" style="font-size: 1.1rem;">
                ₹<?= number_format($defaultPackMrp) ?>
              </span>
              <span class="badge badge-clay" id="pdp-savings-badge"><?= $savings ?>% OFF</span>
            </div>
            <div class="price-per-unit mono tnum" id="pdp-unit-price" style="font-size: 0.95rem; margin-top: 4px;">
              ₹<?= number_format($unitPrice, 2) ?> / piece (Pack of <?= $defaultPackQty ?> pcs)
            </div>
          </div>

          <div class="right tiny muted">
            <span style="color: var(--eco); font-weight: 700;">✓ In Stock</span><br>
            <span>Verified Warehouse Pool</span>
          </div>
        </div>
      </div>

      <!-- Pack Format Selector Box -->
      <div class="pdp-pack-selector-box">
        <div class="flex items-center justify-between">
          <label class="tiny bold muted" style="text-transform: uppercase;">Select Pack Presentation:</label>
          <span class="tiny" style="color: var(--clay);">Cartons save up to 28%</span>
        </div>

        <div class="pdp-pack-options">
          <?php foreach ($packs as $idx => $pack): 
            $pQty = (int) $pack[0];
            $pLabel = (string) $pack[1];
            $pPrice = round($unitPrice * $pQty, 2);
            $pMrp = round($mrpUnit * $pQty, 2);
          ?>
            <div 
              class="pdp-pack-option <?= $idx === 0 ? 'active' : '' ?>"
              data-pcs="<?= $pQty ?>"
              data-label="<?= $view->e($pLabel) ?>"
              data-price="<?= $pPrice ?>"
              data-mrp="<?= $pMrp ?>"
              data-unit-price="<?= $unitPrice ?>"
            >
              <div class="flex items-center justify-between">
                <strong style="font-size: 0.95rem;"><?= $view->e($pLabel) ?></strong>
                <span class="bold mono tnum" style="color: var(--pine);">₹<?= number_format($pPrice) ?></span>
              </div>
              <div class="tiny muted" style="margin-top: 4px;">
                ₹<?= number_format($unitPrice, 2) ?> per piece
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Wholesale Volume Discount Tiers -->
      <div class="tier-table-wrap">
        <div class="tier-table-header">
          <span>Wholesale Volume Pricing (Automatic Tier Drops)</span>
          <span class="tiny text-clay">Save on Carton Quantities</span>
        </div>
        <table class="tier-table">
          <thead>
            <tr>
              <th>Min Quantity (Base Pcs)</th>
              <th>Unit Rate (₹/pc)</th>
              <th>Effective Savings</th>
            </tr>
          </thead>
          <tbody>
            <tr class="active-tier">
              <td>1 – 99 pcs (Sleeve)</td>
              <td class="mono">₹<?= number_format($unitPrice, 2) ?></td>
              <td>Base MRP Discount</td>
            </tr>
            <tr>
              <td>100 – 499 pcs</td>
              <td class="mono">₹<?= number_format($unitPrice * 0.90, 2) ?></td>
              <td><span class="badge badge-clay tiny">10% Extra OFF</span></td>
            </tr>
            <tr>
              <td>500 – 1,999 pcs (Carton)</td>
              <td class="mono">₹<?= number_format($unitPrice * 0.82, 2) ?></td>
              <td><span class="badge badge-clay tiny">18% Extra OFF</span></td>
            </tr>
            <tr>
              <td>2,000+ pcs (Wholesale)</td>
              <td class="mono">₹<?= number_format($unitPrice * 0.74, 2) ?></td>
              <td><span class="badge badge-clay tiny">26% Wholesale Drop</span></td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Add to Cart & Buy Now Actions -->
      <div class="flex items-center gap-3" style="margin-top: 24px;">
        <div class="qty-stepper" style="padding: 4px 8px;">
          <button type="button" class="qty-btn" id="pdp-qty-minus" aria-label="Decrease quantity">−</button>
          <span class="qty-val" id="pdp-qty-val">1</span>
          <button type="button" class="qty-btn" id="pdp-qty-plus" aria-label="Increase quantity">+</button>
        </div>

        <button 
          type="button" 
          class="btn btn-primary btn-lg flex-1 add-to-cart-btn flex items-center justify-center gap-2"
          id="pdp-add-to-cart-btn"
          data-id="<?= $view->e($skuRoot) ?>"
          data-name="<?= $view->e($name) ?>"
          data-price="<?= $defaultPackPrice ?>"
          data-pack="<?= $view->e($defaultPackLabel) ?>"
          data-pcs="<?= $defaultPackQty ?>"
          data-unit-price="<?= $unitPrice ?>"
          data-image="<?= $view->e($image) ?>"
        >
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>
          </svg>
          <span>Add to Supply Basket</span>
        </button>

        <button type="button" class="btn btn-clay btn-lg" id="pdp-buy-now-btn">
          Buy Now
        </button>
      </div>

      <!-- Custom High-Volume Quote Desk -->
      <div style="margin-top: 16px; background-color: var(--surface-2); padding: 12px 16px; border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: space-between;">
        <div class="small">
          Need <strong>10,000+ pieces</strong> or custom logo printing?
        </div>
        <a href="/b2b" class="btn btn-outline-pine btn-sm">Request Factory Quote →</a>
      </div>

    </div>
  </div>

  <!-- Frequently Bought Together (FBT) Bundle -->
  <div class="fbt-card">
    <div class="badge badge-pine tiny" style="margin-bottom: 6px;">Complete Your Service Station</div>
    <h3>Frequently Bought Together</h3>
    <p class="muted small">Save time and freight by pairing matching accessories.</p>

    <div class="fbt-items">
      <div class="flex items-center gap-3">
        <div class="fbt-thumb"><img src="<?= $view->e($image) ?>" alt="Main"></div>
        <div>
          <div class="bold small"><?= $view->e($name) ?></div>
          <div class="tiny muted">₹<?= number_format($defaultPackPrice) ?></div>
        </div>
      </div>

      <span class="fbt-plus">+</span>

      <div class="flex items-center gap-3">
        <div class="fbt-thumb"><img src="https://images.unsplash.com/photo-1615865417491-9941019fbc00?w=120&auto=format&fit=crop&q=80" alt="Birchwood"></div>
        <div>
          <div class="bold small">Birchwood Wooden Stirrers (500 pcs)</div>
          <div class="tiny muted">₹190.00</div>
        </div>
      </div>

      <span class="fbt-plus">+</span>

      <div class="flex items-center gap-3">
        <div class="fbt-thumb"><img src="https://images.unsplash.com/photo-1583947215259-38e31be8751f?w=120&auto=format&fit=crop&q=80" alt="Napkins"></div>
        <div>
          <div class="bold small">2-Ply Kraft Pop-up Napkins (100 pcs)</div>
          <div class="tiny muted">₹85.00</div>
        </div>
      </div>

      <div style="margin-left: auto;">
        <button 
          type="button"
          class="btn btn-clay btn-sm" 
          data-action="add-to-cart"
          data-id="FBT-BUNDLE-01" 
          data-name="Coffee Station Complete Bundle (Cup + Stirrer + Napkin)" 
          data-pack="Full Station Set" 
          data-pcs="100" 
          data-price="<?= $defaultPackPrice + 275 ?>" 
          data-image="<?= $view->e($image) ?>"
        >
          Add 3-Item Bundle (₹<?= number_format($defaultPackPrice + 275) ?>)
        </button>
      </div>
    </div>
  </div>

  <!-- Related Products -->
  <?php if (!empty($relatedProducts)): ?>
    <div style="margin-top: 64px;">
      <h3 style="margin-bottom: 20px;">You Might Also Need</h3>
      <div class="grid grid-4">
        <?php foreach ($relatedProducts as $rel): ?>
          <?= $view->include('components/product-card', [
            'product' => $rel,
            'view'    => $view,
          ]) ?>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php $view->stop(); ?>
