<!-- Auth Modal (Mobile Number & 6-Digit OTP Sign-in) -->
<div class="modal-backdrop" id="auth-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="auth-modal-title-text">
  <div class="auth-modal-card">
    
    <!-- Top Close Button -->
    <button type="button" class="auth-modal-close-btn" id="close-auth-modal" aria-label="Close sign in dialog">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="18" y1="6" x2="6" y2="18"></line>
        <line x1="6" y1="6" x2="18" y2="18"></line>
      </svg>
    </button>

    <!-- STEP 1: Enter Mobile Number (Image 2) -->
    <div class="auth-modal-step" id="auth-step-phone">
      <!-- Illustration: Phone with +91 message bubble -->
      <div class="auth-illustration-wrap">
        <div class="auth-illus-circle">
          <svg width="84" height="84" viewBox="0 0 96 96" fill="none" xmlns="http://www.w3.org/2000/svg">
            <!-- Background Soft Circle -->
            <circle cx="48" cy="48" r="44" fill="#EBF3FF"/>
            <!-- Phone Body -->
            <rect x="30" y="16" width="36" height="64" rx="8" fill="#FFFFFF" stroke="#334155" stroke-width="3"/>
            <!-- Screen Notch / Speaker -->
            <rect x="42" y="21" width="12" height="3" rx="1.5" fill="#94A3B8"/>
            <!-- Home Bar Indicator -->
            <rect x="43" y="73" width="10" height="2" rx="1" fill="#94A3B8"/>
            <!-- Floating Message Bubble with +91 -->
            <g filter="url(#phoneShadow)">
              <rect x="44" y="30" width="34" height="22" rx="7" fill="#2563EB"/>
              <!-- Speech Bubble Tail -->
              <path d="M50 52L46 56V52H50Z" fill="#2563EB"/>
              <!-- +91 Text Inside Bubble -->
              <text x="61" y="45.5" fill="#FFFFFF" font-family="-apple-system, BlinkMacSystemFont, sans-serif" font-size="12" font-weight="800" text-anchor="middle" letter-spacing="0.5">+91</text>
            </g>
            <defs>
              <filter id="phoneShadow" x="40" y="28" width="42" height="32" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                <feDropShadow dx="0" dy="2" stdDeviation="2" flood-opacity="0.15"/>
              </filter>
            </defs>
          </svg>
        </div>
      </div>

      <!-- Title & Subtitle -->
      <h2 id="auth-modal-title-text" class="auth-modal-title">Enter mobile number</h2>
      <p class="auth-modal-subtitle">OTP will be sent to this number for verification</p>

      <!-- Input Box with Flag and +91 -->
      <div class="auth-phone-input-wrap" id="auth-phone-input-container">
        <!-- Indian Flag SVG -->
        <span class="auth-flag-icon" aria-hidden="true">
          <svg width="24" height="16" viewBox="0 0 24 16" fill="none" xmlns="http://www.w3.org/2000/svg" style="border-radius: 2px; box-shadow: 0 0 1px rgba(0,0,0,0.3);">
            <rect width="24" height="5.33" fill="#FF9933"/>
            <rect y="5.33" width="24" height="5.34" fill="#FFFFFF"/>
            <rect y="10.67" width="24" height="5.33" fill="#138808"/>
            <circle cx="12" cy="8" r="2.2" stroke="#000080" stroke-width="0.7" fill="none"/>
            <circle cx="12" cy="8" r="0.6" fill="#000080"/>
          </svg>
        </span>
        <span class="auth-prefix-text">+91</span>
        <span class="auth-prefix-divider"></span>
        <input 
          type="tel" 
          id="auth-phone-input" 
          class="auth-phone-field" 
          maxlength="10" 
          placeholder="Enter 10-digit mobile number" 
          inputmode="numeric"
          autocomplete="tel"
        >
      </div>

      <!-- Continue Action Button -->
      <button type="button" id="auth-btn-continue" class="auth-action-btn" disabled>
        Continue
      </button>

      <!-- Optional footer link -->
      <div class="auth-terms-caption">
        By continuing, you agree to SupplyKaro's <a href="/terms" target="_blank">Terms of Service</a> & <a href="/privacy" target="_blank">Privacy Policy</a>
      </div>
    </div>

    <!-- STEP 2: Enter Verification Code (Image 3) -->
    <div class="auth-modal-step" id="auth-step-otp" style="display: none;">
      <!-- Illustration: Phone with *** password bubble -->
      <div class="auth-illustration-wrap">
        <div class="auth-illus-circle">
          <svg width="84" height="84" viewBox="0 0 96 96" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="48" cy="48" r="44" fill="#EBF3FF"/>
            <rect x="30" y="16" width="36" height="64" rx="8" fill="#FFFFFF" stroke="#334155" stroke-width="3"/>
            <rect x="42" y="21" width="12" height="3" rx="1.5" fill="#94A3B8"/>
            <rect x="43" y="73" width="10" height="2" rx="1" fill="#94A3B8"/>
            <!-- Floating Bubble with *** -->
            <g filter="url(#otpShadow)">
              <rect x="40" y="30" width="38" height="22" rx="7" fill="#2563EB"/>
              <path d="M47 52L43 56V52H47Z" fill="#2563EB"/>
              <!-- Three Password Stars -->
              <text x="59" y="47" fill="#FFFFFF" font-family="-apple-system, BlinkMacSystemFont, sans-serif" font-size="16" font-weight="900" text-anchor="middle" letter-spacing="2">✱✱✱</text>
            </g>
            <defs>
              <filter id="otpShadow" x="36" y="28" width="46" height="32" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                <feDropShadow dx="0" dy="2" stdDeviation="2" flood-opacity="0.15"/>
              </filter>
            </defs>
          </svg>
        </div>
      </div>

      <!-- Title & Dynamic Subtitle -->
      <h2 class="auth-modal-title">Enter verification code</h2>
      <p class="auth-modal-subtitle">
        6 digit OTP has been sent to <strong id="auth-otp-phone-display">+91 </strong>
        <button type="button" id="auth-change-phone-btn" class="auth-inline-link">Edit</button>
      </p>

      <!-- 6 Individual Digit Inputs -->
      <div class="otp-inputs-grid" id="otp-inputs-container">
        <input type="text" class="otp-digit-field" maxlength="1" inputmode="numeric" data-idx="0" autocomplete="off" aria-label="Digit 1">
        <input type="text" class="otp-digit-field" maxlength="1" inputmode="numeric" data-idx="1" autocomplete="off" aria-label="Digit 2">
        <input type="text" class="otp-digit-field" maxlength="1" inputmode="numeric" data-idx="2" autocomplete="off" aria-label="Digit 3">
        <input type="text" class="otp-digit-field" maxlength="1" inputmode="numeric" data-idx="3" autocomplete="off" aria-label="Digit 4">
        <input type="text" class="otp-digit-field" maxlength="1" inputmode="numeric" data-idx="4" autocomplete="off" aria-label="Digit 5">
        <input type="text" class="otp-digit-field" maxlength="1" inputmode="numeric" data-idx="5" autocomplete="off" aria-label="Digit 6">
      </div>

      <!-- Timer & Resend Row -->
      <div class="otp-timer-row">
        <div class="otp-countdown-text" id="otp-timer-display">00:30</div>
        <div class="otp-resend-wrap">
          <span class="muted small">Didn't receive the code?</span>
          <button type="button" id="auth-resend-otp-btn" class="auth-resend-btn" disabled>Resend now</button>
        </div>
      </div>

      <!-- Verify Action Button -->
      <button type="button" id="auth-btn-verify" class="auth-action-btn" disabled>
        Verify
      </button>

    </div>

  </div>
</div>
