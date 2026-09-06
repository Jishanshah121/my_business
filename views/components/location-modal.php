<!-- Location Selector Modal Backdrop -->
<div class="modal-backdrop" id="location-modal-backdrop">
  <div class="location-modal-card" role="dialog" aria-modal="true" aria-labelledby="modal-location-heading">
    
    <!-- Top Header: Illustration & Close Button -->
    <div class="flex items-start justify-between" style="margin-bottom: 20px;">
      <!-- Store & Pin Badge Illustration -->
      <div class="loc-shop-illustration">
        <svg width="48" height="48" viewBox="0 0 56 56" fill="none" xmlns="http://www.w3.org/2000/svg">
          <circle cx="28" cy="28" r="28" fill="#EAF3EE"/>
          <!-- Store Base -->
          <rect x="14" y="24" width="28" height="18" rx="3" fill="#E8B595"/>
          <rect x="14" y="24" width="28" height="6" fill="#D39874"/>
          <!-- Store Awning -->
          <path d="M12 24C12 19 16 16 28 16C40 16 44 19 44 24H12Z" fill="#008000"/>
          <rect x="18" y="29" width="8" height="13" rx="1.5" fill="#008000"/>
          <!-- Location Pin Marker -->
          <g filter="url(#pinShadow)">
            <circle cx="38" cy="26" r="7" fill="#008000"/>
            <circle cx="38" cy="26" r="3" fill="#FFFFFF"/>
            <path d="M38 33L35 28H41L38 33Z" fill="#008000"/>
          </g>
        </svg>
      </div>

      <!-- Close Button -->
      <button type="button" class="loc-modal-close-btn" id="close-location-modal" aria-label="Close location selector">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
      </button>
    </div>

    <!-- Modal Title -->
    <h2 id="modal-location-heading" class="loc-modal-title">
      Share your location to view accurate prices
    </h2>

    <!-- Search City Input -->
    <div class="loc-search-box">
      <input 
        type="text" 
        id="loc-city-search-input" 
        class="loc-search-input" 
        placeholder="Search city name" 
        autocomplete="off"
      >
    </div>

    <!-- Cities List Container -->
    <div class="loc-cities-container">
      <div class="loc-cities-list" id="loc-cities-list">
        
        <div class="loc-city-item" data-city="Bengaluru" data-pincode="560001">
          <span class="loc-city-text">Bengaluru</span>
          <span class="loc-chevron">›</span>
        </div>

        <div class="loc-city-item" data-city="Delhi" data-pincode="110001">
          <span class="loc-city-text">Delhi</span>
          <span class="loc-chevron">›</span>
        </div>

        <div class="loc-city-item" data-city="Gurugram" data-pincode="122001">
          <span class="loc-city-text">Gurugram</span>
          <span class="loc-chevron">›</span>
        </div>

        <div class="loc-city-item" data-city="Bokaro Steel City" data-pincode="827001">
          <span class="loc-city-text">Bokaro Steel City</span>
          <span class="loc-chevron">›</span>
        </div>

        <div class="loc-city-item" data-city="Mumbai" data-pincode="400001">
          <span class="loc-city-text">Mumbai</span>
          <span class="loc-chevron">›</span>
        </div>

        <div class="loc-city-item" data-city="Kolkata" data-pincode="700001">
          <span class="loc-city-text">Kolkata</span>
          <span class="loc-chevron">›</span>
        </div>

        <div class="loc-city-item" data-city="Hyderabad" data-pincode="500001">
          <span class="loc-city-text">Hyderabad</span>
          <span class="loc-chevron">›</span>
        </div>

        <div class="loc-city-item" data-city="Pune" data-pincode="411001">
          <span class="loc-city-text">Pune</span>
          <span class="loc-chevron">›</span>
        </div>

        <div class="loc-city-item" data-city="Ahmedabad" data-pincode="380001">
          <span class="loc-city-text">Ahmedabad</span>
          <span class="loc-chevron">›</span>
        </div>

        <div class="loc-city-item" data-city="Jaipur" data-pincode="302001">
          <span class="loc-city-text">Jaipur</span>
          <span class="loc-chevron">›</span>
        </div>

        <div class="loc-city-item" data-city="Patna" data-pincode="800001">
          <span class="loc-city-text">Patna</span>
          <span class="loc-chevron">›</span>
        </div>

        <div class="loc-city-item" data-city="Ranchi" data-pincode="834001">
          <span class="loc-city-text">Ranchi</span>
          <span class="loc-chevron">›</span>
        </div>

        <div class="loc-city-item" data-city="Noida" data-pincode="201301">
          <span class="loc-city-text">Noida</span>
          <span class="loc-chevron">›</span>
        </div>

        <div class="loc-city-item" data-city="Chandigarh" data-pincode="160001">
          <span class="loc-city-text">Chandigarh</span>
          <span class="loc-chevron">›</span>
        </div>

        <div class="loc-city-item" data-city="Lucknow" data-pincode="226001">
          <span class="loc-city-text">Lucknow</span>
          <span class="loc-chevron">›</span>
        </div>

      </div>
    </div>

    <!-- OR Divider -->
    <div class="loc-or-divider">
      <span>OR</span>
    </div>

    <!-- Login Link Footer -->
    <div class="loc-modal-footer">
      <a href="/login" class="loc-login-link">Login</a> to see your saved addresses
    </div>

  </div>
</div>
