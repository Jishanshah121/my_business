<?php
/**
 * Off-Canvas Mobile Drawer Menu
 * @var App\Support\View $view
 * @var App\Services\Auth\AuthService $auth
 */
$currentUser = $auth->user();
?>
<!-- Mobile Menu Backdrop -->
<div class="mobile-menu-backdrop" id="mobile-menu-backdrop">
  <div class="mobile-menu-drawer" id="mobile-menu-drawer" role="dialog" aria-modal="true" aria-label="Mobile Navigation Menu">
    <!-- Header -->
    <div class="mobile-menu-header">
      <div class="flex items-center gap-2">
        <a href="/" class="brand-logo">
          <div class="brand-name" style="font-size: 1.35rem;">Supply<span>Karo</span></div>
        </a>
      </div>
      <button type="button" class="btn btn-ghost btn-sm close-mobile-menu" aria-label="Close Menu" style="padding: 6px; border-radius: 50%;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="18" y1="6" x2="6" y2="18"></line>
          <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
      </button>
    </div>

    <!-- User Account Strip -->
    <div class="mobile-menu-user-strip">
      <?php if ($currentUser !== null): ?>
        <div class="flex items-center gap-3">
          <div class="user-avatar-circle">
            <?= strtoupper(substr($currentUser->name ?? 'U', 0, 1)) ?>
          </div>
          <div>
            <div class="bold" style="font-size: 0.95rem; color: var(--text);"><?= $view->e($currentUser->name ?? 'Customer') ?></div>
            <div class="muted small"><?= $view->e($currentUser->email ?? '') ?></div>
          </div>
        </div>
        <div class="flex gap-2" style="margin-top: 12px;">
          <a href="/account" class="btn btn-primary btn-sm btn-block">My Account</a>
          <a href="/logout" class="btn btn-secondary btn-sm" style="flex: 0 0 auto;">Logout</a>
        </div>
      <?php else: ?>
        <div class="flex items-center justify-between">
          <div>
            <div class="bold" style="font-size: 0.95rem;">Welcome to SupplyKaro</div>
            <div class="muted small">Login for wholesale rates & order tracking</div>
          </div>
        </div>
        <div class="flex gap-2" style="margin-top: 12px;">
          <button type="button" class="btn btn-primary btn-sm btn-block open-auth-modal-btn">Sign In / Register</button>
        </div>
      <?php endif; ?>
    </div>

    <!-- Quick Action / Location Selector -->
    <div class="mobile-menu-location open-location-modal" style="cursor: pointer;">
      <div class="flex items-center gap-2">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>
        </svg>
        <span class="small">Deliver to: <strong id="drawer-delivery-city">Bokaro, 827001</strong></span>
      </div>
      <span class="small bold" style="color: var(--primary);">Change</span>
    </div>

    <!-- Navigation Links -->
    <div class="mobile-menu-links">
      <div class="mobile-menu-group-title">Shop Categories</div>
      <a href="/categories" class="mobile-menu-link">
        <span class="mobile-menu-icon">📦</span>
        <span>All Products &amp; Catalog</span>
      </a>
      <a href="/category/tableware" class="mobile-menu-link">
        <span class="mobile-menu-icon">🍽️</span>
        <span>Disposable Tableware</span>
      </a>
      <a href="/category/cups-beverage" class="mobile-menu-link">
        <span class="mobile-menu-icon">☕</span>
        <span>Cups, Lids &amp; Straws</span>
      </a>
      <a href="/category/food-packaging" class="mobile-menu-link">
        <span class="mobile-menu-icon">🥡</span>
        <span>Food Packaging &amp; Boxes</span>
      </a>
      <a href="/category/tissues-hygiene" class="mobile-menu-link">
        <span class="mobile-menu-icon">🧻</span>
        <span>Paper Napkins &amp; Tissues</span>
      </a>
      <a href="/category/cleaning-housekeeping" class="mobile-menu-link">
        <span class="mobile-menu-icon">✨</span>
        <span>Cleaning &amp; Housekeeping</span>
      </a>

      <div class="mobile-menu-group-title" style="margin-top: 16px;">Special Services</div>
      <a href="/party-box" class="mobile-menu-link highlight">
        <span class="mobile-menu-icon">🎉</span>
        <div>
          <div class="bold">Build Your Party Box</div>
          <div class="tiny muted">Curated supplies for 10 to 500+ guests</div>
        </div>
      </a>
      <a href="/event-calculator" class="mobile-menu-link">
        <span class="mobile-menu-icon">🧮</span>
        <div>
          <div class="bold">Smart Event Calculator</div>
          <div class="tiny muted">Calculate exact quantities needed</div>
        </div>
      </a>
      <a href="/b2b" class="mobile-menu-link highlight-b2b">
        <span class="mobile-menu-icon">🏢</span>
        <div>
          <div class="bold">B2B Wholesale Portal</div>
          <div class="tiny muted">Direct factory prices &amp; GST ITC invoices</div>
        </div>
      </a>

      <div class="mobile-menu-group-title" style="margin-top: 16px;">Customer Support</div>
      <a href="https://wa.me/919999999999" class="mobile-menu-link whatsapp-link" target="_blank" rel="noopener">
        <span class="mobile-menu-icon">💬</span>
        <span>WhatsApp Support (Direct)</span>
      </a>
    </div>

    <!-- Footer Tagline -->
    <div class="mobile-menu-footer">
      <div class="tiny muted">SupplyKaro · India's Packaging Platform</div>
      <div class="tiny muted">GST Invoicing · 100% Food-Grade Certified</div>
    </div>
  </div>
</div>
