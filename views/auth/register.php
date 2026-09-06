<?php /** @var App\Support\View $view */ $view->extend('layouts/auth'); ?>
<?php $view->start('content'); ?>

<div class="auth-head">
  <span class="auth-pill-tag">Quick Registration</span>
  <h1>Create your account</h1>
  <p>Order premium packaging, cups and event disposables with instant checkout.</p>
</div>

<!-- Social / 1-Click Fast Auth -->
<div class="auth-social-wrap">
  <button type="button" class="btn-social-auth btn-google" onclick="alert('Google Sign-up will be activated with your client credentials.')">
    <svg width="18" height="18" viewBox="0 0 24 24">
      <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.8-2.4 3.66v3.05h3.88c2.27-2.09 3.665-5.17 3.665-9.15z"/>
      <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.26v3.15C3.25 21.36 7.33 24 12 24z"/>
      <path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.26C.46 8.16 0 9.94 0 12s.46 3.84 1.26 5.42l4.02-3.15z"/>
      <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.33 0 3.25 2.64 1.26 6.58l4.02 3.15c.95-2.83 3.6-4.98 6.72-4.98z"/>
    </svg>
    <span>Sign up with Google</span>
  </button>
</div>

<div class="auth-divider">
  <span>or register with email</span>
</div>

<div class="card auth-main-card">
  <form method="post" action="/register" novalidate id="registerForm">
    <?= $view->raw($csrf->field()) ?>

    <div class="row row-2">
      <?= $view->include('components/field', [
          'view' => $view, 'errors' => $errors, 'old' => $old,
          'name' => 'first_name', 'label' => 'First name', 'required' => true, 'autocomplete' => 'given-name',
          'placeholder' => 'e.g. Rahul'
      ]) ?>
      <?= $view->include('components/field', [
          'view' => $view, 'errors' => $errors, 'old' => $old,
          'name' => 'last_name', 'label' => 'Last name', 'autocomplete' => 'family-name',
          'placeholder' => 'e.g. Sharma'
      ]) ?>
    </div>

    <?= $view->include('components/field', [
        'view' => $view, 'errors' => $errors, 'old' => $old,
        'name' => 'email', 'label' => 'Email address', 'type' => 'email',
        'required' => true, 'autocomplete' => 'email',
        'placeholder' => 'rahul@example.com'
    ]) ?>

    <?= $view->include('components/field', [
        'view' => $view, 'errors' => $errors, 'old' => $old,
        'name' => 'phone', 'label' => 'Mobile number', 'type' => 'tel',
        'autocomplete' => 'tel', 'hint' => 'Used for WhatsApp/SMS order tracking only.',
        'placeholder' => '98765 43210',
    ]) ?>

    <div class="auth-password-field-group">
      <?= $view->include('components/field', [
          'view' => $view, 'errors' => $errors, 'old' => $old,
          'name' => 'password', 'label' => 'Create password', 'type' => 'password',
          'required' => true, 'autocomplete' => 'new-password',
          'placeholder' => 'At least 10 chars with a number/symbol',
          'attrs' => 'data-strength-target="strengthMeter"'
      ]) ?>

      <!-- Dynamic Password Strength Meter -->
      <div class="password-strength-container" id="strengthMeter" style="display:none;">
        <div class="strength-track">
          <div class="strength-fill" id="strengthFill"></div>
        </div>
        <div class="strength-label-row">
          <span class="strength-title">Security:</span>
          <span class="strength-status" id="strengthStatus">Weak</span>
        </div>
      </div>
    </div>

    <?= $view->include('components/field', [
        'view' => $view, 'errors' => $errors, 'old' => $old,
        'name' => 'password_confirmation', 'label' => 'Confirm password', 'type' => 'password',
        'required' => true, 'autocomplete' => 'new-password',
        'placeholder' => 'Re-enter your password'
    ]) ?>

    <div class="field auth-optin-field">
      <label class="checkbox auth-checkbox">
        <input type="checkbox" name="marketing_opt_in" value="1" <?= !empty($old['marketing_opt_in']) ? 'checked' : '' ?>>
        <span>Notify me about wholesale discount deals & product launches.</span>
      </label>
    </div>

    <div class="field auth-terms-field">
      <label class="checkbox auth-checkbox">
        <input type="checkbox" name="terms" value="1" required <?= !empty($old['terms']) ? 'checked' : '' ?>>
        <span>I agree to the <a href="/terms" target="_blank">Terms of Service</a> and <a href="/privacy" target="_blank">Privacy Policy</a>.</span>
      </label>
      <?php if (!empty($errors['terms'])): ?>
        <div class="field-error"><?= $view->e($errors['terms'][0]) ?></div>
      <?php endif; ?>
    </div>

    <button type="submit" class="btn btn-primary btn-block btn-lg auth-submit-btn">
      <span>Create My Account</span>
      <span class="auth-btn-arrow">→</span>
    </button>
  </form>
</div>

<div class="auth-alt-box">
  <p class="auth-alt-line">
    Already have an account? <a href="/login" class="auth-link-bold">Sign in</a>
  </p>

  <div class="auth-b2b-banner">
    <div class="auth-b2b-icon">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect width="20" height="14" x="2" y="7" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
      </svg>
    </div>
    <div class="auth-b2b-text">
      <strong>Purchasing for a restaurant, cloud kitchen or corporate?</strong>
      <p>Apply for verified business status, GST tax deduction & volume contract pricing.</p>
      <a href="/register/business" class="auth-b2b-action">Open Business Trade Account →</a>
    </div>
  </div>
</div>

<?php $view->stop(); ?>
