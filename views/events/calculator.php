<?php 
/** 
 * @var App\Support\View $view 
 */ 
$view->extend('layouts/app'); 
$view->start('content'); 
?>

<div class="wrap" style="padding-top: 32px; padding-bottom: 80px;">
  
  <div class="center" style="max-width: 680px; margin: 0 auto 36px;">
    <div class="badge badge-pine" style="margin-bottom: 8px;">Smart Mathematical Algorithm</div>
    <h1>Catering &amp; Event Consumables Calculator</h1>
    <p class="muted">
      Calculate exact disposable requirements for banquets, weddings, cloud kitchens, and religious gatherings based on industry meal rules.
    </p>
  </div>

  <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 32px;">
    
    <!-- Left: Calculator Inputs Form -->
    <div class="card" style="padding: 28px;">
      <h3 style="margin-bottom: 20px;">1. Event Parameters</h3>

      <div class="flex flex-col gap-4">
        <div>
          <label class="small bold muted">Select Event Archetype:</label>
          <select id="calc-event-type" class="search-input-wrap" style="width: 100%; border-radius: var(--radius-sm); padding: 10px; font-weight: 600; margin-top: 6px;">
            <option value="wedding">Indian Wedding Reception (Heavy Buffet)</option>
            <option value="bhandara">Religious Bhandara / Langar (Continuous)</option>
            <option value="corporate">Corporate Conference &amp; Hi-Tea</option>
            <option value="birthday">Birthday &amp; Cocktail Party</option>
          </select>
        </div>

        <div>
          <label class="small bold muted">Total Guest Count:</label>
          <input type="number" id="calc-guests" value="250" class="search-input-wrap" style="width: 100%; border-radius: var(--radius-sm); padding: 10px; font-weight: 700; font-size: 1.1rem; margin-top: 6px;" min="25" max="10000" step="25">
        </div>

        <div>
          <label class="small bold muted">Service Style:</label>
          <select id="calc-style" class="search-input-wrap" style="width: 100%; border-radius: var(--radius-sm); padding: 10px; font-weight: 600; margin-top: 6px;">
            <option value="buffet">Self-Service Buffet (Standard 1.2x Plate Ratio)</option>
            <option value="seated">Seated Traditional Service (1.0x Thali Ratio)</option>
            <option value="cocktail">Standing Finger Food &amp; Bar (2.5x Glass Ratio)</option>
          </select>
        </div>

        <div>
          <label class="small bold muted">Quality Tier:</label>
          <div class="grid grid-3 gap-2" style="margin-top: 6px;">
            <label class="card center" style="padding: 10px; cursor: pointer; border-color: var(--pine); background: var(--pine-wash);">
              <input type="radio" name="calc_tier" value="eco" checked>
              <div class="tiny bold" style="margin-top: 4px;">100% Eco</div>
            </label>
            <label class="card center" style="padding: 10px; cursor: pointer;">
              <input type="radio" name="calc_tier" value="premium">
              <div class="tiny bold" style="margin-top: 4px;">Premium</div>
            </label>
            <label class="card center" style="padding: 10px; cursor: pointer;">
              <input type="radio" name="calc_tier" value="standard">
              <div class="tiny bold" style="margin-top: 4px;">Standard</div>
            </label>
          </div>
        </div>

        <div style="background: var(--surface-2); padding: 12px; border-radius: var(--radius-sm); font-size: 0.82rem; color: var(--ink-2);">
          <strong>Safety Factor:</strong> Formula automatically applies <code>ceil_to_pack(guests × 1.2 × 1.15)</code> to eliminate run-time shortages.
        </div>
      </div>
    </div>

    <!-- Right: Computed Master Bill of Materials -->
    <div class="card" style="padding: 28px; background: #FFFFFF;">
      <h3 style="margin-bottom: 20px;">2. Master Bill of Materials (BOM)</h3>

      <div class="flex flex-col gap-3">
        
        <div class="flex items-center justify-between" style="padding-bottom: 10px; border-bottom: 1px dashed var(--line);">
          <div>
            <div class="bold small">10" Areca Palm Dinner Plates</div>
            <div class="tiny muted">250 guests × 1.2 buffet ratio + 15% buffer</div>
          </div>
          <div class="right">
            <span class="badge badge-pine">350 pcs</span>
            <div class="small mono bold tnum" style="margin-top: 2px;">₹2,975.00</div>
          </div>
        </div>

        <div class="flex items-center justify-between" style="padding-bottom: 10px; border-bottom: 1px dashed var(--line);">
          <div>
            <div class="bold small">6" Bagasse Snack / Sweet Bowls</div>
            <div class="tiny muted">2 items per guest (Dessert + Dal/Curry)</div>
          </div>
          <div class="right">
            <span class="badge badge-pine">600 pcs</span>
            <div class="small mono bold tnum" style="margin-top: 2px;">₹1,920.00</div>
          </div>
        </div>

        <div class="flex items-center justify-between" style="padding-bottom: 10px; border-bottom: 1px dashed var(--line);">
          <div>
            <div class="bold small">Birchwood Heavy Duty Spoon &amp; Fork Kits</div>
            <div class="tiny muted">1.5 sets per guest</div>
          </div>
          <div class="right">
            <span class="badge badge-pine">450 sets</span>
            <div class="small mono bold tnum" style="margin-top: 2px;">₹810.00</div>
          </div>
        </div>

        <div class="flex items-center justify-between" style="padding-bottom: 10px; border-bottom: 1px dashed var(--line);">
          <div>
            <div class="bold small">250ml Ripple Wall Beverage Cups</div>
            <div class="tiny muted">Welcome drink + Water + Coffee / Tea</div>
          </div>
          <div class="right">
            <span class="badge badge-pine">750 pcs</span>
            <div class="small mono bold tnum" style="margin-top: 2px;">₹2,700.00</div>
          </div>
        </div>

        <div class="flex items-center justify-between" style="padding-bottom: 10px; border-bottom: 1px dashed var(--line);">
          <div>
            <div class="bold small">2-Ply Kraft Pop-up Napkins</div>
            <div class="tiny muted">3 napkins per guest</div>
          </div>
          <div class="right">
            <span class="badge badge-pine">1,000 pcs</span>
            <div class="small mono bold tnum" style="margin-top: 2px;">₹400.00</div>
          </div>
        </div>

      </div>

      <!-- Total Kit Cost -->
      <div style="background-color: var(--pine-wash); padding: 18px; border-radius: var(--radius); margin-top: 24px;">
        <div class="flex items-center justify-between">
          <div>
            <div class="tiny bold muted" style="text-transform: uppercase;">Estimated Kit Wholesale Total:</div>
            <div class="bold mono tnum" style="font-size: 1.8rem; color: var(--pine-dark);">₹8,805.00</div>
            <div class="tiny muted">+ 18% GST (₹1,584.90) · ₹35.22 cost per guest</div>
          </div>
          <button 
            type="button" 
            class="btn btn-clay btn-lg" 
            data-action="add-to-cart"
            data-id="CALC-KIT-250"
            data-name="250-Guest Wedding Reception Complete Consumables Kit"
            data-pack="3,150 Total Pieces in Master Carton"
            data-pcs="250"
            data-price="8805.00"
            data-image="https://images.unsplash.com/photo-1584269600464-37b1b58a9fe7?w=200&auto=format&fit=crop&q=80"
          >
            Add 250-Guest Kit →
          </button>
        </div>
      </div>

    </div>

  </div>

</div>

<?php $view->stop(); ?>
