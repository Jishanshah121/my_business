<?php
/** @var App\Support\View $view */
/** @var App\Services\Auth\AuthService $auth */
$currentUser = $auth->user();
?>

<!-- Desktop Sticky Header -->
<header class="site-header">
  <div class="wrap header-container">
    
    <!-- LEFT: Logo & Dynamic Delivery Location -->
    <div class="header-left">
      <a href="/" class="brand-logo">
        <div class="brand-name">Supply<span>Karo</span></div>
        <div class="brand-tagline">Har Supply, Ek Jagah.</div>
      </a>

      <!-- Location Selector Trigger -->
      <button type="button" class="location-selector-btn open-location-modal" title="Change Delivery Location">
        <span class="loc-icon">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>
          </svg>
        </span>
        <div>
          <span class="loc-label">Delivering to</span>
          <span class="loc-city" id="header-delivery-city">Bokaro ▼</span>
        </div>
      </button>
    </div>

    <!-- CENTER: Navigation Menu -->
    <nav class="header-center-nav" aria-label="Main Navigation">
      <a href="/categories" class="nav-dropdown-trigger">
        Browse Catalogue <span style="font-size: 0.7rem;">▼</span>
      </a>
      <a href="/b2b#quote-form" class="header-link">Bulk Orders</a>
      <a href="/b2b" class="header-link">Business Supplies</a>
      <a href="/categories?eco=1" class="header-link nav-extra-link">Sustainability</a>
      <a href="/party-box" class="header-link nav-extra-link">Event Kits</a>
    </nav>

    <!-- RIGHT: Search, Auth & Cart -->
    <div class="header-right">
      <!-- Search Field -->
      <div class="search-bar-wrap">
        <form action="/categories" method="GET" class="header-search-form header-search-capsule" role="search">
          <button type="submit" class="search-input-icon-btn" aria-label="Search catalog">
            <svg class="search-icon-svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#FF4D4D" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="11" cy="11" r="7.5"/>
              <path d="m21 21-4.8-4.8"/>
            </svg>
          </button>
          <input 
            type="text" 
            name="q" 
            id="header-search-input" 
            class="search-input-field" 
            placeholder="Search items or categories" 
            autocomplete="off"
            value="<?= $view->e($_GET['q'] ?? '') ?>"
          >
        </form>

        <!-- Search Overlay Dropdown -->
        <div class="search-overlay-dropdown" id="search-overlay-dropdown">
          <div class="tiny bold muted" style="text-transform: uppercase;">Trending Searches</div>
          <div class="search-chips-group">
            <span class="search-chip-item" data-query="Ripple Wall Coffee Cup">Ripple Cups (250ml)</span>
            <span class="search-chip-item" data-query="Areca Palm Plate">Dona &amp; Pattal Plates</span>
            <span class="search-chip-item" data-query="Meal Delivery Box">Meal Delivery Boxes</span>
            <span class="search-chip-item" data-query="Brown Kraft Bag">Brown SOS Bags</span>
            <span class="search-chip-item" data-query="Birchwood">Wooden Cutlery</span>
            <span class="search-chip-item" data-query="Napkin">2-Ply Napkins</span>
            <span class="search-chip-item" data-query="Butter Paper">Butter Paper Sheets</span>
          </div>
        </div>
      </div>

      <!-- Login / Signup or Account -->
      <?php if ($currentUser !== null): ?>
        <a href="/account" class="btn btn-secondary btn-sm flex items-center gap-2">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
          </svg>
          <span><?= $view->e($currentUser->firstName() ?: 'Account') ?></span>
        </a>
      <?php else: ?>
        <a href="/login" class="login-signup-btn open-auth-modal">
          Login / Signup
        </a>
      <?php endif; ?>

      <!-- Cart Icon Trigger -->
      <button type="button" class="cart-icon-btn" aria-label="View Shopping Basket">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>
        </svg>
        <span class="cart-count-pill" style="display: none;">0</span>
      </button>
    </div>

  </div>

  <!-- Mobile Header Stack -->
  <div class="mobile-header-stack">
    <!-- Row 1 -->
    <div class="mobile-row-1">
      <a href="/categories" class="btn btn-ghost btn-sm" style="padding: 4px 8px;" aria-label="Menu">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/>
        </svg>
      </a>
      <a href="/" class="brand-logo" style="text-align: center;">
        <div class="brand-name" style="font-size: 1.35rem;">Supply<span>Karo</span></div>
      </a>
      <button type="button" class="cart-icon-btn" style="padding: 0;" aria-label="View Shopping Basket">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>
        </svg>
        <span class="cart-count-pill" style="display: none;">0</span>
      </button>
    </div>

    <!-- Row 2: Search Field -->
    <div class="mobile-row-2">
      <div class="search-bar-wrap">
        <form action="/categories" method="GET" class="header-search-form header-search-capsule mobile-search-form" role="search">
          <button type="submit" class="search-input-icon-btn" aria-label="Search catalog">
            <svg class="search-icon-svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#FF4D4D" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="11" cy="11" r="7.5"/>
              <path d="m21 21-4.8-4.8"/>
            </svg>
          </button>
          <input 
            type="text" 
            name="q" 
            class="search-input-field" 
            placeholder="Search items or categories" 
            value="<?= $view->e($_GET['q'] ?? '') ?>"
          >
        </form>
      </div>
    </div>

    <!-- Row 3: Delivery Location -->
    <div class="mobile-row-3 open-location-modal" style="cursor: pointer;">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>
      </svg>
      <span>Delivering to: <strong id="mobile-delivery-city">Bokaro, 827001</strong> ▼</span>
    </div>
  </div>
</header>
