<?php /** @var App\Support\View $view */ ?><!doctype html>
<html lang="en-IN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $view->e($status) ?> · SupplyKaro</title>
<meta name="robots" content="noindex">
<link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
<header class="site-header"><div class="wrap"><a class="brand" href="/"><span class="brand-mark">S</span> SupplyKaro</a></div></header>
<main class="auth-shell">
  <div class="auth-card center">
    <div style="font-size:3.4rem;font-weight:700;letter-spacing:-.03em;color:var(--pine)"><?= $view->e($status) ?></div>
    <h1 style="margin-top:8px;font-size:1.25rem"><?= $view->e($message) ?></h1>
    <div class="actions" style="justify-content:center;margin-top:22px">
      <a class="btn btn-primary" href="/">Back to home</a>
    </div>
    <?php if (($exception ?? null) !== null): ?>
      <div class="card" style="margin-top:26px;text-align:left">
        <div class="card-title">Debug — <?= $view->e($exception::class) ?></div>
        <p class="small mono" style="margin-top:8px;word-break:break-word"><?= $view->e($exception->getMessage()) ?></p>
        <p class="tiny muted mono"><?= $view->e($exception->getFile()) ?>:<?= $view->e($exception->getLine()) ?></p>
        <pre class="tiny mono" style="overflow-x:auto;background:var(--surface-2);padding:12px;border-radius:8px"><?= $view->e($exception->getTraceAsString()) ?></pre>
      </div>
    <?php endif; ?>
  </div>
</main>
</body>
</html>
