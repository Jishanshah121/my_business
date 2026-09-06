<?php 
/** 
 * @var App\Support\View $view 
 */ 
$view->extend('layouts/app'); 
$view->start('content'); 
?>

<div class="wrap" style="padding-top: 32px; padding-bottom: 80px;">
  
  <div class="flex items-center gap-2 small muted" style="margin-bottom: 16px;">
    <a href="/">Home</a> <span>/</span> <span style="color: var(--ink);">Shopping Cart</span>
  </div>

  <h1 style="margin-bottom: 24px;">Your Packaging &amp; Supply Basket</h1>

  <!-- Empty Cart State -->
  <div id="full-cart-empty-state" class="card center" style="padding: 60px 24px; display: none;">
    <div style="margin-bottom: 16px;">
      <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--border-dark)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>
      </svg>
    </div>
    <h2>Your Supply Basket is Empty</h2>
    <p class="muted" style="max-width: 480px; margin: 10px auto 24px;">
      Explore our master catalog to add food-grade containers, greaseproof wrapping sheets, cups, and tableware.
    </p>
    <a href="/categories" class="btn btn-primary btn-lg">Explore Catalog Products →</a>
  </div>

  <!-- Full Cart Grid -->
  <div id="full-cart-content" style="display: grid; grid-template-columns: 1.6fr 1fr; gap: 32px; align-items: start;">
    
    <!-- Left: Cart Items List -->
    <div class="card" style="padding: 24px;">
      
      <!-- Free Shipping Meter -->
      <div style="background-color: var(--pine-wash); padding: 14px 18px; border-radius: var(--radius-sm); margin-bottom: 20px;">
        <div class="small" id="full-cart-ship-text" style="color: var(--pine-dark);">
          You qualify for <strong>Free Freight &amp; Same-Day Dispatch</strong>
        </div>
        <div class="shipping-progress-bar" style="margin-top: 6px;">
          <div class="shipping-progress-fill" id="full-cart-ship-fill" style="width: 100%;"></div>
        </div>
      </div>

      <!-- Dynamic Line Items Container -->
      <div class="flex flex-col gap-4" id="full-cart-items-container">
        <!-- Dynamically rendered by Cart.render() -->
      </div>

      <!-- Coupon Form -->
      <div class="flex items-center gap-2" style="margin-top: 24px; padding-top: 16px; border-top: 1px dashed var(--line);">
        <input type="text" class="search-input-wrap" style="padding: 8px 14px; border-radius: var(--radius-sm); max-width: 220px;" placeholder="Coupon Code (e.g. B2BFIRST)">
        <button class="btn btn-secondary btn-sm" onclick="window.SupplyKaro.Cart.showToast('Coupon B2BFIRST applied! Saved ₹200 on cart subtotal.');">Apply Promo</button>
      </div>

    </div>

    <!-- Right: Order Summary & Checkout -->
    <div class="card" style="padding: 24px; background: #FFFFFF; position: sticky; top: 100px;">
      <h3 style="margin-bottom: 16px;">Order Summary</h3>

      <div class="flex flex-col gap-3 small">
        <div class="flex items-center justify-between">
          <span class="muted">Cart Subtotal (Pack Base):</span>
          <span class="bold mono tnum" id="full-cart-subtotal-val">₹0.00</span>
        </div>
        <div class="flex items-center justify-between">
          <span class="muted">Estimated Delivery (Express):</span>
          <span class="bold mono tnum" style="color: var(--eco);">FREE</span>
        </div>
        <div class="flex items-center justify-between">
          <span class="muted">GST (18% HSN):</span>
          <span class="bold mono tnum" id="full-cart-gst-val">₹0.00</span>
        </div>

        <div class="flex items-center justify-between" style="padding-top: 14px; border-top: 1px solid var(--line); margin-top: 8px;">
          <span style="font-weight: 700; font-size: 1.15rem;">Grand Total:</span>
          <span class="bold mono tnum" style="font-size: 1.45rem; color: var(--pine);" id="full-cart-total-val">₹0.00</span>
        </div>
      </div>

      <a href="/checkout" class="btn btn-primary btn-lg btn-block" style="margin-top: 20px;">
        Proceed to Secure Checkout →
      </a>

      <div class="center tiny muted" style="margin-top: 14px;">
        Full GST Tax Invoice with your GSTIN generated immediately upon checkout.
      </div>
    </div>

  </div>

</div>

<?php $view->stop(); ?>
