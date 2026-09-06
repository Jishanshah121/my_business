<?php /** @var App\Support\View $view */ $view->extend('layouts/auth', ['title' => 'Verify Email']); ?>
<?php $view->start('content'); ?>

<div class="auth-head">
  <span class="auth-pill-tag">Verification Required</span>
  <h1>Confirm your email</h1>
  <p>We've dispatched a secure verification link to <strong><?= $view->e($user?->email ?? 'your email address') ?></strong>.</p>
</div>

<div class="card auth-main-card">
  <div class="auth-verify-icon-box">
    <div class="verify-email-icon">
      <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M22 13V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v12c0 1.1.9 2 2 2h9"/><polyline points="22,6 12,13 2,6"/>
      </svg>
    </div>
  </div>
  
  <p class="auth-verify-text">
    Open the verification email and click the confirmation link to activate your full SupplyKaro account benefits, access automated GST invoices, and track your dispatches.
  </p>
  
  <div class="auth-notice-box" style="margin: 20px 0;">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
    <span>Link valid for 24 hours. Please check your promotions or spam folder if it doesn't arrive within 2 minutes.</span>
  </div>

  <form method="post" action="/email/verify/resend">
    <?= $view->raw($csrf->field()) ?>
    <button type="submit" class="btn btn-secondary btn-block btn-lg">Resend Verification Email</button>
  </form>
</div>

<div class="auth-alt-box">
  <p class="auth-alt-line">
    <a href="/account" class="auth-link-bold">Continue to Account Dashboard →</a>
  </p>
</div>

<?php $view->stop(); ?>
