<?php 
/** 
 * @var App\Support\View $view 
 * @var array $plates
 * @var array $cups
 * @var array $cutlery
 * @var array $tissues
 */ 
$view->extend('layouts/app'); 
$view->start('content'); 
?>

<div class="wrap" style="padding-top: 32px; padding-bottom: 80px;">
  
  <!-- Header -->
  <div class="center" style="max-width: 680px; margin: 0 auto 36px;">
    <div class="badge badge-gold" style="margin-bottom: 8px;">Interactive Kit Engine</div>
    <h1>Build Your Custom Party Box</h1>
    <p class="muted">
      Select your guest count, customize tableware, and let our zero-shortage formula calculate exact quantities with safety margins included.
    </p>
  </div>

  <div class="wizard-container">
    
    <!-- Step Bar -->
    <div class="wizard-steps-bar">
      <div class="wizard-step active">
        <div class="wizard-step-num">1</div>
        <span>Guest Count &amp; Buffer</span>
      </div>
      <div class="wizard-step active">
        <div class="wizard-step-num">2</div>
        <span>Customize Consumables</span>
      </div>
      <div class="wizard-step active">
        <div class="wizard-step-num">3</div>
        <span>Ready-to-Ship Box</span>
      </div>
    </div>

    <!-- Step 1: Guest Count Slider -->
    <div style="margin-bottom: 32px; text-align: center;">
      <label class="bold small muted" style="text-transform: uppercase;">1. Adjust Number of Attendees / Guests:</label>
      <div style="font-size: 3rem; font-weight: 800; color: var(--pine); margin: 8px 0;" id="party-box-guest-val">50 Guests</div>
      
      <input 
        type="range" 
        id="party-box-slider" 
        class="slider-control" 
        min="10" 
        max="300" 
        step="10" 
        value="50"
      >

      <div class="flex items-center justify-center gap-4 tiny muted" style="margin-top: 10px;">
        <span>Min: 10 Guests</span>
        <span>•</span>
        <span style="color: var(--pine-dark); font-weight: 700;">+15% Safety Buffer Auto-Applied</span>
        <span>•</span>
        <span>Max: 300 Guests</span>
      </div>
    </div>

    <!-- Step 2: Role Selectors -->
    <h3 style="margin-bottom: 16px;">Consumables Breakdown</h3>
    <div class="role-selection-grid">
      
      <!-- Role 1: Dinner Plates -->
      <div class="role-card selected">
        <div class="flex items-center justify-between" style="margin-bottom: 8px;">
          <strong style="font-size: 1rem;">Dinner Plates</strong>
          <span class="badge badge-pine" id="role-plates-qty">60 pcs</span>
        </div>
        <p class="tiny muted" style="margin-bottom: 10px;">10" Premium Areca Palm Leaf Biodegradable Plate</p>
        <div class="bold small mono tnum" style="color: var(--pine);">₹8.50 / pc · ₹510 total</div>
      </div>

      <!-- Role 2: Beverage Cups -->
      <div class="role-card selected">
        <div class="flex items-center justify-between" style="margin-bottom: 8px;">
          <strong style="font-size: 1rem;">Beverage Cups</strong>
          <span class="badge badge-pine" id="role-cups-qty">100 pcs</span>
        </div>
        <p class="tiny muted" style="margin-bottom: 10px;">250ml Ripple Wall Insulated Hot &amp; Cold Kraft Cup</p>
        <div class="bold small mono tnum" style="color: var(--pine);">₹3.60 / pc · ₹360 total</div>
      </div>

      <!-- Role 3: Wooden Cutlery -->
      <div class="role-card selected">
        <div class="flex items-center justify-between" style="margin-bottom: 8px;">
          <strong style="font-size: 1rem;">Wooden Cutlery</strong>
          <span class="badge badge-pine" id="role-forks-qty">100 pcs</span>
        </div>
        <p class="tiny muted" style="margin-bottom: 10px;">160mm Birchwood Fork &amp; Spoon Set (Individually Packed)</p>
        <div class="bold small mono tnum" style="color: var(--pine);">₹1.80 / pc · ₹180 total</div>
      </div>

      <!-- Role 4: Table Tissues -->
      <div class="role-card selected">
        <div class="flex items-center justify-between" style="margin-bottom: 8px;">
          <strong style="font-size: 1rem;">Table Napkins</strong>
          <span class="badge badge-pine" id="role-napkins-qty">150 pcs</span>
        </div>
        <p class="tiny muted" style="margin-bottom: 10px;">2-Ply Unbleached Natural Kraft Pop-up Napkins</p>
        <div class="bold small mono tnum" style="color: var(--pine);">₹0.40 / pc · ₹60 total</div>
      </div>

      <!-- Role 5: Dessert / Snack Plates -->
      <div class="role-card selected">
        <div class="flex items-center justify-between" style="margin-bottom: 8px;">
          <strong style="font-size: 1rem;">Snack Platters</strong>
          <span class="badge badge-pine">50 pcs</span>
        </div>
        <p class="tiny muted" style="margin-bottom: 10px;">6" Bagasse Square Snack &amp; Cake Plates</p>
        <div class="bold small mono tnum" style="color: var(--pine);">₹3.20 / pc · ₹160 total</div>
      </div>

      <!-- Role 6: Garbage & Cleanliness -->
      <div class="role-card selected">
        <div class="flex items-center justify-between" style="margin-bottom: 8px;">
          <strong style="font-size: 1rem;">Cleanup Kit</strong>
          <span class="badge badge-pine">10 pcs</span>
        </div>
        <p class="tiny muted" style="margin-bottom: 10px;">Heavy Duty Biodegradable Garbage Bags (30x40")</p>
        <div class="bold small mono tnum" style="color: var(--pine);">₹6.00 / pc · ₹60 total</div>
      </div>

    </div>

    <!-- Step 3: Total Box Summary & Action -->
    <div style="background-color: var(--surface-2); border-radius: var(--radius); padding: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: gap-4;">
      <div>
        <div class="tiny bold muted" style="text-transform: uppercase;">Total Party Box Price (Excl. 18% GST):</div>
        <div class="flex items-baseline gap-2">
          <span class="price-headline mono tnum" id="party-box-total-display" style="font-size: 2.2rem; color: var(--pine-dark);">
            ₹1,330
          </span>
          <span class="tiny muted" id="party-box-items-count">470 total pieces in one master box</span>
        </div>
      </div>

      <div class="flex items-center gap-3">
        <button 
          type="button" 
          class="btn btn-clay btn-lg" 
          id="add-party-box-to-cart"
          data-action="add-to-cart"
          data-id="PARTY-BOX-470"
          data-name="Curated 50-Guest Celebration Party Box"
          data-pack="Complete 470-Piece All-in-One Master Box"
          data-pcs="470"
          data-price="1330.00"
          data-image="https://images.unsplash.com/photo-1530103862676-de8c9debad1d?w=400&auto=format&fit=crop&q=80"
        >
          Add Complete Party Box to Cart →
        </button>
      </div>
    </div>

  </div>

</div>

<?php $view->stop(); ?>
