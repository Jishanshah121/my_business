<?php
/** @var App\Support\View $view */
/** @var App\Services\Auth\AuthService $auth */
/** @var array<string,mixed> $flash */
$currentUser = $auth->user();
$path = $_SERVER['REQUEST_URI'] ?? '/';
?><!doctype html>
<html lang="en-IN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $view->e($title ?? 'SupplyKaro · Har Supply, Ek Jagah.') ?></title>
<meta name="description" content="<?= $view->e($metaDescription ?? 'SupplyKaro is India\'s B2B and B2C marketplace for disposable food-service supplies, packaging, tableware, catering, and event consumables.') ?>">
<meta name="robots" content="<?= $view->e($robots ?? 'index, follow') ?>">
<meta name="auth-status" content="<?= $currentUser !== null ? 'logged_in' : 'guest' ?>">
<meta name="user-id" content="<?= $currentUser ? $currentUser->id : '' ?>">
<meta name="csrf-token" content="<?= $view->e($csrf->token()) ?>">
<link rel="stylesheet" href="/assets/css/app.css">
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' rx='8' fill='%23008000'/><text x='16' y='22' font-family='sans-serif' font-size='16' font-weight='700' fill='white' text-anchor='middle'>S</text></svg>">
</head>
<body data-user-logged-in="<?= $currentUser !== null ? 'true' : 'false' ?>">
<a class="skip-link" href="#main" style="position:absolute;left:-9999px;">Skip to content</a>

<!-- Master Sticky Header -->
<?= $view->include('components/header', ['view' => $view, 'auth' => $auth]) ?>

<!-- Location Access Permission Modal (Image 4) -->
<?= $view->include('components/location-access-modal', ['view' => $view]) ?>

<!-- Location Selector Modal (City Search) -->
<?= $view->include('components/location-modal', ['view' => $view]) ?>

<!-- Auth Modal (Phone & OTP Verification - Images 2 & 3) -->
<?= $view->include('components/auth-modal', ['view' => $view]) ?>

<!-- Flash Messages -->
<?php if (!empty($flash['success']) || !empty($flash['error']) || !empty($flash['warning'])): ?>
  <div class="wrap" style="padding-top: 16px;">
    <?= $view->include('components/flash', ['flash' => $flash, 'view' => $view]) ?>
  </div>
<?php endif; ?>

<!-- Main Content Area -->
<main id="main">
  <?= $view->section('content') ?>
</main>

<!-- Slide-Out Cart Drawer -->
<?= $view->include('components/cart-drawer', ['view' => $view]) ?>

<!-- Master Footer -->
<?= $view->include('components/footer', ['view' => $view]) ?>

<!-- Core JavaScript Engine -->
<script src="/assets/js/app.js"></script>
</body>
</html>
