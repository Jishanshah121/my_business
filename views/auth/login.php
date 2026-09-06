<?php /** @var App\Support\View $view */ $view->extend('layouts/auth'); ?>
<?php $view->start('content'); ?>

<div class="auth-head">
  <span class="auth-pill-tag">Secure Member Login</span>
  <h1>Welcome to SupplyKaro</h1>
  <p>Sign in to manage orders, track live dispatches, and access GST invoices.</p>
</div>

<!-- Mode Switcher Tabs (Mobile OTP vs Password) -->
<div class="auth-tabs-toggle" style="display: flex; gap: 8px; margin: 20px 0; background: #F1F5F9; padding: 4px; border-radius: 12px;">
  <button type="button" id="tab-btn-otp" class="auth-mode-tab active" style="flex: 1; padding: 10px 14px; border: none; border-radius: 8px; font-weight: 700; font-size: 0.88rem; cursor: pointer; transition: all 0.2s ease; background: #FFFFFF; color: #008000; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
    📱 Mobile OTP
  </button>
  <button type="button" id="tab-btn-password" class="auth-mode-tab" style="flex: 1; padding: 10px 14px; border: none; border-radius: 8px; font-weight: 600; font-size: 0.88rem; cursor: pointer; transition: all 0.2s ease; background: transparent; color: #64748B;">
    🔑 Email &amp; Password
  </button>
</div>

<!-- ============================================================== MODE 1: MOBILE NUMBER & OTP (Features 2 & 3) -->
<div id="auth-mode-otp-panel" class="card auth-main-card" style="padding: 28px;">
  
  <!-- Step 1: Mobile Number Input -->
  <div id="page-auth-step-phone">
    <div style="text-align: center; margin-bottom: 20px;">
      <div style="width: 64px; height: 64px; border-radius: 50%; background: #EFF6FF; margin: 0 auto 12px; display: flex; align-items: center; justify-content: center;">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect width="14" height="20" x="5" y="2" rx="2" ry="2"/><path d="M12 18h.01"/>
        </svg>
      </div>
      <h2 style="font-size: 1.35rem; font-weight: 800; margin: 0 0 6px; color: #0F172A;">Enter mobile number</h2>
      <p style="font-size: 0.88rem; color: #64748B; margin: 0;">An instant 6-digit OTP will be sent to this number for fast verification.</p>
    </div>

    <div class="form-group" style="margin-bottom: 20px;">
      <div class="auth-phone-input-wrap">
        <div class="auth-flag-badge">
          <svg class="indian-flag-svg" width="22" height="15" viewBox="0 0 640 480">
            <path fill="#f93" d="M0 0h640v160H0z"/>
            <path fill="#fff" d="M0 160h640v160H0z"/>
            <path fill="#128807" d="M0 320h640v160H0z"/>
            <circle cx="320" cy="240" r="50" fill="#008"/>
            <circle cx="320" cy="240" r="40" fill="#fff"/>
            <circle cx="320" cy="240" r="10" fill="#008"/>
          </svg>
          <span class="auth-country-code">+91</span>
          <span class="auth-divider-bar">|</span>
        </div>
        <input 
          type="tel" 
          id="page-phone-input" 
          class="auth-phone-field" 
          placeholder="Enter 10-digit mobile number" 
          maxlength="10" 
          autocomplete="tel-national" 
          inputmode="numeric"
          autofocus
        >
      </div>
    </div>

    <button type="button" id="page-btn-continue" class="btn btn-block btn-lg auth-modal-btn" disabled style="width: 100%;">
      Continue
    </button>
  </div>

  <!-- Step 2: 6-Digit OTP Verification -->
  <div id="page-auth-step-otp" style="display: none;">
    <div style="text-align: center; margin-bottom: 20px;">
      <div style="width: 64px; height: 64px; border-radius: 50%; background: #EFF6FF; margin: 0 auto 12px; display: flex; align-items: center; justify-content: center;">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
        </svg>
      </div>
      <h2 style="font-size: 1.35rem; font-weight: 800; margin: 0 0 6px; color: #0F172A;">Enter verification code</h2>
      <p style="font-size: 0.88rem; color: #64748B; margin: 0;">
        6 digit OTP sent to <strong id="page-otp-phone-display" style="color: #0F172A;">+91</strong>
        <button type="button" id="page-change-phone-btn" class="auth-edit-phone-btn" style="background: none; border: none; color: #008000; font-weight: 700; cursor: pointer; text-decoration: underline; margin-left: 4px;">Edit</button>
      </p>
    </div>

    <div class="otp-inputs-grid" style="display: flex; gap: 8px; justify-content: center; margin-bottom: 20px;">
      <input type="text" class="otp-digit-field page-otp-field" maxlength="1" inputmode="numeric" autocomplete="one-time-code">
      <input type="text" class="otp-digit-field page-otp-field" maxlength="1" inputmode="numeric">
      <input type="text" class="otp-digit-field page-otp-field" maxlength="1" inputmode="numeric">
      <input type="text" class="otp-digit-field page-otp-field" maxlength="1" inputmode="numeric">
      <input type="text" class="otp-digit-field page-otp-field" maxlength="1" inputmode="numeric">
      <input type="text" class="otp-digit-field page-otp-field" maxlength="1" inputmode="numeric">
    </div>

    <div class="otp-resend-row" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; font-size: 0.86rem;">
      <span class="otp-timer-text" style="color: #64748B;">Resend code in: <strong id="page-otp-timer-display" style="color: #0F172A;">00:30</strong></span>
      <button type="button" id="page-resend-otp-btn" class="otp-resend-btn" disabled style="background: none; border: none; color: #64748B; font-weight: 700; cursor: pointer;">Resend now</button>
    </div>

    <button type="button" id="page-btn-verify" class="btn btn-block btn-lg auth-modal-btn" disabled style="width: 100%;">
      Verify &amp; Continue
    </button>
  </div>

</div>

<!-- ============================================================== MODE 2: EMAIL & PASSWORD -->
<div id="auth-mode-password-panel" class="card auth-main-card" style="display: none;">
  <form method="post" action="/login" novalidate>
    <?= $view->raw($csrf->field()) ?>

    <?= $view->include('components/field', [
        'view' => $view, 'errors' => $errors, 'old' => $old,
        'name' => 'identifier', 'label' => 'Email address or mobile',
        'type' => 'text', 'required' => true, 'autocomplete' => 'username',
        'placeholder' => 'e.g. rohit@cafe.com or 9876543210'
    ]) ?>

    <?= $view->include('components/field', [
        'view' => $view, 'errors' => $errors, 'old' => $old,
        'name' => 'password', 'label' => 'Password',
        'type' => 'password', 'required' => true, 'autocomplete' => 'current-password',
        'placeholder' => 'Enter your password'
    ]) ?>

    <div class="auth-remember-row">
      <label class="checkbox auth-checkbox">
        <input type="checkbox" name="remember" value="1" checked>
        <span>Keep me signed in</span>
      </label>
      <a class="auth-forgot-link" href="/forgot-password">Forgot password?</a>
    </div>

    <button type="submit" class="btn btn-primary btn-block btn-lg auth-submit-btn">
      <span>Sign in with Password</span>
      <span class="auth-btn-arrow">→</span>
    </button>
  </form>
</div>

<div class="auth-alt-box">
  <p class="auth-alt-line">
    New to SupplyKaro? <a href="/register" class="auth-link-bold">Create a personal account</a>
  </p>
  
  <div class="auth-b2b-banner">
    <div class="auth-b2b-icon">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect width="20" height="14" x="2" y="7" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
      </svg>
    </div>
    <div class="auth-b2b-text">
      <strong>Buying for a restaurant, cafe or enterprise?</strong>
      <p>Unlock custom trade rates, net-30 terms &amp; automated GST input credit.</p>
      <a href="/register/business" class="auth-b2b-action">Open a Business Account →</a>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  // Toggle between Mobile OTP and Password mode
  const tabOtp = document.getElementById('tab-btn-otp');
  const tabPass = document.getElementById('tab-btn-password');
  const panelOtp = document.getElementById('auth-mode-otp-panel');
  const panelPass = document.getElementById('auth-mode-password-panel');

  if (tabOtp && tabPass && panelOtp && panelPass) {
    tabOtp.addEventListener('click', () => {
      tabOtp.style.background = '#FFFFFF';
      tabOtp.style.color = '#008000';
      tabOtp.style.boxShadow = '0 1px 3px rgba(0,0,0,0.08)';
      tabPass.style.background = 'transparent';
      tabPass.style.color = '#64748B';
      tabPass.style.boxShadow = 'none';
      panelOtp.style.display = 'block';
      panelPass.style.display = 'none';
    });

    tabPass.addEventListener('click', () => {
      tabPass.style.background = '#FFFFFF';
      tabPass.style.color = '#008000';
      tabPass.style.boxShadow = '0 1px 3px rgba(0,0,0,0.08)';
      tabOtp.style.background = 'transparent';
      tabOtp.style.color = '#64748B';
      tabOtp.style.boxShadow = 'none';
      panelPass.style.display = 'block';
      panelOtp.style.display = 'none';
    });
  }

  // Page-level Mobile OTP Logic
  const phoneInput = document.getElementById('page-phone-input');
  const btnContinue = document.getElementById('page-btn-continue');
  const stepPhone = document.getElementById('page-auth-step-phone');
  const stepOtp = document.getElementById('page-auth-step-otp');
  const phoneDisplay = document.getElementById('page-otp-phone-display');
  const changePhoneBtn = document.getElementById('page-change-phone-btn');
  const resendBtn = document.getElementById('page-resend-otp-btn');
  const timerDisplay = document.getElementById('page-otp-timer-display');
  const btnVerify = document.getElementById('page-btn-verify');
  const otpFields = document.querySelectorAll('.page-otp-field');

  let currentPhone = '';
  let countdown = null;

  function getCsrf() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  }

  if (phoneInput && btnContinue) {
    phoneInput.addEventListener('input', (e) => {
      let val = e.target.value.replace(/\D/g, '');
      if (val.length > 10) val = val.slice(0, 10);
      e.target.value = val;

      if (val.length === 10) {
        btnContinue.disabled = false;
        btnContinue.classList.add('active');
      } else {
        btnContinue.disabled = true;
        btnContinue.classList.remove('active');
      }
    });

    phoneInput.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' && !btnContinue.disabled) {
        e.preventDefault();
        btnContinue.click();
      }
    });

    btnContinue.addEventListener('click', async () => {
      const phone = phoneInput.value.replace(/\D/g, '');
      if (phone.length !== 10) return;

      currentPhone = phone;
      btnContinue.disabled = true;
      btnContinue.textContent = 'Sending OTP...';

      try {
        const res = await fetch('/api/auth/send-otp', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': getCsrf()
          },
          body: JSON.stringify({ phone })
        });
        const data = await res.json();

        if (data.success) {
          stepPhone.style.display = 'none';
          stepOtp.style.display = 'block';
          if (phoneDisplay) phoneDisplay.textContent = `+91 ${phone}`;
          otpFields.forEach(f => f.value = '');
          if (otpFields[0]) setTimeout(() => otpFields[0].focus(), 150);

          startCountdown(30);
          alert('OTP sent! Use demo verification code: 123456');
        } else {
          alert(data.message || 'Failed to send OTP.');
          btnContinue.disabled = false;
          btnContinue.textContent = 'Continue';
        }
      } catch (err) {
        alert('Network error while requesting OTP.');
        btnContinue.disabled = false;
        btnContinue.textContent = 'Continue';
      }
    });
  }

  function startCountdown(sec) {
    clearInterval(countdown);
    let remaining = sec;
    if (resendBtn) resendBtn.disabled = true;

    function tick() {
      const s = String(remaining % 60).padStart(2, '0');
      if (timerDisplay) timerDisplay.textContent = `00:${s}`;
    }

    tick();
    countdown = setInterval(() => {
      remaining--;
      if (remaining <= 0) {
        clearInterval(countdown);
        if (timerDisplay) timerDisplay.textContent = '00:00';
        if (resendBtn) {
          resendBtn.disabled = false;
          resendBtn.style.color = '#008000';
        }
      } else {
        tick();
      }
    }, 1000);
  }

  if (changePhoneBtn) {
    changePhoneBtn.addEventListener('click', () => {
      clearInterval(countdown);
      stepOtp.style.display = 'none';
      stepPhone.style.display = 'block';
      if (phoneInput) setTimeout(() => phoneInput.focus(), 100);
      btnContinue.disabled = false;
      btnContinue.textContent = 'Continue';
    });
  }

  if (resendBtn) {
    resendBtn.addEventListener('click', () => {
      if (resendBtn.disabled || !currentPhone) return;
      btnContinue.click();
    });
  }

  // OTP field handlers
  otpFields.forEach((field, index) => {
    field.addEventListener('input', (e) => {
      const val = e.target.value.replace(/\D/g, '');
      e.target.value = val ? val.slice(-1) : '';

      if (e.target.value && index < otpFields.length - 1) {
        otpFields[index + 1].focus();
      }

      checkOtp();
    });

    field.addEventListener('keydown', (e) => {
      if (e.key === 'Backspace' && !e.target.value && index > 0) {
        otpFields[index - 1].focus();
      } else if (e.key === 'ArrowLeft' && index > 0) {
        otpFields[index - 1].focus();
      } else if (e.key === 'ArrowRight' && index < otpFields.length - 1) {
        otpFields[index + 1].focus();
      } else if (e.key === 'Enter' && !btnVerify.disabled) {
        btnVerify.click();
      }
    });

    field.addEventListener('paste', (e) => {
      e.preventDefault();
      const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
      if (pasted.length >= 6) {
        otpFields.forEach((f, i) => {
          f.value = pasted[i] || '';
        });
        if (otpFields[5]) otpFields[5].focus();
        checkOtp();
      }
    });
  });

  function checkOtp() {
    const code = Array.from(otpFields).map(f => f.value).join('');
    if (code.length === 6) {
      btnVerify.disabled = false;
      btnVerify.classList.add('active');
    } else {
      btnVerify.disabled = true;
      btnVerify.classList.remove('active');
    }
  }

  if (btnVerify) {
    btnVerify.addEventListener('click', async () => {
      const code = Array.from(otpFields).map(f => f.value).join('');
      if (code.length !== 6) return;

      btnVerify.disabled = true;
      btnVerify.textContent = 'Verifying...';

      try {
        const res = await fetch('/api/auth/verify-otp', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': getCsrf()
          },
          body: JSON.stringify({ phone: currentPhone, otp: code })
        });
        const data = await res.json();

        if (data.success) {
          const urlParams = new URLSearchParams(window.location.search);
          const redirectUrl = urlParams.get('redirect') || '/account';
          window.location.href = redirectUrl;
        } else {
          alert(data.message || 'Incorrect verification code.');
          btnVerify.disabled = false;
          btnVerify.textContent = 'Verify & Continue';
        }
      } catch (err) {
        alert('Network error while verifying OTP.');
        btnVerify.disabled = false;
        btnVerify.textContent = 'Verify & Continue';
      }
    });
  }
});
</script>

<?php $view->stop(); ?>
