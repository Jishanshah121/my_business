<?php
/**
 * Modern Amazon/Flipkart-grade Admin Console Layout
 * @var App\Support\View $view
 * @var App\Models\User $user
 */
?><!doctype html>
<html lang="en-IN">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $view->e($title ?? 'Admin Console') ?> · SupplyKaro Seller Studio</title>
  <meta name="robots" content="noindex, nofollow">
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/app.css">
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' rx='8' fill='%23008000'/><text x='16' y='22' font-family='sans-serif' font-size='16' font-weight='700' fill='white' text-anchor='middle'>S</text></svg>">
</head>
<body class="admin-app-body">

<div class="admin-layout">
  <!-- SIDEBAR NAVIGATION (Amazon Seller Central / Flipkart Hub Style) -->
  <aside class="admin-sidebar">
    <div class="admin-sidebar-brand">
      <a href="/admin" class="admin-brand-link">
        <span class="admin-brand-mark">S</span>
        <div class="admin-brand-text">
          <strong>SupplyKaro</strong>
          <span class="admin-hub-badge">Seller Studio</span>
        </div>
      </a>
    </div>

    <nav class="admin-nav-menu">
      <div class="admin-nav-section-label">MANAGEMENT</div>
      
      <?php $currUri = $_SERVER['REQUEST_URI'] ?? ''; ?>
      <a href="/admin" class="admin-nav-item <?= ($currUri === '/admin' ? 'active' : '') ?>">
        <span class="admin-nav-icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>
          </svg>
        </span>
        <span>Dashboard Overview</span>
      </a>

      <a href="/admin/products" class="admin-nav-item <?= (str_starts_with($currUri, '/admin/products') && !str_contains($currUri, '/create') ? 'active' : '') ?>">
        <span class="admin-nav-icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
            <path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>
          </svg>
        </span>
        <span>Catalog & Inventory</span>
      </a>

      <a href="/admin/products/create" class="admin-nav-item <?= (str_contains($currUri, '/admin/products/create') ? 'active' : '') ?>">
        <span class="admin-nav-icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
        </span>
        <span>Add New Product</span>
      </a>

      <a href="/admin/business-accounts" class="admin-nav-item <?= (str_contains($currUri, '/admin/business-accounts') ? 'active' : '') ?>">
        <span class="admin-nav-icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect width="20" height="14" x="2" y="7" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
          </svg>
        </span>
        <span>B2B Verification</span>
      </a>

      <div class="admin-nav-section-label" style="margin-top:20px;">STOREFRONT</div>

      <a href="/" target="_blank" class="admin-nav-item">
        <span class="admin-nav-icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>
          </svg>
        </span>
        <span>Live Customer Store</span>
        <span class="admin-nav-ext">↗</span>
      </a>
    </nav>

    <!-- Admin User Footer Info -->
    <div class="admin-sidebar-foot">
      <div class="admin-user-info">
        <div class="admin-user-avatar">
          <?= $view->e($user ? $user->initials() : 'A') ?>
        </div>
        <div class="admin-user-details">
          <strong><?= $view->e($user ? $user->fullName() : 'Administrator') ?></strong>
          <span><?= $view->e($user && !empty($user->roles()) ? $user->roles()[0] : 'Admin') ?></span>
        </div>
      </div>
      <form method="post" action="/logout" style="margin-top:10px;">
        <?= $view->raw($csrf->field()) ?>
        <button type="submit" class="admin-logout-btn">
          <span>Sign out</span>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
          </svg>
        </button>
      </form>
    </div>
  </aside>

  <!-- MAIN VIEW CANVAS -->
  <div class="admin-main-viewport">
    <!-- Top Header Bar -->
    <header class="admin-top-bar">
      <div class="admin-bar-left">
        <h1 class="admin-page-title"><?= $view->e($title ?? 'Admin Console') ?></h1>
      </div>

      <div class="admin-bar-right">
        <a href="/admin/products/create" class="btn btn-primary btn-sm admin-btn-action">
          <span>+ Add New Listing</span>
        </a>
      </div>
    </header>

    <!-- Page Content Container -->
    <main class="admin-content-area">
      <?= $view->include('components/flash', ['flash' => $flash, 'view' => $view]) ?>
      <?= $view->section('content') ?>
    </main>
  </div>
</div>

<script src="/assets/js/app.js"></script>
</body>
</html>
