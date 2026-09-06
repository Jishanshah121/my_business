<?php
/** @var App\Support\View $view */
?><!doctype html>
<html lang="en-IN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $view->e($title ?? 'Account') ?> · SupplyKaro</title>
<meta name="robots" content="noindex, nofollow">
<meta name="csrf-token" content="<?= $view->e($csrf->token()) ?>">
<meta name="auth-status" content="guest">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/app.css">
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' rx='8' fill='%23008000'/><text x='16' y='22' font-family='sans-serif' font-size='16' font-weight='700' fill='white' text-anchor='middle'>S</text></svg>">
</head>
<body class="auth-page-body">

<div class="auth-split-layout <?= ($wide ?? false) ? 'auth-wide-mode' : '' ?>">
  <!-- Left Brand & Social Proof Hero Pane -->
  <aside class="auth-hero-pane">
    <div class="auth-hero-top">
      <a class="auth-brand-logo" href="/">
        <span class="auth-brand-mark">S</span>
        <span class="auth-brand-name">SupplyKaro</span>
      </a>
      <div class="auth-brand-pill">B2B Trade Network</div>
    </div>

    <div class="auth-hero-main">
      <div class="auth-hero-badge">
        <span class="auth-badge-dot"></span>
        <span>Trusted by 1,200+ Businesses in India</span>
      </div>
      
      <h2 class="auth-hero-title">Procure packaging & disposable supplies at true wholesale rates.</h2>
      <p class="auth-hero-desc">From biodegradable food containers and ripple cups to customized event packaging with GST invoices and instant delivery.</p>

      <!-- Trust Metrics Grid -->
      <div class="auth-metrics-grid">
        <div class="auth-metric-box">
          <div class="auth-metric-num">₹50Cr+</div>
          <div class="auth-metric-lbl">Supplies Delivered</div>
        </div>
        <div class="auth-metric-box">
          <div class="auth-metric-num">24h</div>
          <div class="auth-metric-lbl">Dispatch Guarantee</div>
        </div>
        <div class="auth-metric-box">
          <div class="auth-metric-num">100%</div>
          <div class="auth-metric-lbl">GST Input Credit</div>
        </div>
      </div>

      <!-- Social Proof Testimonial Card -->
      <div class="auth-review-card">
        <div class="auth-review-stars">★★★★★</div>
        <p class="auth-review-text">"SupplyKaro simplified our entire packaging supply chain across 14 cafe outlets. No minimum order pressure, fast Razorpay settlements, and pristine quality."</p>
        <div class="auth-review-author">
          <div class="auth-author-avatar">RK</div>
          <div>
            <div class="auth-author-name">Rohit Kapoor</div>
            <div class="auth-author-title">Operations Head, ChaiBistro Labs</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Security & Trust Badges -->
    <div class="auth-hero-foot">
      <div class="auth-trust-badges">
        <div class="auth-trust-item">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          <span>256-Bit SSL</span>
        </div>
        <div class="auth-trust-dot">·</div>
        <div class="auth-trust-item">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          <span>Razorpay Verified</span>
        </div>
        <div class="auth-trust-dot">·</div>
        <div class="auth-trust-item">
          <span>ISO 9001 Quality</span>
        </div>
      </div>
    </div>
  </aside>

  <!-- Right Form Container -->
  <main class="auth-form-pane">
    <div class="auth-form-inner <?= ($wide ?? false) ? 'auth-form-wide' : '' ?>">
      <div class="auth-mobile-logo-bar">
        <a class="auth-brand-logo" href="/">
          <span class="auth-brand-mark">S</span>
          <span class="auth-brand-name">SupplyKaro</span>
        </a>
      </div>

      <?= $view->include('components/flash', ['flash' => $flash, 'view' => $view]) ?>
      <?= $view->section('content') ?>
      
      <div class="auth-form-foot-note">
        <span>© <?= date('Y') ?> SupplyKaro Technologies Pvt. Ltd. · All rights reserved</span>
      </div>
    </div>
  </main>
</div>

<script src="/assets/js/app.js"></script>
</body>
</html>
