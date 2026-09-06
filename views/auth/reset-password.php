<?php /** @var App\Support\View $view */ $view->extend('layouts/auth', ['title' => 'Choose New Password']); ?>
<?php $view->start('content'); ?>

<div class="auth-head">
  <span class="auth-pill-tag">Security Update</span>
  <h1>Choose a new password</h1>
  <p>This single-use reset link expires in 60 minutes for security purposes.</p>
</div>

<div class="card auth-main-card">
  <form method="post" action="/reset-password/<?= $view->e($token) ?>" novalidate>
    <?= $view->raw($csrf->field()) ?>

    <div class="auth-password-field-group">
      <?= $view->include('components/field', [
          'view' => $view, 'errors' => $errors, 'old' => $old,
          'name' => 'password', 'label' => 'New password', 'type' => 'password',
          'required' => true, 'autocomplete' => 'new-password',
          'placeholder' => 'At least 10 chars with a number/symbol',
          'attrs' => 'data-strength-target="resetStrengthMeter"'
      ]) ?>

      <div class="password-strength-container" id="resetStrengthMeter" style="display:none;">
        <div class="strength-track">
          <div class="strength-fill" id="resetStrengthFill"></div>
        </div>
        <div class="strength-label-row">
          <span class="strength-title">Security:</span>
          <span class="strength-status" id="resetStrengthStatus">Weak</span>
        </div>
      </div>
    </div>

    <?= $view->include('components/field', [
        'view' => $view, 'errors' => $errors, 'old' => $old,
        'name' => 'password_confirmation', 'label' => 'Confirm new password', 'type' => 'password',
        'required' => true, 'autocomplete' => 'new-password',
        'placeholder' => 'Re-enter your new password'
    ]) ?>

    <div class="auth-notice-box">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
      <span>Updating your password will sign you out of all other active sessions.</span>
    </div>

    <button type="submit" class="btn btn-primary btn-block btn-lg auth-submit-btn">
      <span>Save New Password & Sign In</span>
      <span class="auth-btn-arrow">→</span>
    </button>
  </form>
</div>

<?php $view->stop(); ?>
