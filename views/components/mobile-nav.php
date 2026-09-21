<?php
/**
 * Mobile Bottom Navigation Bar (Fixed App-Like Bottom Bar)
 * @var App\Support\View $view
 * @var App\Services\Auth\AuthService $auth
 */
$currentUser = $auth->user();
$currentUri = $_SERVER['REQUEST_URI'] ?? '/';
?>
<nav class="mobile-bottom-nav" aria-label="Mobile Navigation">
  <!-- 1. Home -->
  <a href="/" class="mobile-nav-item <?= $currentUri === '/' ? 'active' : '' ?>">
    <svg class="mobile-nav-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
      <polyline points="9 22 9 12 15 12 15 22"/>
    </svg>
    <span class="mobile-nav-label">Home</span>
  </a>

  <!-- 2. Categories -->
  <a href="/categories" class="mobile-nav-item <?= str_starts_with($currentUri, '/categories') || str_starts_with($currentUri, '/category') ? 'active' : '' ?>">
    <svg class="mobile-nav-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <rect width="7" height="7" x="3" y="3" rx="1"/>
      <rect width="7" height="7" x="14" y="3" rx="1"/>
      <rect width="7" height="7" x="14" y="14" rx="1"/>
      <rect width="7" height="7" x="3" y="14" rx="1"/>
    </svg>
    <span class="mobile-nav-label">Categories</span>
  </a>

  <!-- 3. Party Box / Events -->
  <a href="/party-box" class="mobile-nav-item <?= str_starts_with($currentUri, '/party-box') || str_starts_with($currentUri, '/event') ? 'active' : '' ?>">
    <div class="mobile-nav-icon-wrap" style="position: relative;">
      <svg class="mobile-nav-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="m7.5 4.27 9 5.15"/>
        <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
        <path d="m3.3 7 8.7 5 8.7-5"/>
        <path d="M12 22V12"/>
      </svg>
      <span class="nav-sparkle-dot"></span>
    </div>
    <span class="mobile-nav-label">Party Box</span>
  </a>

  <!-- 4. Cart Drawer Trigger -->
  <button type="button" class="mobile-nav-item cart-icon-btn" aria-label="Open Shopping Basket" style="background:none; border:none; padding:0; cursor:pointer;">
    <div class="mobile-nav-icon-wrap" style="position: relative;">
      <svg class="mobile-nav-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/>
        <path d="M3 6h18"/>
        <path d="M16 10a4 4 0 0 1-8 0"/>
      </svg>
      <span class="cart-count-pill mobile-bottom-cart-pill" style="display: none;">0</span>
    </div>
    <span class="mobile-nav-label">Basket</span>
  </button>

  <!-- 5. Account / Login -->
  <?php if ($currentUser !== null): ?>
    <a href="/account" class="mobile-nav-item <?= str_starts_with($currentUri, '/account') ? 'active' : '' ?>">
      <svg class="mobile-nav-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
        <circle cx="12" cy="7" r="4"/>
      </svg>
      <span class="mobile-nav-label">Account</span>
    </a>
  <?php else: ?>
    <button type="button" class="mobile-nav-item open-auth-modal-btn" aria-label="Login or Sign Up" style="background:none; border:none; padding:0; cursor:pointer;">
      <svg class="mobile-nav-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
        <polyline points="10 17 15 12 10 7"/>
        <line x1="15" x2="3" y1="12" y2="12"/>
      </svg>
      <span class="mobile-nav-label">Sign In</span>
    </button>
  <?php endif; ?>
</nav>
