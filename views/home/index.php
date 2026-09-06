<?php 
/** 
 * SUPPLYKARO — MASTER HOMEPAGE
 * Tagline: "Har Supply, Ek Jagah."
 * @var App\Support\View $view 
 * @var array $heroSlides
 * @var array $primaryCategories
 * @var array $bestSellers
 * @var array $newArrivals
 * @var array $featured
 * @var array $businessNeeds
 * @var array $occasions
 */ 
$view->extend('layouts/app'); 
$view->start('content'); 
?>

<div class="wrap">
  
  <!-- =========================================================================
       SECTION 1: EDITORIAL HERO CAROUSEL
       ========================================================================= -->
  <div class="hero-carousel-container" aria-label="Hero Highlights">
    <div class="hero-slider-track" id="hero-slider-track">
      <?php foreach ($heroSlides as $slide): ?>
        <div class="hero-slide">
          <img src="<?= $view->e($slide['image']) ?>" alt="<?= $view->e($slide['title']) ?>" class="hero-slide-bg" loading="lazy">
          <div class="hero-slide-overlay"></div>
          <div class="hero-slide-content">
            <h1><?= $view->e($slide['title']) ?></h1>
            <p><?= $view->e($slide['subtitle']) ?></p>
            <div class="flex items-center gap-3 flex-wrap">
              <a href="<?= $view->e($slide['ctaUrl']) ?>" class="btn btn-primary btn-lg">
                <?= $view->e($slide['ctaText']) ?>
              </a>
              <?php if (!empty($slide['secText'])): ?>
                <a href="<?= $view->e($slide['secUrl']) ?>" class="btn btn-secondary btn-lg" style="background: rgba(255,255,255,0.15); color: #fff; border-color: rgba(255,255,255,0.3); backdrop-filter: blur(8px);">
                  <?= $view->e($slide['secText']) ?>
                </a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Arrow Controls -->
    <button type="button" class="hero-nav-arrow hero-nav-prev" id="hero-prev-btn" aria-label="Previous Slide">‹</button>
    <button type="button" class="hero-nav-arrow hero-nav-next" id="hero-next-btn" aria-label="Next Slide">›</button>

    <!-- Pagination Dots -->
    <div class="hero-dots-wrap">
      <?php foreach ($heroSlides as $idx => $slide): ?>
        <div class="hero-dot <?= $idx === 0 ? 'active' : '' ?>" data-index="<?= $idx ?>"></div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- ====================================================================  <!-- =========================================================================
       SECTION 2: SHOP BY CATEGORY RAIL (Horizontal Icon Cards)
       ========================================================================= -->
  <section class="category-rail-section">
    <div class="rail-header">
      <h2>Shop by Category</h2>
      <a href="/categories" class="bold small">View All Categories →</a>
    </div>

    <div class="category-rail-track" id="category-rail-track">
      <?php foreach ($primaryCategories as $cat): ?>
        <a href="/category/<?= $view->e($cat['slug']) ?>" class="category-tile-card">
          <div class="category-icon-box">
            <svg class="ui-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/>
            </svg>
          </div>
          <span class="category-tile-name"><?= $view->e($cat['name']) ?></span>
        </a>
      <?php endforeach; ?>
      <a href="/categories" class="category-tile-card" style="background: var(--surface-alt);">
        <div class="category-icon-box" style="background: var(--border);">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/>
          </svg>
        </div>
        <span class="category-tile-name" style="color: var(--primary);">View All</span>
      </a>
    </div>
  </section>

  <!-- =========================================================================
       SECTION 3: TRUST & BENEFITS STRIP
       ========================================================================= -->
  <section class="trust-strip-section">
    <div class="trust-grid">
      <div class="trust-card">
        <div class="trust-icon-wrap">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
            <path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>
          </svg>
        </div>
        <div class="trust-text">
          <h4>Wide Range of Products</h4>
          <p>Everything you need, all in one single place.</p>
        </div>
      </div>
      <div class="trust-card">
        <div class="trust-icon-wrap">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 2H2v10l9.29 9.29c.94.94 2.48.94 3.42 0l6.58-6.58c.94-.94.94-2.48 0-3.42L12 2Z"/>
            <path d="M7 7h.01"/>
          </svg>
        </div>
        <div class="trust-text">
          <h4>Best Wholesale Prices</h4>
          <p>Competitive direct factory pricing for every business.</p>
        </div>
      </div>
      <div class="trust-card">
        <div class="trust-icon-wrap">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect width="16" height="13" x="1" y="3" rx="2"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>
          </svg>
        </div>
        <div class="trust-text">
          <h4>Fast &amp; Reliable Delivery</h4>
          <p>On-time dispatch and door delivery every time.</p>
        </div>
      </div>
      <div class="trust-card">
        <div class="trust-icon-wrap">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/>
          </svg>
        </div>
        <div class="trust-text">
          <h4>Quality You Can Trust</h4>
          <p>100% Food-grade products selected for business use.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- =========================================================================
       SECTION 4: BEST SELLERS PRODUCT RAIL
       ========================================================================= -->
  <section style="margin-top: var(--space-10);">
    <div class="rail-header">
      <div>
        <h2>Best Sellers</h2>
        <p class="muted small">High-demand commercial packaging &amp; tableware items.</p>
      </div>
      <a href="/categories" class="bold small">View All Products →</a>
    </div>

    <div class="grid grid-4">
      <?php foreach (array_slice($bestSellers, 0, 4) as $product): ?>
        <?= $view->include('components/product-card', [
          'product' => $product,
          'view'    => $view,
        ]) ?>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- =========================================================================
       SECTION 5: SHOP BY BUSINESS NEEDS
       ========================================================================= -->
  <section class="business-needs-section">
    <div class="rail-header">
      <div>
        <h2>Shop by Business Needs</h2>
        <p class="muted small">Everything your business needs, in one place.</p>
      </div>
      <a href="/b2b" class="bold small" style="color: var(--primary);">View All Business Supplies →</a>
    </div>

    <div class="business-cards-grid">
      <?php foreach ($businessNeeds as $biz): ?>
        <a href="<?= $view->e($biz['url']) ?>" class="business-card-tile">
          <img src="<?= $view->e($biz['image']) ?>" alt="<?= $view->e($biz['name']) ?>" class="business-card-bg" loading="lazy">
          <div class="business-card-overlay"></div>
          <div class="business-card-info">
            <div class="business-card-title"><?= $view->e($biz['name']) ?></div>
            <div class="business-card-desc"><?= $view->e($biz['desc']) ?></div>
            <div class="business-card-cta"><?= $view->e($biz['cta']) ?></div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- =========================================================================
       SECTION 6: BULK BUYING & WHOLESALE SLABS
       ========================================================================= -->
  <section class="bulk-slabs-section">
    <div class="flex items-center justify-between flex-wrap gap-4">
      <div>
        <span class="badge badge-primary" style="margin-bottom: 6px;">Wholesale Advantage</span>
        <h2>Buying in Bulk?</h2>
        <p class="muted small">Get significantly better pricing when you buy carton &amp; pallet quantities.</p>
      </div>
      <div class="flex items-center gap-3">
        <a href="/b2b#quote-form" class="btn btn-primary">Request Custom Quote</a>
        <a href="/b2b" class="btn btn-secondary">Explore B2B Portal</a>
      </div>
    </div>

    <div class="bulk-slabs-grid">
      <div class="bulk-slab-box">
        <div class="slab-qty">100 pcs</div>
        <div class="slab-disc">Standard Retail</div>
      </div>
      <div class="bulk-slab-box">
        <div class="slab-qty">500 pcs</div>
        <div class="slab-disc">Save 10% Extra</div>
      </div>
      <div class="bulk-slab-box">
        <div class="slab-qty">1,000 pcs</div>
        <div class="slab-disc">Save 18% Carton Rate</div>
      </div>
      <div class="bulk-slab-box">
        <div class="slab-qty">5,000 pcs</div>
        <div class="slab-disc">Save 26% Wholesale</div>
      </div>
      <div class="bulk-slab-box">
        <div class="slab-qty">10,000+ pcs</div>
        <div class="slab-disc">Direct Factory Quote</div>
      </div>
    </div>
  </section>

  <!-- =========================================================================
       SECTION 7: SHOP BY OCCASION
       ========================================================================= -->
  <section style="margin-top: var(--space-10);">
    <div class="rail-header">
      <div>
        <h2>Shop by Occasion</h2>
        <p class="muted small">Curated tableware and consumable setups for every celebration scale.</p>
      </div>
      <a href="/party-box" class="bold small">All Event Kits →</a>
    </div>

    <div class="grid grid-4">
      <?php foreach ($occasions as $occ): ?>
        <div class="card" style="padding: 24px; display: flex; flex-direction: column; justify-content: space-between;">
          <div>
            <span class="badge badge-neutral" style="margin-bottom: 12px; font-weight: 700;"><?= $view->e($occ['tag'] ?? 'Occasion') ?></span>
            <h3 style="font-size: 1.15rem; margin-bottom: 4px;"><?= $view->e($occ['name']) ?></h3>
            <p class="small muted" style="margin-bottom: 16px;"><?= $view->e($occ['desc']) ?></p>
          </div>
          <a href="<?= $view->e($occ['url']) ?>" class="btn btn-outline btn-sm">
            <?= $view->e($occ['cta']) ?> →
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  <!-- =========================================================================
       SECTION 8: SIGNATURE EVENT CALCULATOR WIDGET
       ========================================================================= -->
  <section class="event-calc-banner">
    <div class="event-calc-grid">
      <div>
        <span class="badge badge-warning" style="margin-bottom: 8px;">Zero-Shortage Event Algorithm</span>
        <h2 style="color: #FFFFFF; font-size: 2.2rem; margin-bottom: 12px;">Planning an Event?</h2>
        <p style="color: #D2E4DA; font-size: 1.05rem; line-height: 1.6; margin-bottom: 24px;">
          Tell us your guest count and meal format. Our mathematical algorithm automatically calculates plates, bowls, cups, spoons, tissues, and trash bags with safety margins included.
        </p>
        <div class="flex items-center gap-3">
          <a href="/event-calculator" class="btn btn-primary btn-lg" style="background: var(--primary);">Open Full Calculator →</a>
          <a href="/party-box" class="btn btn-secondary btn-lg" style="background: rgba(255,255,255,0.1); color: #fff; border-color: rgba(255,255,255,0.2);">Party Box Builder</a>
        </div>
      </div>

      <!-- Quick Interactive Widget -->
      <div class="calc-widget-surface">
        <h3 style="margin-bottom: 14px; font-size: 1.1rem;">Quick 50-Guest Celebration Estimate</h3>
        <div class="flex flex-col gap-2 small" style="margin-bottom: 16px;">
          <div class="flex items-center justify-between" style="padding: 6px 0; border-bottom: 1px dashed var(--border);">
            <span>10" Areca Palm Plates (60 pcs):</span>
            <strong class="mono tnum">₹510.00</strong>
          </div>
          <div class="flex items-center justify-between" style="padding: 6px 0; border-bottom: 1px dashed var(--border);">
            <span>250ml Ripple Cups (100 pcs):</span>
            <strong class="mono tnum">₹360.00</strong>
          </div>
          <div class="flex items-center justify-between" style="padding: 6px 0; border-bottom: 1px dashed var(--border);">
            <span>Birchwood Spoon+Fork Sets (100 pcs):</span>
            <strong class="mono tnum">₹180.00</strong>
          </div>
          <div class="flex items-center justify-between" style="padding: 6px 0; border-bottom: 1px dashed var(--border);">
            <span>2-Ply Kraft Napkins (150 pcs):</span>
            <strong class="mono tnum">₹60.00</strong>
          </div>
        </div>
        <div class="flex items-center justify-between" style="padding-top: 10px; border-top: 1px solid var(--border); margin-bottom: 14px;">
          <strong>Total Estimated Kit:</strong>
          <span class="bold mono tnum" style="font-size: 1.25rem; color: var(--primary);">₹1,110.00</span>
        </div>
        <a href="/event-calculator?guests=50" class="btn btn-primary btn-block">
          Customize 50-Guest Supplies Kit →
        </a>
      </div>
    </div>
  </section>

  <!-- =========================================================================
       SECTION 9: "BETTER CHOICES" ECO COLLECTION
       ========================================================================= -->
  <section class="eco-section">
    <div class="rail-header">
      <div>
        <h2>Better Choices (Eco-Friendly Tableware)</h2>
        <p class="muted small">Choose products designed with sustainability in mind. 100% CPCB certified.</p>
      </div>
      <a href="/categories?eco=1" class="bold small" style="color: var(--success);">Explore Green Range →</a>
    </div>

    <div class="eco-cards-grid">
      <a href="/category/tableware?material=bagasse" class="eco-material-card">
        <div class="eco-icon-wrap">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 2v20"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
          </svg>
        </div>
        <div class="bold" style="color: var(--text);">Sugarcane Bagasse</div>
        <div class="tiny muted" style="margin-top: 4px;">100% Compostable Plates &amp; Meal Trays</div>
      </a>
      <a href="/category/tableware?material=areca" class="eco-material-card">
        <div class="eco-icon-wrap">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/><path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/>
          </svg>
        </div>
        <div class="bold" style="color: var(--text);">Areca Palm Leaf</div>
        <div class="tiny muted" style="margin-top: 4px;">Chemical-Free Natural Fallen Leaf Platters</div>
      </a>
      <a href="/category/cutlery?material=birchwood" class="eco-material-card">
        <div class="eco-icon-wrap">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="m18 15-6-6-6 6"/><path d="M12 9v12"/>
          </svg>
        </div>
        <div class="bold" style="color: var(--text);">Natural Birchwood</div>
        <div class="tiny muted" style="margin-top: 4px;">Splinter-Free Heavy Duty Cutlery</div>
      </a>
      <a href="/category/cups-beverage?material=kraft" class="eco-material-card">
        <div class="eco-icon-wrap">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect width="14" height="18" x="5" y="3" rx="2"/><path d="M9 7h6"/><path d="M9 11h6"/>
          </svg>
        </div>
        <div class="bold" style="color: var(--text);">Recycled Kraft Paper</div>
        <div class="tiny muted" style="margin-top: 4px;">Insulated Ripple Cups &amp; Takeaway Bags</div>
      </a>
    </div>
  </section>

  <!-- =========================================================================
       SECTION 10: NEW ARRIVALS ON SUPPLYKARO
       ========================================================================= -->
  <?php if (!empty($newArrivals)): ?>
    <section style="margin-top: var(--space-10);">
      <div class="rail-header">
        <div>
          <h2>New on SupplyKaro</h2>
          <p class="muted small">Recently launched sustainable packaging and catering lines.</p>
        </div>
        <a href="/categories" class="bold small">Browse All Catalog Products →</a>
      </div>

      <div class="grid grid-4">
        <?php foreach (array_slice($newArrivals, 0, 4) as $product): ?>
          <?= $view->include('components/product-card', [
            'product' => $product,
            'view'    => $view,
          ]) ?>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

  <!-- =========================================================================
       SECTION 11: B2B COMMERCIAL PROCUREMENT OVERVIEW
       ========================================================================= -->
  <section style="margin-top: var(--space-10);">
    <div style="background-color: var(--b2b-wash); border: 1.5px solid #C4D9CD; border-radius: var(--radius-lg); padding: 28px;">
      <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
          <span class="badge" style="background: #D5E8DD; color: #1E4C3A; margin-bottom: 6px; font-weight: 700;">Commercial Kitchen Procurement</span>
          <h3 style="color: #1E4C3A; font-size: 1.35rem;">Consolidate All Your Supplies Under One Account</h3>
          <p class="small" style="color: #2D5846; margin: 4px 0 0;">
            Direct factory master carton pricing, 100% GST input tax credit, and scheduled deliveries.
          </p>
        </div>
        <div class="flex items-center gap-3">
          <a href="/b2b#quote-form" class="btn btn-secondary">Request Volume Quote</a>
          <a href="/b2b" class="btn btn-primary" style="background: #1E4C3A;">Open Business Console →</a>
        </div>
      </div>

      <div class="grid grid-3" style="margin-top: 20px;">
        <div class="card" style="padding: 16px;">
          <strong class="small" style="color: #1E4C3A;">100% Verified GST Invoicing</strong>
          <p class="tiny muted" style="margin-top: 4px;">Get full Input Tax Credit (ITC) on all orders with compliant tax invoices.</p>
        </div>
        <div class="card" style="padding: 16px;">
          <strong class="small" style="color: #1E4C3A;">Direct Factory Price Slabs</strong>
          <p class="tiny muted" style="margin-top: 4px;">Save up to 35% on carton and pallet shipments directly from production units.</p>
        </div>
        <div class="card" style="padding: 16px;">
          <strong class="small" style="color: #1E4C3A;">Dedicated Account Managers</strong>
          <p class="tiny muted" style="margin-top: 4px;">Direct support for custom branding, standing purchase orders, and sample testing.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- =========================================================================
       SECTION 12: TESTIMONIALS & CASE STUDIES
       ========================================================================= -->
  <section style="margin-top: var(--space-10);">
    <div class="rail-header">
      <div>
        <h2>Why Businesses Trust SupplyKaro</h2>
        <p class="muted small">Real feedback from cafe founders, caterers, and cloud kitchen operators.</p>
      </div>
    </div>

    <div class="grid grid-3">
      <div class="card" style="padding: 24px;">
        <div class="flex items-center gap-1" style="color: #F59E0B; margin-bottom: 12px;">
          <?php for ($i = 0; $i < 5; $i++): ?>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          <?php endfor; ?>
        </div>
        <p class="small" style="line-height: 1.6; margin-bottom: 16px;">
          "SupplyKaro replaced three different unorganized mandi distributors for our chain of 6 cafes. We get reliable 24h delivery, clean GST invoices, and zero cup leakage."
        </p>
        <div class="flex items-center gap-3">
          <div style="width: 38px; height: 38px; border-radius: 50%; background: var(--primary-wash); color: var(--primary); font-weight: 700; display: grid; place-items: center;">RK</div>
          <div>
            <div class="bold small">Rohit Kapoor</div>
            <div class="tiny muted">Founder, The Daily Roast Cafe (Delhi NCR)</div>
          </div>
        </div>
      </div>

      <div class="card" style="padding: 24px;">
        <div class="flex items-center gap-1" style="color: #F59E0B; margin-bottom: 12px;">
          <?php for ($i = 0; $i < 5; $i++): ?>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          <?php endfor; ?>
        </div>
        <p class="small" style="line-height: 1.6; margin-bottom: 16px;">
          "The event calculator is pure genius. We planned a 1,200-guest wedding reception without a single plate shortage. All areca palm platters were pristine."
        </p>
        <div class="flex items-center gap-3">
          <div style="width: 38px; height: 38px; border-radius: 50%; background: var(--success-wash); color: var(--success); font-weight: 700; display: grid; place-items: center;">AM</div>
          <div>
            <div class="bold small">Ananya Mukherjee</div>
            <div class="tiny muted">Lead Event Planner, Royal Feasts Caterers</div>
          </div>
        </div>
      </div>

      <div class="card" style="padding: 24px;">
        <div class="flex items-center gap-1" style="color: #F59E0B; margin-bottom: 12px;">
          <?php for ($i = 0; $i < 5; $i++): ?>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          <?php endfor; ?>
        </div>
        <p class="small" style="line-height: 1.6; margin-bottom: 16px;">
          "As a multi-brand cloud kitchen, packaging consistency is our brand. SupplyKaro's Net-30 credit terms and carton prices dropped our packaging costs by 19%."
        </p>
        <div class="flex items-center gap-3">
          <div style="width: 38px; height: 38px; border-radius: 50%; background: var(--warning-wash); color: var(--warning); font-weight: 700; display: grid; place-items: center;">VS</div>
          <div>
            <div class="bold small">Vikramaditya Singh</div>
            <div class="tiny muted">Ops Director, BoxEats Cloud Kitchens</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- =========================================================================
       SECTION 13: BRAND TRUST & WHY SUPPLYKARO
       ========================================================================= -->
  <section style="margin-top: var(--space-10); margin-bottom: var(--space-8);">
    <div style="background-color: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 36px; text-align: center;">
      <h3 style="font-size: 1.6rem; margin-bottom: 8px;">Har Supply, Ek Jagah.</h3>
      <p class="muted small" style="max-width: 600px; margin: 0 auto 24px;">
        Direct manufacturer procurement, Pan-India logistics, certified eco-friendly materials, and dedicated enterprise support.
      </p>
      <div class="flex items-center justify-center gap-6 flex-wrap small bold" style="color: var(--text-secondary);">
        <span class="flex items-center gap-1">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--success)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
          2,400+ Active B2B Accounts
        </span>
        <span class="flex items-center gap-1">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--success)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
          100% GST Tax Compliant
        </span>
        <span class="flex items-center gap-1">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--success)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
          Zero Split-Stock Guarantee
        </span>
        <span class="flex items-center gap-1">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--success)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
          24-Hour Dispatch SLA
        </span>
      </div>
    </div>
  </section>

</div>

<?php $view->stop(); ?>
