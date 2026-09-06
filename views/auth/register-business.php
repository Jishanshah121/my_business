<?php /** @var App\Support\View $view */ $view->extend('layouts/auth', ['wide' => true, 'title' => 'Open Business Account']); ?>
<?php $view->start('content'); ?>

<div class="auth-head">
  <span class="auth-pill-tag tag-b2b">Verified B2B Trade Onboarding</span>
  <h1>Open a Business Account</h1>
  <p>Trade pricing tiers, automated GST input credit, bulk quotations & priority dispatches.</p>
</div>

<div class="auth-b2b-highlight-card">
  <div class="highlight-badge">Instant Access</div>
  <div class="highlight-body">
    <strong>Start ordering immediately at standard rates.</strong>
    <span>Wholesale trade discount pricing unlocks once our operations team verifies your GSTIN (typically within 4 working hours).</span>
  </div>
</div>

<div class="card auth-main-card auth-b2b-form-card">
  <form method="post" action="/register/business" novalidate id="businessRegisterForm">
    <?= $view->raw($csrf->field()) ?>

    <!-- STEP 1: Enterprise Profile -->
    <div class="auth-form-section">
      <div class="section-title-wrap">
        <span class="section-badge">1</span>
        <div>
          <h3 class="section-title">Business Profile & Verification</h3>
          <p class="section-sub">Required for generating valid GST tax invoices</p>
        </div>
      </div>

      <div class="section-fields">
        <?= $view->include('components/field', [
            'view' => $view, 'errors' => $errors, 'old' => $old,
            'name' => 'company_name', 'label' => 'Registered Company / Business Name', 'required' => true,
            'autocomplete' => 'organization',
            'placeholder' => 'e.g. ChaiBistro Hospitality Pvt. Ltd.'
        ]) ?>

        <div class="row row-2">
          <?= $view->include('components/field', [
              'view' => $view, 'errors' => $errors, 'old' => $old,
              'name' => 'business_type_id', 'label' => 'Business Sector', 'required' => true,
              'options' => array_column($businessTypes ?? [], 'name', 'id'),
              'placeholder' => 'Select your business type',
          ]) ?>

          <?= $view->include('components/field', [
              'view' => $view, 'errors' => $errors, 'old' => $old,
              'name' => 'expected_monthly_spend', 'label' => 'Estimated Monthly Spend (₹)',
              'type' => 'number', 'hint' => 'Helps us assign your bulk discount bracket.',
              'placeholder' => 'e.g. 50000',
              'attrs' => 'min="0" step="500"',
          ]) ?>
        </div>

        <div class="row row-2">
          <?= $view->include('components/field', [
              'view' => $view, 'errors' => $errors, 'old' => $old,
              'name' => 'contact_person', 'label' => 'Authorized Contact Person', 'required' => true,
              'placeholder' => 'e.g. Rohit Kapoor'
          ]) ?>
          <?= $view->include('components/field', [
              'view' => $view, 'errors' => $errors, 'old' => $old,
              'name' => 'contact_phone', 'label' => 'Direct Contact Mobile', 'type' => 'tel',
              'required' => true, 'placeholder' => '98765 43210',
          ]) ?>
        </div>

        <?= $view->include('components/field', [
            'view' => $view, 'errors' => $errors, 'old' => $old,
            'name' => 'gstin', 'label' => 'GSTIN (GST Identification Number)', 'required' => true,
            'hint' => '15-character statutory GSTIN. Automatically verified against official registry.',
            'placeholder' => '27AAAAA0000A1Z5',
            'attrs' => 'style="text-transform:uppercase;font-family:var(--font-mono);letter-spacing:0.05em;" maxlength="15"',
        ]) ?>

        <div class="row row-2">
          <?= $view->include('components/field', [
              'view' => $view, 'errors' => $errors, 'old' => $old,
              'name' => 'pan', 'label' => 'Company PAN', 'placeholder' => 'ABCDE1234F',
              'hint' => 'Optional if GSTIN provided.',
              'attrs' => 'style="text-transform:uppercase;font-family:var(--font-mono);" maxlength="10"',
          ]) ?>
          <?= $view->include('components/field', [
              'view' => $view, 'errors' => $errors, 'old' => $old,
              'name' => 'fssai_licence', 'label' => 'FSSAI License No.', 'hint' => 'Optional for food businesses.',
              'placeholder' => '14-digit FSSAI number',
              'attrs' => 'maxlength="14"',
          ]) ?>
        </div>
      </div>
    </div>

    <!-- STEP 2: Administrator Credentials -->
    <div class="auth-form-section">
      <div class="section-title-wrap">
        <span class="section-badge">2</span>
        <div>
          <h3 class="section-title">Primary Admin Credentials</h3>
          <p class="section-sub">These details will be used to log in and manage your account</p>
        </div>
      </div>

      <div class="section-fields">
        <div class="row row-2">
          <?= $view->include('components/field', [
              'view' => $view, 'errors' => $errors, 'old' => $old,
              'name' => 'first_name', 'label' => 'First name', 'required' => true, 'autocomplete' => 'given-name',
              'placeholder' => 'First name'
          ]) ?>
          <?= $view->include('components/field', [
              'view' => $view, 'errors' => $errors, 'old' => $old,
              'name' => 'last_name', 'label' => 'Last name', 'autocomplete' => 'family-name',
              'placeholder' => 'Last name'
          ]) ?>
        </div>

        <?= $view->include('components/field', [
            'view' => $view, 'errors' => $errors, 'old' => $old,
            'name' => 'email', 'label' => 'Official Business Email', 'type' => 'email',
            'required' => true, 'autocomplete' => 'email',
            'hint' => 'Invoices, dispatch notes and GST credit statements will be sent here.',
            'placeholder' => 'billing@yourcompany.com'
        ]) ?>

        <div class="row row-2">
          <?= $view->include('components/field', [
              'view' => $view, 'errors' => $errors, 'old' => $old,
              'name' => 'password', 'label' => 'Create Password', 'type' => 'password',
              'required' => true, 'autocomplete' => 'new-password',
              'hint' => 'Minimum 10 characters with numbers/symbols.',
              'placeholder' => '••••••••••••'
          ]) ?>

          <?= $view->include('components/field', [
              'view' => $view, 'errors' => $errors, 'old' => $old,
              'name' => 'password_confirmation', 'label' => 'Confirm Password', 'type' => 'password',
              'required' => true, 'autocomplete' => 'new-password',
              'placeholder' => '••••••••••••'
          ]) ?>
        </div>
      </div>
    </div>

    <div class="field auth-terms-field">
      <label class="checkbox auth-checkbox">
        <input type="checkbox" name="terms" value="1" required <?= !empty($old['terms']) ? 'checked' : '' ?>>
        <span>I agree to the <a href="/terms" target="_blank">B2B Terms of Supply</a> and confirm that I am authorised to open this commercial trade account.</span>
      </label>
      <?php if (!empty($errors['terms'])): ?>
        <div class="field-error"><?= $view->e($errors['terms'][0]) ?></div>
      <?php endif; ?>
    </div>

    <button type="submit" class="btn btn-primary btn-block btn-lg auth-submit-btn">
      <span>Submit Business Registration</span>
      <span class="auth-btn-arrow">→</span>
    </button>
  </form>
</div>

<div class="auth-alt-box">
  <p class="auth-alt-line">
    Purchasing personal supplies only? <a href="/register" class="auth-link-bold">Create a standard account</a> ·
    Already registered? <a href="/login" class="auth-link-bold">Sign in</a>
  </p>
</div>

<?php $view->stop(); ?>
