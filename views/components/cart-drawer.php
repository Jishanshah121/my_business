<!-- Slide-Out Cart Drawer Overlay -->
<div class="cart-drawer-overlay" id="cart-drawer-overlay">
  <div class="cart-drawer-panel cart-drawer" role="dialog" aria-modal="true" aria-label="Shopping Cart Drawer">
    <!-- Drawer Header -->
    <div class="drawer-header cart-drawer-header">
      <div class="flex items-center gap-2">
        <svg class="ui-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>
        </svg>
        <h3 style="font-size: 1.15rem; font-weight: 700; margin: 0;">Your Supply Basket</h3>
      </div>
      <button class="btn btn-ghost btn-sm close-cart-drawer" aria-label="Close Shopping Cart" style="padding: 4px 8px; font-size: 1.1rem; line-height: 1;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="18" y1="6" x2="6" y2="18"></line>
          <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
      </button>
    </div>

    <!-- Free Delivery Progress -->
    <div style="background-color: var(--pine-wash); padding: 12px 20px; border-bottom: 1px solid var(--pine-tint);">
      <div class="small" id="free-shipping-text" style="color: var(--pine-dark); line-height: 1.3;">
        Add <strong>₹500</strong> more to unlock <strong>Free Express Delivery</strong>
      </div>
      <div class="free-ship-bar shipping-progress-bar">
        <div class="free-ship-fill shipping-progress-fill" id="free-shipping-fill" style="width: 70%;"></div>
      </div>
    </div>

    <!-- Line Items List -->
    <div class="drawer-body cart-drawer-body" id="cart-items-container">
      <!-- Items dynamically populated by window.SupplyKaro.Cart -->
    </div>

    <!-- Drawer Footer & Checkout CTA -->
    <div class="drawer-footer cart-drawer-footer">
      <div class="flex items-center justify-between small" style="margin-bottom: 6px;">
        <span class="muted">Subtotal (Pack Base):</span>
        <span class="bold mono tnum" id="cart-subtotal-val">₹0.00</span>
      </div>
      <div class="flex items-center justify-between small" style="margin-bottom: 12px;">
        <span class="muted">Estimated GST (18% HSN):</span>
        <span class="bold mono tnum" id="cart-gst-val">₹0.00</span>
      </div>
      <div class="flex items-center justify-between" style="padding-top: 10px; border-top: 1px solid var(--line); margin-bottom: 16px;">
        <span style="font-weight: 700; font-size: 1.05rem;">Estimated Total:</span>
        <span class="bold mono tnum" style="font-size: 1.25rem; color: var(--pine);" id="cart-total-val">₹0.00</span>
      </div>

      <div class="grid grid-2 gap-2">
        <a href="/cart" class="btn btn-secondary btn-block">View Full Cart</a>
        <a href="/checkout" class="btn btn-primary btn-block">Proceed to Pay →</a>
      </div>
      <div class="center tiny muted flex items-center justify-center gap-1" style="margin-top: 10px;">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
          <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
        </svg>
        <span>256-Bit SSL Secured Checkout · Instant GST Invoicing</span>
      </div>
    </div>
  </div>
</div>
