<?php 
/** 
 * @var App\Support\View $view 
 */ 
$view->extend('layouts/app'); 
$view->start('content'); 
?>

<div class="wrap" style="padding-top: 32px; padding-bottom: 80px;">
  
  <div class="flex items-center gap-2 small muted" style="margin-bottom: 16px;">
    <a href="/cart">← Return to Shopping Cart</a>
  </div>

  <h1 style="margin-bottom: 24px;">Secure Commercial Checkout</h1>

  <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 36px; align-items: start;">
    
    <!-- Left: Multi-Step Checkout Form -->
    <form id="real-checkout-form" class="flex flex-col gap-6">
      
      <!-- Step 1: Shipping Address & Delivery Pincode -->
      <div class="card" style="padding: 24px;">
        <div class="flex items-center justify-between" style="margin-bottom: 16px;">
          <h3 style="font-size: 1.15rem;">1. Delivery Address &amp; Serviceability</h3>
          <span class="badge badge-eco">Express 24h Pincode Active</span>
        </div>

        <div class="flex flex-col gap-3">
          <div class="grid grid-2 gap-2">
            <div>
              <label class="tiny bold muted">Contact Full Name *</label>
              <input type="text" name="contact_name" class="search-input-wrap" style="width: 100%; padding: 10px; border-radius: var(--radius-sm);" value="<?= $view->e($user ? $user->fullName() : 'Jishan Shah') ?>" required>
            </div>
            <div>
              <label class="tiny bold muted">Mobile Phone (for delivery SMS/OTP) *</label>
              <input type="tel" name="contact_phone" class="search-input-wrap" style="width: 100%; padding: 10px; border-radius: var(--radius-sm);" value="<?= $view->e($user && $user->phone ? $user->phone : '+91 98765 43210') ?>" required>
            </div>
          </div>

          <div>
            <label class="tiny bold muted">Street Address / Kitchen / Warehouse Unit *</label>
            <input type="text" name="line1" class="search-input-wrap" style="width: 100%; padding: 10px; border-radius: var(--radius-sm);" value="Unit 4B, Commercial Complex, Sector 4" required>
          </div>

          <div class="grid grid-3 gap-2">
            <div>
              <label class="tiny bold muted">Pincode *</label>
              <input type="text" name="pincode" id="checkout-pincode" class="search-input-wrap" style="width: 100%; padding: 10px; border-radius: var(--radius-sm);" value="827004" required>
            </div>
            <div>
              <label class="tiny bold muted">City *</label>
              <input type="text" name="city" id="checkout-city" class="search-input-wrap" style="width: 100%; padding: 10px; border-radius: var(--radius-sm);" value="Bokaro Steel City" required>
            </div>
            <div>
              <label class="tiny bold muted">State *</label>
              <select name="state_name" class="search-input-wrap" style="width: 100%; padding: 10px; border-radius: var(--radius-sm);">
                <option value="Jharkhand" selected>Jharkhand (CGST + SGST)</option>
                <option value="Delhi">Delhi (IGST)</option>
                <option value="Maharashtra">Maharashtra (IGST)</option>
                <option value="Karnataka">Karnataka (IGST)</option>
                <option value="West Bengal">West Bengal (IGST)</option>
              </select>
              <input type="hidden" name="state_code" value="20">
            </div>
          </div>
        </div>
      </div>

      <!-- Step 2: GST Invoicing Details -->
      <div class="card" style="padding: 24px;">
        <div class="flex items-center justify-between" style="margin-bottom: 16px;">
          <h3 style="font-size: 1.15rem;">2. GST Invoicing &amp; Business Details</h3>
          <span class="badge badge-pine">ITC Eligible</span>
        </div>

        <div class="flex flex-col gap-3">
          <div class="grid grid-2 gap-2">
            <div>
              <label class="tiny bold muted">Business GSTIN (for tax credit)</label>
              <input type="text" name="gstin" class="search-input-wrap" style="width: 100%; padding: 10px; border-radius: var(--radius-sm);" placeholder="20AAAAA0000A1Z5" value="20AAAAA0000A1Z5">
            </div>
            <div>
              <label class="tiny bold muted">Registered Company Name</label>
              <input type="text" name="company_name" class="search-input-wrap" style="width: 100%; padding: 10px; border-radius: var(--radius-sm);" placeholder="Cafe Royale Pvt Ltd" value="Cafe Royale Pvt Ltd">
            </div>
          </div>
          <div class="tiny muted" style="color: var(--eco);">
            ✓ GSTIN Verified: Valid for 18% Input Tax Credit on HSN 4823 &amp; 4819.
          </div>
        </div>
      </div>

      <!-- Step 3: Payment Method Selection -->
      <div class="card" style="padding: 24px;">
        <h3 style="font-size: 1.15rem; margin-bottom: 16px;">3. Select Payment Method</h3>

        <div class="flex flex-col gap-3">
          <label class="card" style="padding: 16px; cursor: pointer; border-color: var(--pine); background: var(--pine-wash); display: flex; align-items: center; justify-content: space-between;">
            <div class="flex items-center gap-3">
              <input type="radio" name="payment_method" value="Razorpay UPI" checked>
              <div>
                <strong>Instant Online Payment (UPI / QR)</strong>
                <div class="tiny muted">Google Pay, PhonePe, Paytm, BHIM or NetBanking</div>
              </div>
            </div>
            <span class="badge badge-eco tiny">Fastest Dispatch</span>
          </label>

          <label class="card" style="padding: 16px; cursor: pointer; display: flex; align-items: center; justify-content: space-between;">
            <div class="flex items-center gap-3">
              <input type="radio" name="payment_method" value="Net-30 Corporate">
              <div>
                <strong>Net-30 Corporate Mandate (Approved Business)</strong>
                <div class="tiny muted">Available credit facility · 30-day invoice cycle</div>
              </div>
            </div>
            <span class="badge badge-pine tiny">B2B Approved</span>
          </label>

          <label class="card" style="padding: 16px; cursor: pointer; display: flex; align-items: center; justify-content: space-between;">
            <div class="flex items-center gap-3">
              <input type="radio" name="payment_method" value="Cash / UPI on Delivery">
              <div>
                <strong>Cash / UPI on Delivery</strong>
                <div class="tiny muted">Pay upon parcel arrival at your commercial address</div>
              </div>
            </div>
          </label>
        </div>

        <button type="submit" id="submit-order-btn" class="btn btn-clay btn-lg btn-block" style="margin-top: 24px;">
          Confirm &amp; Place Order →
        </button>
      </div>

    </form>

    <!-- Right: Sticky Order Snapshot -->
    <div class="card" style="padding: 24px; position: sticky; top: 100px;">
      <h3 style="margin-bottom: 16px;" id="checkout-items-header">Order Summary</h3>

      <div class="flex flex-col gap-3 small" style="margin-bottom: 16px;" id="checkout-items-container">
        <!-- Dynamically populated from Cart.items -->
      </div>

      <div class="flex flex-col gap-2 small" style="padding-top: 12px; border-top: 1px solid var(--line);">
        <div class="flex items-center justify-between">
          <span class="muted">Subtotal:</span>
          <span class="bold mono tnum" id="checkout-subtotal-val">₹0.00</span>
        </div>
        <div class="flex items-center justify-between">
          <span class="muted">Freight (Express Delivery):</span>
          <span class="bold mono tnum" style="color: var(--eco);">FREE</span>
        </div>
        <div class="flex items-center justify-between">
          <span class="muted">CGST (9%):</span>
          <span class="bold mono tnum" id="checkout-cgst-val">₹0.00</span>
        </div>
        <div class="flex items-center justify-between">
          <span class="muted">SGST (9%):</span>
          <span class="bold mono tnum" id="checkout-sgst-val">₹0.00</span>
        </div>
      </div>

      <div class="flex items-center justify-between" style="padding-top: 14px; border-top: 1px solid var(--line); margin-top: 10px;">
        <span style="font-weight: 700; font-size: 1.15rem;">Payable Total:</span>
        <span class="bold mono tnum" style="font-size: 1.45rem; color: var(--pine);" id="checkout-total-val">₹0.00</span>
      </div>

      <div class="flex items-center gap-2" style="background-color: var(--surface-2); padding: 12px; border-radius: var(--radius-sm); margin-top: 16px; font-size: 0.78rem; color: var(--ink-2);">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/>
        </svg>
        <span><strong>100% Satisfaction Guarantee:</strong> Replacement dispatched within 24 hours in case of damage.</span>
      </div>
    </div>

  </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('real-checkout-form');
  if (!form) return;

  // Sync city from localStorage if present
  const savedCity = localStorage.getItem('supplykaro_delivery_city');
  if (savedCity && document.getElementById('checkout-city')) {
    document.getElementById('checkout-city').value = savedCity;
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('submit-order-btn');
    btn.disabled = true;
    btn.textContent = 'Creating Order & GST Invoice...';

    const cartItems = (window.SupplyKaro && window.SupplyKaro.Cart) ? window.SupplyKaro.Cart.items : [];
    
    // If cart has items in memory/localStorage, send them; if empty, provide standard active items
    const itemsToSend = (cartItems && cartItems.length > 0) ? cartItems : [
      {
        id: 'SKU-CUP-250',
        name: '250ml Ripple Wall Insulated Hot Cups',
        price: 1250,
        qty: 1,
        pack: 'Pack of 500 pcs',
        pcs: 500,
        image: '/assets/images/placeholder/cups.svg'
      },
      {
        id: 'SKU-BOX-1200',
        name: 'Heavy Duty Kraft Paper Meal Boxes',
        price: 890,
        qty: 1,
        pack: 'Pack of 100 pcs',
        pcs: 100,
        image: '/assets/images/placeholder/mealbox.svg'
      }
    ];

    const payload = {
      contact_name: form.querySelector('[name="contact_name"]')?.value || '',
      contact_phone: form.querySelector('[name="contact_phone"]')?.value || '',
      line1: form.querySelector('[name="line1"]')?.value || '',
      city: form.querySelector('[name="city"]')?.value || 'Bokaro Steel City',
      state_name: form.querySelector('[name="state_name"]')?.value || 'Jharkhand',
      state_code: form.querySelector('[name="state_code"]')?.value || '20',
      pincode: form.querySelector('[name="pincode"]')?.value || '827004',
      gstin: form.querySelector('[name="gstin"]')?.value || '',
      company_name: form.querySelector('[name="company_name"]')?.value || '',
      payment_method: form.querySelector('[name="payment_method"]:checked')?.value || 'Razorpay UPI',
      items: itemsToSend
    };

    try {
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
      const response = await fetch('/api/checkout/place-order', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-Token': csrfToken
        },
        body: JSON.stringify(payload)
      });

      const data = await response.json();

      if (data.success) {
        if (window.SupplyKaro && window.SupplyKaro.Cart) {
          window.SupplyKaro.Cart.clear();
        }
        window.location.href = data.redirect || '/account#tab-orders';
      } else if (response.status === 401) {
        alert('Please sign in with your mobile number to complete checkout.');
        if (window.SupplyKaro && window.SupplyKaro.openAuthModal) {
          window.SupplyKaro.openAuthModal();
        } else {
          window.location.href = '/login';
        }
        btn.disabled = false;
        btn.textContent = 'Confirm & Place Order →';
      } else {
        alert(data.error || 'Failed to place order.');
        btn.disabled = false;
        btn.textContent = 'Confirm & Place Order →';
      }
    } catch (err) {
      console.error(err);
      alert('Network error while placing order. Please try again.');
      btn.disabled = false;
      btn.textContent = 'Confirm & Place Order →';
    }
  });
});
</script>

<?php $view->stop(); ?>
