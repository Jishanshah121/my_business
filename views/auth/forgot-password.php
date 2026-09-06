<?php /** @var App\Support\View $view */ $view->extend('layouts/auth', ['title' => 'Reset Password']); ?>
<?php $view->start('content'); ?>

<div class="auth-head">
  <span class="auth-pill-tag">Account Security</span>
  <h1>Reset your password</h1>
  <p>Enter your registered email address and we'll send a secure one-time password reset link.</p>
</div>

<div class="card auth-main-card">
  <form method="post" action="/forgot-password" novalidate>
    <?= $view->raw($csrf->field()) ?>

    <?= $view->include('components/field', [
        'view' => $view, 'errors' => $errors, 'old' => $old,
        'name' => 'email', 'label' => 'Registered Email address', 'type' => 'email',
        'required' => true, 'autocomplete' => 'email',
        'placeholder' => 'e.g. rohit@company.com'
    ]) ?>

    <button type="submit" class="btn btn-primary btn-block btn-lg auth-submit-btn">
      <span>Send Password Reset Link</span>
      <span class="auth-btn-arrow">→</span>
    </button>
  </form>
</div>

<div class="auth-alt-box">
  <p class="auth-alt-line">
    Remember your password? <a href="/login" class="auth-link-bold">Back to Sign In</a>
  </p>
</div>

<?php $view->stop(); ?>
