<!-- Location Access Permission Modal (Image 4) -->
<div class="modal-backdrop" id="location-access-backdrop" role="dialog" aria-modal="true" aria-labelledby="loc-access-heading">
  <div class="location-access-card">
    
    <!-- Close Button -->
    <button type="button" class="loc-access-close-btn" id="close-location-access-modal" aria-label="Close location prompt">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="18" y1="6" x2="6" y2="18"></line>
        <line x1="6" y1="6" x2="18" y2="18"></line>
      </svg>
    </button>

    <!-- 3D Folded Map & Pin Illustration (Image 4) -->
    <div class="loc-illustration-wrap">
      <div class="loc-map-circle">
        <svg width="104" height="104" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
          <!-- Soft Background Circle -->
          <circle cx="50" cy="50" r="46" fill="#F0F4FF"/>
          
          <!-- Folded Map Panel 1 (Left) -->
          <path d="M26 36L38 31V66L26 71V36Z" fill="#93C5FD" stroke="#60A5FA" stroke-width="1.5" stroke-linejoin="round"/>
          <path d="M28 41C30 40 33 42 35 40V49C33 50 30 48 28 50V41Z" fill="#3B82F6" fill-opacity="0.8"/>
          
          <!-- Folded Map Panel 2 (Center) -->
          <path d="M38 31L62 37V72L38 66V31Z" fill="#BFDBFE" stroke="#60A5FA" stroke-width="1.5" stroke-linejoin="round"/>
          <!-- Map Greenery / Terrains -->
          <path d="M42 45C46 43 54 48 58 45V55C54 57 46 52 42 54V45Z" fill="#34D399" fill-opacity="0.9"/>
          
          <!-- Folded Map Panel 3 (Right) -->
          <path d="M62 37L74 32V67L62 72V37Z" fill="#93C5FD" stroke="#60A5FA" stroke-width="1.5" stroke-linejoin="round"/>
          <path d="M64 42C67 40 70 43 72 41V51C70 52 67 50 64 52V42Z" fill="#3B82F6" fill-opacity="0.8"/>

          <!-- 3D Red Location Pin -->
          <g filter="url(#pinDropShadow)">
            <!-- Pin Head -->
            <ellipse cx="50" cy="27" rx="9" ry="9" fill="#EF4444"/>
            <circle cx="50" cy="25" r="4" fill="#FFFFFF"/>
            <!-- Pin Needle Point -->
            <path d="M44 32L50 47L56 32C54 35 46 35 44 32Z" fill="#DC2626"/>
            <circle cx="50" cy="47" r="1.5" fill="#991B1B"/>
          </g>

          <defs>
            <filter id="pinDropShadow" x="38" y="16" width="24" height="38" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
              <feDropShadow dx="0" dy="3" stdDeviation="2" flood-color="#7F1D1D" flood-opacity="0.25"/>
            </filter>
          </defs>
        </svg>
      </div>
    </div>

    <!-- Heading & Subtitle -->
    <h2 id="loc-access-heading" class="loc-access-title">Allow location access</h2>
    <p class="loc-access-subtitle">We will use it to help you set up your delivery location and show accurate stock availability.</p>

    <!-- Action Buttons -->
    <div class="loc-access-actions">
      <button type="button" class="loc-access-primary-btn" id="btn-allow-location-access">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/>
        </svg>
        <span>Allow location access</span>
      </button>

      <button type="button" class="loc-access-secondary-btn" id="btn-manual-location-select">
        Select location manually
      </button>
    </div>

  </div>
</div>
