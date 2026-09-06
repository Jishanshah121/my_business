<?php 
/** 
 * @var App\Support\View $view 
 */ 
$view->extend('layouts/app'); 
$view->start('content'); 
?>

<div class="wrap" style="padding-top: 32px; padding-bottom: 80px;">
  
  <!-- B2B Hero Section -->
  <div style="background: linear-gradient(135deg, #0F3827 0%, #16533B 100%); border-radius: var(--radius-lg); padding: 48px; color: #FFFFFF; margin-bottom: 48px;">
    <div style="max-width: 680px;">
      <span class="badge badge-clay" style="margin-bottom: 12px; font-weight: 700;">SupplyKaro Wholesale Desk</span>
      <h1 style="color: #FFFFFF; font-size: 2.5rem; margin-bottom: 16px;">Direct Factory Packaging Procurement for Food Businesses</h1>
      <p style="color: #D2E4DA; font-size: 1.15rem; line-height: 1.6; margin-bottom: 28px;">
        Powering 2,400+ cafes, cloud kitchens, QSR chains, and caterers across India with consolidated GST billing, volumetric freight optimization, and Net-30 credit terms.
      </p>
      <div class="flex items-center gap-3">
        <a href="#quote-form" class="btn btn-clay btn-lg">Request Custom Bulk Quote →</a>
        <a href="/register/business" class="btn btn-secondary btn-lg" style="background: rgba(255,255,255,0.1); color: #fff; border-color: rgba(255,255,255,0.25);">Open B2B Account</a>
      </div>
    </div>
  </div>

  <!-- 4 Pillars of SupplyKaro B2B -->
  <div class="grid grid-4" style="margin-bottom: 48px;">
    <div class="card" style="padding: 24px;">
      <div style="margin-bottom: 14px;">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="22 17 13.5 8.5 8.5 13.5 2 7"/><polyline points="16 17 22 17 22 11"/>
        </svg>
      </div>
      <h3 style="font-size: 1.1rem; margin-bottom: 8px;">Tiered Volume Pricing</h3>
      <p class="small muted">Automatic volume breaks on carton quantities. Save up to 35% compared to local mandi middlemen.</p>
    </div>

    <div class="card" style="padding: 24px;">
      <div style="margin-bottom: 14px;">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>
        </svg>
      </div>
      <h3 style="font-size: 1.1rem; margin-bottom: 8px;">100% GST Compliance</h3>
      <p class="small muted">Claim full Input Tax Credit (ITC) with valid HSN codes, downloadable PDFs, and monthly ledger exports.</p>
    </div>

    <div class="card" style="padding: 24px;">
      <div style="margin-bottom: 14px;">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/>
        </svg>
      </div>
      <h3 style="font-size: 1.1rem; margin-bottom: 8px;">Cadence Re-ordering</h3>
      <p class="small muted">Smart consumption tracking alerts you before you run out of cups or meal boxes, with 1-click reorder.</p>
    </div>

    <div class="card" style="padding: 24px;">
      <div style="margin-bottom: 14px;">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/>
        </svg>
      </div>
      <h3 style="font-size: 1.1rem; margin-bottom: 8px;">Net-30 Credit Terms</h3>
      <p class="small muted">Approved GSTIN-verified businesses unlock flexible 15-to-30 day credit limits with automated RTGS mandates.</p>
    </div>
  </div>

  <!-- B2B Quote & Sample Request Form -->
  <div id="quote-form" class="card" style="padding: 40px; background: #FFFFFF;">
    <div class="grid grid-2 gap-8">
      
      <div>
        <span class="badge badge-pine" style="margin-bottom: 8px; font-weight: 700;">2-Hour Response Time</span>
        <h2>Request a Custom Bulk Quote or Sample Kit</h2>
        <p class="muted" style="margin: 12px 0 24px;">
          Need bespoke custom-printed coffee cups, 25,000+ burger boxes, or want a physical sample box delivered to your kitchen? Fill out your business details below.
        </p>

        <div class="flex flex-col gap-3 small">
          <div class="flex items-center gap-3">
            <span style="color: var(--eco); font-size: 1.2rem;">✓</span>
            <span>Free Sample Box with 15+ packaging items for verified businesses.</span>
          </div>
          <div class="flex items-center gap-3">
            <span style="color: var(--eco); font-size: 1.2rem;">✓</span>
            <span>Dedicated Enterprise Key Account Manager.</span>
          </div>
          <div class="flex items-center gap-3">
            <span style="color: var(--eco); font-size: 1.2rem;">✓</span>
            <span>Custom branding &amp; multicolor offset printing facility.</span>
          </div>
        </div>
      </div>

      <form onsubmit="event.preventDefault(); window.SupplyKaro.Cart.showToast('Quote request submitted! Your account manager will contact you within 2 hours.');" class="flex flex-col gap-3">
        <div class="grid grid-2 gap-2">
          <div>
            <label class="tiny bold muted">Business / Company Name *</label>
            <input type="text" class="search-input-wrap" style="width: 100%; padding: 10px; border-radius: var(--radius-sm);" placeholder="e.g. Blue Tokai Roasters" required>
          </div>
          <div>
            <label class="tiny bold muted">Business GSTIN (Optional)</label>
            <input type="text" class="search-input-wrap" style="width: 100%; padding: 10px; border-radius: var(--radius-sm);" placeholder="07AAAAA0000A1Z5">
          </div>
        </div>

        <div class="grid grid-2 gap-2">
          <div>
            <label class="tiny bold muted">Contact Person Name *</label>
            <input type="text" class="search-input-wrap" style="width: 100%; padding: 10px; border-radius: var(--radius-sm);" placeholder="Jishan Shah" required>
          </div>
          <div>
            <label class="tiny bold muted">Phone Number (WhatsApp) *</label>
            <input type="tel" class="search-input-wrap" style="width: 100%; padding: 10px; border-radius: var(--radius-sm);" placeholder="+91 98765 43210" required>
          </div>
        </div>

        <div>
          <label class="tiny bold muted">Expected Monthly Packaging Spend</label>
          <select class="search-input-wrap" style="width: 100%; padding: 10px; border-radius: var(--radius-sm);">
            <option>₹25,000 – ₹50,000 / month</option>
            <option>₹50,000 – ₹2,00,000 / month</option>
            <option>₹2,00,000+ / month (Enterprise Contract)</option>
          </select>
        </div>

        <div>
          <label class="tiny bold muted">Items &amp; Quantities Required</label>
          <textarea class="search-input-wrap" style="width: 100%; height: 80px; padding: 10px; border-radius: var(--radius-sm);" placeholder="e.g., 250ml Ripple Cups (10 cartons), 3-comp meal boxes (5 cartons), custom printed kraft carry bags (5,000 pcs)"></textarea>
        </div>

        <button type="submit" class="btn btn-clay btn-lg" style="margin-top: 8px;">
          Submit Quote &amp; Sample Request →
        </button>
      </form>

    </div>
  </div>

</div>

<?php $view->stop(); ?>
