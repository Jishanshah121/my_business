/**
 * SUPPLYKARO — Master Frontend Engine
 * Modern, Vanilla ES Script, Zero Dependency, Ultra Fast.
 */

(function () {
  'use strict';

  // ----------------------------------------------------------- Cart Store
  // ----------------------------------------------------------- Cart Store
  const Cart = {
    items: [],

    init() {
      try {
        const saved = localStorage.getItem('supplykaro_cart');
        const parsed = saved ? JSON.parse(saved) : [];
        this.items = Array.isArray(parsed) ? parsed.map(i => {
          const price = parseFloat(i.price) || 0;
          const pcs = parseInt(i.pcs, 10) || 1;
          const unitPrice = parseFloat(i.unitPrice) || (pcs > 0 ? price / pcs : price);
          return {
            id: String(i.id || ''),
            name: String(i.name || 'Product'),
            pack: String(i.pack || `Pack of ${pcs} pcs`),
            pcs: pcs,
            price: price,
            unitPrice: unitPrice,
            qty: Math.max(1, parseInt(i.qty, 10) || 1),
            image: String(i.image || '/assets/images/placeholder/cups.svg')
          };
        }).filter(i => i.id !== '') : [];
      } catch (e) {
        this.items = [];
      }
    },

    save() {
      try {
        localStorage.setItem('supplykaro_cart', JSON.stringify(this.items));
      } catch (e) {}
    },

    addItem(item) {
      if (!item || !item.id) return;
      const id = String(item.id);
      const existing = this.items.find(i => i.id === id && (i.pack === item.pack || !item.pack));
      const addQty = Math.max(1, parseInt(item.qty || 1, 10) || 1);
      
      if (existing) {
        existing.qty += addQty;
      } else {
        const price = parseFloat(item.price) || 0;
        const pcs = parseInt(item.pcs, 10) || 1;
        const unitPrice = parseFloat(item.unitPrice) || (pcs > 0 ? price / pcs : price);

        this.items.push({
          id: id,
          name: String(item.name || 'Product'),
          pack: String(item.pack || `Pack of ${pcs} pcs`),
          pcs: pcs,
          price: price,
          unitPrice: unitPrice,
          qty: addQty,
          image: String(item.image || '/assets/images/placeholder/cups.svg')
        });
      }

      this.save();
      this.render();
      this.syncProductCards();
      this.showToast(`Added ${item.name || 'Product'} to basket!`);
      
      // Open drawer to confirm
      const drawer = document.getElementById('cart-drawer-overlay');
      if (drawer) {
        drawer.classList.add('open');
      }
    },

    updateQty(id, delta) {
      const item = this.items.find(i => i.id === String(id));
      if (!item) return;
      item.qty += delta;
      if (item.qty <= 0) {
        this.items = this.items.filter(i => i.id !== String(id));
      }
      this.save();
      this.render();
      this.syncProductCards();
    },

    getTotal() {
      return this.items.reduce((sum, i) => sum + ((parseFloat(i.price) || 0) * (parseInt(i.qty, 10) || 1)), 0);
    },

    getItemCount() {
      return this.items.reduce((sum, i) => sum + (parseInt(i.qty, 10) || 0), 0);
    },

    syncProductCards() {
      document.querySelectorAll('.fast-add-container').forEach(container => {
        const id = container.dataset.id;
        if (!id) return;
        const item = this.items.find(i => i.id === id);
        if (item && item.qty > 0) {
          container.innerHTML = `
            <div class="stepper-active">
              <button type="button" class="stepper-btn" data-id="${id}" data-delta="-1" aria-label="Decrease quantity">−</button>
              <span class="stepper-count">${item.qty}</span>
              <button type="button" class="stepper-btn" data-id="${id}" data-delta="1" aria-label="Increase quantity">+</button>
            </div>
          `;
        } else {
          const existingBtn = container.querySelector('.fast-add-btn');
          if (!existingBtn) {
            const name = container.dataset.name || 'Product';
            const price = container.dataset.price || '0';
            const pack = container.dataset.pack || '';
            const pcs = container.dataset.pcs || '1';
            const unitPrice = container.dataset.unitPrice || '0';
            const image = container.dataset.image || '';

            container.innerHTML = `
              <button 
                type="button" 
                class="fast-add-btn" 
                data-id="${id}"
                data-name="${name}"
                data-price="${price}"
                data-pack="${pack}"
                data-pcs="${pcs}"
                data-unit-price="${unitPrice}"
                data-image="${image}"
                aria-label="Add ${name} to cart"
              >
                + ADD
              </button>
            `;
          }
        }
      });
    },

    render() {
      const count = this.getItemCount();
      document.querySelectorAll('.cart-count-pill').forEach(pill => {
        pill.textContent = count;
        pill.style.display = count > 0 ? 'inline-flex' : 'none';
      });

      const subtotal = this.getTotal();
      const gst = subtotal * 0.18;
      const total = subtotal + gst;
      const threshold = 1500;
      const progress = Math.min(100, Math.round((subtotal / threshold) * 100));

      // 1. Render Drawer Elements
      const drawerContainer = document.getElementById('cart-items-container');
      const drawerSubtotal = document.getElementById('cart-subtotal-val');
      const drawerGst = document.getElementById('cart-gst-val');
      const drawerTotal = document.getElementById('cart-total-val');
      const freeShipFill = document.getElementById('free-shipping-fill');
      const freeShipText = document.getElementById('free-shipping-text');

      if (drawerContainer) {
        if (this.items.length === 0) {
          drawerContainer.innerHTML = `
            <div style="text-align: center; padding: 48px 16px; color: var(--text-muted);">
              <div style="margin-bottom: 12px;">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--border-dark)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>
                </svg>
              </div>
              <h4 style="color: var(--text); margin-bottom: 6px;">Your cart is empty</h4>
              <p class="small">Explore products and add wholesale cartons to begin.</p>
            </div>
          `;
        } else {
          let html = '';
          this.items.forEach(item => {
            const price = parseFloat(item.price) || 0;
            const unitPrice = parseFloat(item.unitPrice) || 0;
            const qty = parseInt(item.qty, 10) || 1;
            const lineTotal = (price * qty).toFixed(2);
            html += `
              <div class="flex gap-3" style="padding: 12px 0; border-bottom: 1px solid var(--border-subtle);">
                <img src="${item.image}" alt="${item.name}" style="width: 54px; height: 54px; border-radius: var(--radius-sm); object-fit: cover; border: 1px solid var(--border);">
                <div style="flex: 1;">
                  <div style="font-weight: 700; font-size: 0.88rem; color: var(--text); line-height: 1.25;">${item.name}</div>
                  <div class="tiny muted" style="margin: 2px 0 6px;">${item.pack} · ₹${unitPrice.toFixed(2)}/pc</div>
                  <div class="flex items-center justify-between">
                    <div class="stepper-active">
                      <button type="button" class="stepper-btn" data-id="${item.id}" data-delta="-1" aria-label="Decrease quantity">−</button>
                      <span class="stepper-count">${qty}</span>
                      <button type="button" class="stepper-btn" data-id="${item.id}" data-delta="1" aria-label="Increase quantity">+</button>
                    </div>
                    <div class="bold mono tnum" style="font-size: 0.95rem;">₹${lineTotal}</div>
                  </div>
                </div>
              </div>
            `;
          });
          drawerContainer.innerHTML = html;
        }

        if (drawerSubtotal) drawerSubtotal.textContent = `₹${subtotal.toLocaleString('en-IN', {minimumFractionDigits: 2})}`;
        if (drawerGst) drawerGst.textContent = `₹${gst.toLocaleString('en-IN', {minimumFractionDigits: 2})}`;
        if (drawerTotal) drawerTotal.textContent = `₹${total.toLocaleString('en-IN', {minimumFractionDigits: 2})}`;
        if (freeShipFill) freeShipFill.style.width = `${progress}%`;
        if (freeShipText) {
          if (subtotal >= threshold) {
            freeShipText.innerHTML = `<strong>Unlocked:</strong> You have qualified for Free Express Delivery.`;
          } else {
            const needed = (threshold - subtotal).toFixed(0);
            freeShipText.innerHTML = `Add <strong>₹${needed}</strong> more to unlock <strong>Free Express Delivery</strong>`;
          }
        }
      }

      // 2. Render Full Cart Page
      const fullContainer = document.getElementById('full-cart-items-container');
      const emptyCartBox = document.getElementById('full-cart-empty-state');
      const fullCartBox = document.getElementById('full-cart-content');
      const fullSubtotal = document.getElementById('full-cart-subtotal-val');
      const fullGst = document.getElementById('full-cart-gst-val');
      const fullTotal = document.getElementById('full-cart-total-val');
      const fullShipFill = document.getElementById('full-cart-ship-fill');
      const fullShipText = document.getElementById('full-cart-ship-text');

      if (fullContainer) {
        if (this.items.length === 0) {
          if (emptyCartBox) emptyCartBox.style.display = 'block';
          if (fullCartBox) fullCartBox.style.display = 'none';
        } else {
          if (emptyCartBox) emptyCartBox.style.display = 'none';
          if (fullCartBox) fullCartBox.style.display = 'grid';

          let fullHtml = '';
          this.items.forEach(item => {
            const price = parseFloat(item.price) || 0;
            const unitPrice = parseFloat(item.unitPrice) || 0;
            const qty = parseInt(item.qty, 10) || 1;
            const lineTotal = (price * qty).toFixed(2);
            fullHtml += `
              <div class="flex items-center justify-between" style="padding-bottom: 16px; border-bottom: 1px solid var(--line-subtle);">
                <div class="flex items-center gap-4">
                  <img src="${item.image}" alt="${item.name}" style="width: 68px; height: 68px; border-radius: var(--radius-sm); object-fit: cover; border: 1px solid var(--border);">
                  <div>
                    <div class="bold" style="font-size: 1.05rem;">${item.name}</div>
                    <div class="small muted">${item.pack} · ₹${unitPrice.toFixed(2)} / piece</div>
                    <div class="tiny" style="color: var(--eco);">✓ Ready for Dispatch</div>
                  </div>
                </div>
                <div class="flex items-center gap-4">
                  <div class="stepper-active">
                    <button type="button" class="stepper-btn" data-id="${item.id}" data-delta="-1" aria-label="Decrease quantity">−</button>
                    <span class="stepper-count">${qty}</span>
                    <button type="button" class="stepper-btn" data-id="${item.id}" data-delta="1" aria-label="Increase quantity">+</button>
                  </div>
                  <div class="bold mono tnum" style="font-size: 1.15rem; min-width: 90px; text-align: right;">₹${lineTotal}</div>
                </div>
              </div>
            `;
          });
          fullContainer.innerHTML = fullHtml;
        }

        if (fullSubtotal) fullSubtotal.textContent = `₹${subtotal.toLocaleString('en-IN', {minimumFractionDigits: 2})}`;
        if (fullGst) fullGst.textContent = `₹${gst.toLocaleString('en-IN', {minimumFractionDigits: 2})}`;
        if (fullTotal) fullTotal.textContent = `₹${total.toLocaleString('en-IN', {minimumFractionDigits: 2})}`;
        if (fullShipFill) fullShipFill.style.width = `${progress}%`;
        if (fullShipText) {
          if (subtotal >= threshold) {
            fullShipText.innerHTML = `<strong>Unlocked:</strong> You qualify for Free Freight & Same-Day Dispatch.`;
          } else {
            const needed = (threshold - subtotal).toFixed(0);
            fullShipText.innerHTML = `Add <strong>₹${needed}</strong> more to unlock <strong>Free Express Delivery</strong>`;
          }
        }
      }

      // 3. Render Checkout Page
      const checkoutContainer = document.getElementById('checkout-items-container');
      const checkoutHeader = document.getElementById('checkout-items-header');
      const checkoutSubtotal = document.getElementById('checkout-subtotal-val');
      const checkoutCgst = document.getElementById('checkout-cgst-val');
      const checkoutSgst = document.getElementById('checkout-sgst-val');
      const checkoutTotal = document.getElementById('checkout-total-val');

      if (checkoutContainer) {
        if (checkoutHeader) {
          checkoutHeader.textContent = `Order Summary (${count} Item${count === 1 ? '' : 's'})`;
        }

        if (this.items.length === 0) {
          checkoutContainer.innerHTML = `<div class="muted small">Your basket is empty. Please add items before checking out.</div>`;
        } else {
          let chHtml = '';
          this.items.forEach(item => {
            const lineTotal = (item.price * item.qty).toFixed(2);
            chHtml += `
              <div class="flex items-center justify-between">
                <span>${item.qty}x ${item.name} (${item.pack})</span>
                <span class="bold mono tnum">₹${lineTotal}</span>
              </div>
            `;
          });
          checkoutContainer.innerHTML = chHtml;
        }

        const halfGst = (gst / 2).toFixed(2);
        if (checkoutSubtotal) checkoutSubtotal.textContent = `₹${subtotal.toLocaleString('en-IN', {minimumFractionDigits: 2})}`;
        if (checkoutCgst) checkoutCgst.textContent = `₹${halfGst}`;
        if (checkoutSgst) checkoutSgst.textContent = `₹${halfGst}`;
        if (checkoutTotal) checkoutTotal.textContent = `₹${total.toLocaleString('en-IN', {minimumFractionDigits: 2})}`;
      }
    },

    showToast(message) {
      let toast = document.getElementById('global-toast');
      if (!toast) {
        toast = document.createElement('div');
        toast.id = 'global-toast';
        toast.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:300;background:var(--text);color:#fff;padding:12px 20px;border-radius:var(--radius-md);font-size:0.9rem;font-weight:600;box-shadow:var(--shadow-modal);display:flex;align-items:center;gap:10px;transform:translateY(20px);opacity:0;transition:all 0.3s ease;';
        document.body.appendChild(toast);
      }
      toast.innerHTML = `
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;">
          <polyline points="20 6 9 17 4 12"></polyline>
        </svg>
        <span>${message}</span>
      `;
      toast.style.transform = 'translateY(0)';
      toast.style.opacity = '1';
      setTimeout(() => {
        toast.style.transform = 'translateY(20px)';
        toast.style.opacity = '0';
      }, 3000);
    }
  };

  // ----------------------------------------------------------- Auth & Location Guards for Cart Actions
  function isUserLoggedIn() {
    return document.body.dataset.userLoggedIn === 'true' ||
           document.querySelector('meta[name="auth-status"]')?.content === 'logged_in';
  }

  function hasUserSetLocation() {
    return localStorage.getItem('supplykaro_location_set') === 'true';
  }

  function guardCartAction(pendingItem) {
    // 1. If user is NOT logged in: Open Mobile Auth Modal (Image 2 & 3) directly on current page!
    if (!isUserLoggedIn()) {
      if (pendingItem) {
        window.supplyKaroPendingAdd = pendingItem;
        try {
          sessionStorage.setItem('supplykaro_pending_cart_item', JSON.stringify(pendingItem));
        } catch (e) {}
      }
      const authModal = document.getElementById('auth-modal-backdrop');
      if (authModal) {
        authModal.classList.add('active');
        const phoneInput = document.getElementById('auth-phone-input');
        if (phoneInput) setTimeout(() => phoneInput.focus(), 150);
        Cart.showToast('Please sign in with your mobile number to add items');
      } else {
        const currentUrl = window.location.pathname + window.location.search;
        window.location.href = `/register?redirect=${encodeURIComponent(currentUrl)}`;
      }
      return false;
    }

    // 2. If user IS logged in:
    // If location has already been given -> proceed immediately, never ask again!
    if (hasUserSetLocation()) {
      return true;
    }

    // If location has NOT been given yet -> Open "Allow location access" modal (Image 4):
    const locModal = document.getElementById('location-access-backdrop') || document.getElementById('location-modal-backdrop');
    if (locModal) {
      window.supplyKaroPendingAdd = pendingItem;
      locModal.classList.add('active');
      Cart.showToast('Please allow location access to continue');
    }
    return false;
  }

  // ----------------------------------------------------------- Fast Add & Action Handlers
  function handleFastAdd(target) {
    // Support polymorphic argument: element or (id, element)
    const btn = (target && target.nodeType) ? target : (arguments[1] || (typeof target === 'string' ? document.querySelector(`.fast-add-btn[data-id="${target}"]`) : null));
    const container = btn ? btn.closest('.fast-add-container') : null;
    const card = btn ? btn.closest('.product-card') : null;

    const id = (btn && btn.dataset.id) || (container && container.dataset.id) || (card && card.dataset.id) || (typeof target === 'string' ? target : 'ITEM');
    const name = (btn && btn.dataset.name) || (container && container.dataset.name) || card?.querySelector('.product-card-title')?.textContent.trim() || 'Product';
    
    let price = parseFloat((btn && btn.dataset.price) || (container && container.dataset.price) || 0);
    if (!price && card) {
      const priceText = card.querySelector('.price-main')?.textContent.replace(/[^0-9.]/g, '');
      price = parseFloat(priceText) || 0;
    }

    const pack = (btn && btn.dataset.pack) || (container && container.dataset.pack) || card?.querySelector('.product-pack-desc')?.textContent.trim() || 'Standard Pack';
    const pcs = parseInt((btn && btn.dataset.pcs) || (container && container.dataset.pcs) || 1, 10) || 1;
    const unitPrice = parseFloat((btn && btn.dataset.unitPrice) || (container && container.dataset.unitPrice) || (pcs > 0 ? price / pcs : price));
    const image = (btn && btn.dataset.image) || (container && container.dataset.image) || card?.querySelector('img')?.src || '/assets/images/placeholder/cups.svg';

    const item = {
      id: id,
      name: name,
      pack: pack,
      pcs: pcs,
      price: price,
      unitPrice: unitPrice,
      qty: 1,
      image: image
    };

    if (!guardCartAction(item)) {
      return;
    }

    Cart.addItem(item);
  }

  function handleActionAdd(btn) {
    if (!btn) return;
    const id = btn.dataset.id || 'ITEM';
    const name = btn.dataset.name || 'Product';
    const price = parseFloat(btn.dataset.price) || 0;
    const pack = btn.dataset.pack || 'Standard Pack';
    const pcs = parseInt(btn.dataset.pcs, 10) || 1;
    const unitPrice = parseFloat(btn.dataset.unitPrice) || (pcs > 0 ? price / pcs : price);
    const qty = Math.max(1, parseInt(btn.dataset.qty, 10) || 1);
    const image = btn.dataset.image || '/assets/images/placeholder/cups.svg';

    const item = {
      id: id,
      name: name,
      pack: pack,
      pcs: pcs,
      price: price,
      unitPrice: unitPrice,
      qty: qty,
      image: image
    };

    if (!guardCartAction(item)) {
      return;
    }

    Cart.addItem(item);
  }

  // ----------------------------------------------------------- Product Detail Page (PDP) Cart Wiring
  function initPdpCart() {
    const packOptions = document.querySelectorAll('.pdp-pack-option');
    const addBtn = document.getElementById('pdp-add-to-cart-btn') || document.querySelector('.add-to-cart-btn');
    const priceHeadline = document.getElementById('pdp-price-headline');
    const mrpStrike = document.getElementById('pdp-mrp-strike');
    const unitPriceEl = document.getElementById('pdp-unit-price');
    const savingsBadge = document.getElementById('pdp-savings-badge');

    if (packOptions.length > 0 && addBtn) {
      packOptions.forEach(opt => {
        opt.addEventListener('click', () => {
          packOptions.forEach(o => o.classList.remove('active'));
          opt.classList.add('active');

          const price = parseFloat(opt.dataset.price) || 0;
          const mrp = parseFloat(opt.dataset.mrp) || (price * 1.4);
          const pcs = parseInt(opt.dataset.pcs, 10) || 50;
          const label = opt.dataset.label || `Pack of ${pcs} pcs`;
          const unitPrice = parseFloat(opt.dataset.unitPrice) || (pcs > 0 ? price / pcs : price);
          const savings = mrp > price ? Math.round(((mrp - price) / mrp) * 100) : 0;

          if (priceHeadline) priceHeadline.textContent = `₹${price.toLocaleString('en-IN', {minimumFractionDigits: 0})}`;
          if (mrpStrike) mrpStrike.textContent = `₹${mrp.toLocaleString('en-IN', {minimumFractionDigits: 0})}`;
          if (unitPriceEl) unitPriceEl.textContent = `₹${unitPrice.toFixed(2)} / piece (${label})`;
          if (savingsBadge) savingsBadge.textContent = `${savings}% OFF`;

          addBtn.dataset.price = price;
          addBtn.dataset.pack = label;
          addBtn.dataset.pcs = pcs;
          addBtn.dataset.unitPrice = unitPrice;
        });
      });
    }

    if (addBtn) {
      addBtn.addEventListener('click', () => {
        const qtyEl = document.getElementById('pdp-qty-val');
        const qty = qtyEl ? parseInt(qtyEl.textContent, 10) || 1 : 1;

        const id = addBtn.dataset.id || 'ITEM';
        const name = addBtn.dataset.name || document.querySelector('h1')?.textContent.trim() || 'Product';
        const price = parseFloat(addBtn.dataset.price) || 35.00;
        const pack = addBtn.dataset.pack || 'Pack of 100 Sheets';
        const pcs = parseInt(addBtn.dataset.pcs, 10) || 100;
        const unitPrice = parseFloat(addBtn.dataset.unitPrice) || (pcs > 0 ? price / pcs : price);
        const image = addBtn.dataset.image || document.getElementById('pdp-main-img')?.src || '/assets/images/products/butterpaper.png';

        const item = {
          id: id,
          name: name,
          pack: pack,
          pcs: pcs,
          price: price,
          unitPrice: unitPrice,
          qty: qty,
          image: image
        };

        if (!guardCartAction(item)) {
          return;
        }

        Cart.addItem(item);
      });
    }
  }

  function handleBuyNow(btn) {
    const addBtn = document.getElementById('pdp-add-to-cart-btn') || document.querySelector('.add-to-cart-btn');
    if (addBtn) {
      const qtyEl = document.getElementById('pdp-qty-val');
      const qty = qtyEl ? parseInt(qtyEl.textContent, 10) || 1 : 1;

      const item = {
        id: addBtn.dataset.id || 'ITEM',
        name: addBtn.dataset.name || document.querySelector('h1')?.textContent.trim() || 'Product',
        price: parseFloat(addBtn.dataset.price) || 35.00,
        pack: addBtn.dataset.pack || 'Pack of 100 Sheets',
        pcs: parseInt(addBtn.dataset.pcs, 10) || 100,
        unitPrice: parseFloat(addBtn.dataset.unitPrice) || 35.00,
        qty: qty,
        image: addBtn.dataset.image || document.getElementById('pdp-main-img')?.src || '/assets/images/products/butterpaper.png'
      };

      if (!guardCartAction(item)) {
        return;
      }

      Cart.addItem(item);
      setTimeout(() => {
        window.location.href = '/checkout';
      }, 100);
    }
  }

  // ----------------------------------------------------------- Hero Carousel
  function initHeroCarousel() {
    const track = document.getElementById('hero-slider-track');
    const slides = document.querySelectorAll('.hero-slide');
    const dots = document.querySelectorAll('.hero-dot');
    const prevBtn = document.getElementById('hero-prev-btn');
    const nextBtn = document.getElementById('hero-next-btn');

    if (!track || slides.length === 0) return;

    let currentIndex = 0;
    let autoInterval = null;

    function goToSlide(index) {
      if (index < 0) index = slides.length - 1;
      if (index >= slides.length) index = 0;
      currentIndex = index;
      track.style.transform = `translateX(-${currentIndex * 100}%)`;
      dots.forEach((d, i) => d.classList.toggle('active', i === currentIndex));
    }

    function startAuto() {
      stopAuto();
      autoInterval = setInterval(() => {
        goToSlide(currentIndex + 1);
      }, 4500);
    }

    function stopAuto() {
      if (autoInterval) clearInterval(autoInterval);
    }

    if (prevBtn) prevBtn.addEventListener('click', () => { goToSlide(currentIndex - 1); startAuto(); });
    if (nextBtn) nextBtn.addEventListener('click', () => { goToSlide(currentIndex + 1); startAuto(); });

    dots.forEach((dot, i) => {
      dot.addEventListener('click', () => {
        goToSlide(i);
        startAuto();
      });
    });

    track.addEventListener('mouseenter', stopAuto);
    track.addEventListener('mouseleave', startAuto);

    // Touch / Swipe Navigation for Mobile Phones
    let touchStartX = 0;
    let touchStartY = 0;
    let touchEndX = 0;
    const minSwipeDistance = 35;

    track.addEventListener('touchstart', (e) => {
      stopAuto();
      touchStartX = e.changedTouches[0].clientX;
      touchStartY = e.changedTouches[0].clientY;
    }, { passive: true });

    track.addEventListener('touchend', (e) => {
      touchEndX = e.changedTouches[0].clientX;
      const touchEndY = e.changedTouches[0].clientY;
      const diffX = touchStartX - touchEndX;
      const diffY = touchStartY - touchEndY;

      // Only handle horizontal swipe if horizontal movement exceeds vertical scroll
      if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > minSwipeDistance) {
        if (diffX > 0) {
          goToSlide(currentIndex + 1); // Swiped left -> next slide
        } else {
          goToSlide(currentIndex - 1); // Swiped right -> prev slide
        }
      }
      startAuto();
    }, { passive: true });

    startAuto();
  }

  // ----------------------------------------------------------- Location Selector Modal (Interactive Hyperpure-Style)
  // ----------------------------------------------------------- Location Access Modal (Feature 4 - Image 4)
  function initLocationAccessModal() {
    const accessModal = document.getElementById('location-access-backdrop');
    const closeBtn = document.getElementById('close-location-access-modal');
    const allowBtn = document.getElementById('btn-allow-location-access');
    const manualBtn = document.getElementById('btn-manual-location-select');
    const cityModal = document.getElementById('location-modal-backdrop');
    const cityEl = document.getElementById('header-delivery-city');
    const mobileCityEl = document.getElementById('mobile-delivery-city');

    if (!accessModal) return;

    const hasSetLocation = localStorage.getItem('supplykaro_location_set') === 'true';
    const isAuthPage = window.location.pathname.startsWith('/login') || window.location.pathname.startsWith('/register');

    // First-Visit Prompt (Image 4):
    // If the user has NEVER given their location, show the "Allow location access" modal on the first page visit.
    // If already given, system will NEVER ask again!
    if (!hasSetLocation && !isAuthPage) {
      setTimeout(() => {
        accessModal.classList.add('active');
      }, 400);
    }

    if (closeBtn) {
      closeBtn.addEventListener('click', () => accessModal.classList.remove('active'));
    }

    accessModal.addEventListener('click', (e) => {
      if (e.target === accessModal) accessModal.classList.remove('active');
    });

    function finalizeLocation(city, pin) {
      localStorage.setItem('supplykaro_delivery_city', city);
      localStorage.setItem('supplykaro_location_set', 'true');
      if (pin) localStorage.setItem('supplykaro_delivery_pincode', pin);

      if (cityEl) cityEl.textContent = `${city} ▼`;
      if (mobileCityEl) mobileCityEl.textContent = `${city}${pin ? ', ' + pin : ''} ▼`;
      const accountCityEl = document.getElementById('account-city-label');
      if (accountCityEl) accountCityEl.textContent = `Delivering to ${city} ▼`;

      accessModal.classList.remove('active');
      if (cityModal) cityModal.classList.remove('active');

      Cart.showToast(`Delivery location set to ${city} · Store ready`);

      if (window.supplyKaroPendingAdd) {
        const pending = window.supplyKaroPendingAdd;
        window.supplyKaroPendingAdd = null;
        Cart.addItem(pending);
      }
    }

    if (allowBtn) {
      allowBtn.addEventListener('click', () => {
        allowBtn.disabled = true;
        allowBtn.innerHTML = `<span>Detecting location...</span>`;

        if ('geolocation' in navigator) {
          navigator.geolocation.getCurrentPosition(
            (pos) => {
              finalizeLocation('Bokaro Steel City', '827001');
            },
            (err) => {
              finalizeLocation('Bokaro Steel City', '827001');
            },
            { timeout: 6000 }
          );
        } else {
          finalizeLocation('Bokaro Steel City', '827001');
        }
      });
    }

    if (manualBtn) {
      manualBtn.addEventListener('click', () => {
        accessModal.classList.remove('active');
        if (cityModal) {
          cityModal.classList.add('active');
          const searchInput = document.getElementById('loc-city-search-input');
          if (searchInput) {
            searchInput.value = '';
            searchInput.focus();
          }
        }
      });
    }
  }

  // ----------------------------------------------------------- City Search Modal (Manual City Selector)
  function initLocationSelector() {
    const modal = document.getElementById('location-modal-backdrop');
    const closeBtn = document.getElementById('close-location-modal');
    const searchInput = document.getElementById('loc-city-search-input');
    const cityItems = document.querySelectorAll('.loc-city-item');
    const cityEl = document.getElementById('header-delivery-city');
    const mobileCityEl = document.getElementById('mobile-delivery-city');
    const drawerCityEl = document.getElementById('drawer-delivery-city');

    if (!modal) return;

    const hasSetLocation = localStorage.getItem('supplykaro_location_set') === 'true';
    const savedCity = localStorage.getItem('supplykaro_delivery_city') || (hasSetLocation ? 'Bokaro' : 'Select Location');
    if (cityEl) cityEl.textContent = `${savedCity} ▼`;
    if (mobileCityEl) mobileCityEl.textContent = savedCity;
    if (drawerCityEl) drawerCityEl.textContent = `${savedCity}, 827001`;
    const accountCityEl = document.getElementById('account-city-label');
    if (accountCityEl) accountCityEl.textContent = `Delivering to ${savedCity} ▼`;

    function selectLocation(city, pin) {
      if (!city) return;

      // Update active highlight in modal list
      cityItems.forEach(c => {
        const name = (c.dataset.city || c.textContent).trim();
        if (name.toLowerCase().includes(city.toLowerCase())) {
          c.classList.add('active');
        } else {
          c.classList.remove('active');
        }
      });

      // Update headers
      if (cityEl) cityEl.textContent = `${city} ▼`;
      if (mobileCityEl) mobileCityEl.textContent = `${city}${pin ? ', ' + pin : ''}`;
      if (drawerCityEl) drawerCityEl.textContent = `${city}${pin ? ', ' + pin : ''}`;
      const accountCityEl = document.getElementById('account-city-label');
      if (accountCityEl) accountCityEl.textContent = `Delivering to ${city} ▼`;

      // Save in localStorage (System will NEVER ask automatically again)
      localStorage.setItem('supplykaro_delivery_city', city);
      localStorage.setItem('supplykaro_location_set', 'true');
      if (pin) localStorage.setItem('supplykaro_delivery_pincode', pin);

      // Close modal and show toast
      modal.classList.remove('active');
      Cart.showToast(`Delivery location set to ${city} · Store ready`);

      // If an add-to-cart action was pending location selection:
      if (window.supplyKaroPendingAdd) {
        const pending = window.supplyKaroPendingAdd;
        window.supplyKaroPendingAdd = null;
        Cart.addItem(pending);
      }
    }

    // Global listener for all manual triggers
    document.addEventListener('click', (e) => {
      if (e.target.closest('.open-location-modal, .location-selector-btn')) {
        e.preventDefault();
        modal.classList.add('active');
        if (searchInput) {
          searchInput.value = '';
          searchInput.focus();
          cityItems.forEach(item => item.style.display = 'flex');
        }
      }
    });

    if (closeBtn) {
      closeBtn.addEventListener('click', () => modal.classList.remove('active'));
    }

    modal.addEventListener('click', (e) => {
      if (e.target === modal) modal.classList.remove('active');
    });

    // Escape key closes modal
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && modal.classList.contains('active')) {
        modal.classList.remove('active');
      }
    });

    // Real-time city search filtering
    if (searchInput) {
      searchInput.addEventListener('input', (e) => {
        const query = e.target.value.toLowerCase().trim();
        cityItems.forEach(item => {
          const name = (item.dataset.city || item.textContent).toLowerCase();
          item.style.display = name.includes(query) ? 'flex' : 'none';
        });
      });

      searchInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
          e.preventDefault();
          const val = searchInput.value.trim();
          if (val.length > 1) {
            selectLocation(val, '');
          }
        }
      });
    }

    // City Selection handler
    cityItems.forEach(item => {
      item.addEventListener('click', () => {
        const city = item.dataset.city || item.querySelector('.loc-city-text')?.textContent.trim();
        const pin = item.dataset.pincode || '';
        selectLocation(city, pin);
      });
    });
  }

  // ----------------------------------------------------------- Search Dropdown Overlay
  function initSearchOverlay() {
    const input = document.getElementById('header-search-input');
    const overlay = document.getElementById('search-overlay-dropdown');

    if (!input || !overlay) return;

    input.addEventListener('focus', () => overlay.classList.add('active'));
    document.addEventListener('click', (e) => {
      if (!input.contains(e.target) && !overlay.contains(e.target)) {
        overlay.classList.remove('active');
      }
    });

    overlay.querySelectorAll('.search-chip-item').forEach(chip => {
      chip.addEventListener('click', () => {
        input.value = chip.dataset.query || chip.textContent.trim();
        overlay.classList.remove('active');
        const form = input.closest('form');
        if (form) form.submit();
      });
    });
  }

  // ----------------------------------------------------------- Cart Drawer Toggle
  function initCartDrawer() {
    const overlay = document.getElementById('cart-drawer-overlay');
    if (!overlay) return;

    document.addEventListener('click', (e) => {
      const trigger = e.target.closest('.cart-icon-btn, .open-cart-drawer');
      if (trigger) {
        e.preventDefault();
        overlay.classList.add('open');
        Cart.render();
        return;
      }
      const closeBtn = e.target.closest('.close-cart-drawer');
      if (closeBtn) {
        e.preventDefault();
        overlay.classList.remove('open');
        return;
      }
      if (e.target === overlay) {
        overlay.classList.remove('open');
      }
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && overlay.classList.contains('open')) {
        overlay.classList.remove('open');
      }
    });
  }

  // ----------------------------------------------------------- Horizontal Rail Scroll
  function initRailScroll() {
    document.querySelectorAll('.rail-scroll-prev').forEach(btn => {
      btn.addEventListener('click', () => {
        const rail = document.querySelector(btn.dataset.target);
        if (rail) rail.scrollBy({ left: -260, behavior: 'smooth' });
      });
    });
    document.querySelectorAll('.rail-scroll-next').forEach(btn => {
      btn.addEventListener('click', () => {
        const rail = document.querySelector(btn.dataset.target);
        if (rail) rail.scrollBy({ left: 260, behavior: 'smooth' });
      });
    });
  }

  // ----------------------------------------------------------- Modern Auth UI Enhancements
  function initAuthEnhancements() {
    // 1. Password Visibility Toggle
    document.querySelectorAll('.password-toggle-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        const targetName = btn.dataset.target;
        const input = targetName ? document.getElementById(targetName) : btn.closest('.field-control-wrap')?.querySelector('input');
        if (!input) return;

        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';

        const eyeOpen = btn.querySelector('.eye-open');
        const eyeClosed = btn.querySelector('.eye-closed');
        if (eyeOpen && eyeClosed) {
          eyeOpen.style.display = isPassword ? 'none' : 'block';
          eyeClosed.style.display = isPassword ? 'block' : 'none';
        }
      });
    });

    // 2. Real-Time Password Strength Meter
    document.querySelectorAll('input[data-strength-target]').forEach(input => {
      const targetId = input.dataset.strengthTarget;
      const meter = document.getElementById(targetId);
      if (!meter) return;

      const fill = meter.querySelector('.strength-fill');
      const status = meter.querySelector('.strength-status');

      input.addEventListener('input', () => {
        const val = input.value;
        if (!val) {
          meter.style.display = 'none';
          return;
        }

        meter.style.display = 'block';

        let score = 0;
        if (val.length >= 8) score++;
        if (val.length >= 10) score++;
        if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score++;
        if (/[0-9]/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;

        const levels = [
          { text: 'Too short (min 8 chars)', color: '#DC2626', width: '20%' },
          { text: 'Weak', color: '#EA580C', width: '40%' },
          { text: 'Fair', color: '#D97706', width: '60%' },
          { text: 'Good', color: '#2563EB', width: '80%' },
          { text: 'Strong & Secure', color: '#16A34A', width: '100%' }
        ];

        let index = 0;
        if (val.length < 8) {
          index = 0;
        } else if (score <= 2) {
          index = 1;
        } else if (score === 3) {
          index = 2;
        } else if (score === 4) {
          index = 3;
        } else {
          index = 4;
        }

        const current = levels[index];
        if (fill) {
          fill.style.width = current.width;
          fill.style.backgroundColor = current.color;
        }
        if (status) {
          status.textContent = current.text;
          status.style.color = current.color;
        }
      });
    });

    // 3. GSTIN & PAN Automatic Uppercase
    document.querySelectorAll('input[name="gstin"], input[name="pan"]').forEach(input => {
      input.addEventListener('input', () => {
        const start = input.selectionStart;
        const end = input.selectionEnd;
        input.value = input.value.toUpperCase();
        input.setSelectionRange(start, end);
      });
    });
  }

  // ----------------------------------------------------------- Amazon/Flipkart Listing Studio Helpers
  function initAdminStudioHelpers() {
    const fileInput = document.getElementById('image_file');
    const urlInput = document.getElementById('image_url');
    const previewImg = document.getElementById('liveImagePreview');

    // 1. File Upload Preview
    if (fileInput && previewImg) {
      fileInput.addEventListener('change', () => {
        const file = fileInput.files[0];
        if (file) {
          const reader = new FileReader();
          reader.onload = (e) => {
            previewImg.src = e.target.result;
          };
          reader.readAsDataURL(file);
        }
      });
    }

    // 2. Direct URL Preview
    if (urlInput && previewImg) {
      urlInput.addEventListener('input', () => {
        const val = urlInput.value.trim();
        if (val) {
          previewImg.src = val;
        }
      });
    }

    // 3. Dynamic Pack Pricing Calculator
    const basePriceEl = document.getElementById('base_unit_price');
    const mrpPriceEl = document.getElementById('mrp_unit_price');
    const pack1QtyEl = document.getElementById('pack_1_qty');
    const pack2QtyEl = document.getElementById('pack_2_qty');
    const pack1SumEl = document.getElementById('pack1Summary');
    const pack2SumEl = document.getElementById('pack2Summary');

    function updatePackCalculations() {
      if (!basePriceEl) return;
      const base = parseFloat(basePriceEl.value) || 0;
      const mrp = parseFloat(mrpPriceEl ? mrpPriceEl.value : 0) || (base * 1.5);
      
      const p1Qty = parseInt(pack1QtyEl ? pack1QtyEl.value : 50) || 50;
      const p2Qty = parseInt(pack2QtyEl ? pack2QtyEl.value : 500) || 500;

      if (pack1SumEl) {
        const p1Price = (base * p1Qty).toFixed(2);
        const p1Mrp = (mrp * p1Qty).toFixed(2);
        pack1SumEl.innerHTML = `Pack Price: <strong>₹${p1Price}</strong> (MRP ₹${p1Mrp})`;
      }

      if (pack2SumEl) {
        if (p2Qty > 0) {
          const p2Price = (base * p2Qty).toFixed(2);
          const p2Mrp = (mrp * p2Qty).toFixed(2);
          pack2SumEl.innerHTML = `Carton Price: <strong>₹${p2Price}</strong> (MRP ₹${p2Mrp})`;
        } else {
          pack2SumEl.innerHTML = `<em>Master carton pack disabled (Qty: 0)</em>`;
        }
      }
    }

    document.querySelectorAll('.calc-trigger').forEach(input => {
      input.addEventListener('input', updatePackCalculations);
    });
  }

  // ----------------------------------------------------------- CSP-Safe Event Delegation
  document.addEventListener('click', (e) => {
    // 0. Auth Modal Triggers (.open-auth-modal, .login-signup-btn)
    const authTrigger = e.target.closest('.open-auth-modal, .login-signup-btn');
    if (authTrigger) {
      const authModal = document.getElementById('auth-modal-backdrop');
      if (authModal) {
        e.preventDefault();
        e.stopPropagation();
        authModal.classList.add('active');
        const phoneInput = document.getElementById('auth-phone-input');
        if (phoneInput) setTimeout(() => phoneInput.focus(), 150);
        return;
      }
    }

    // 1. Fast Add Button (+ ADD)
    const fastAddBtn = e.target.closest('.fast-add-btn');
    if (fastAddBtn) {
      e.preventDefault();
      e.stopPropagation();
      handleFastAdd(fastAddBtn);
      return;
    }

    // 2. Stepper Buttons (+ / -)
    const stepperBtn = e.target.closest('.stepper-btn');
    if (stepperBtn) {
      e.preventDefault();
      e.stopPropagation();
      const id = stepperBtn.dataset.id;
      const delta = parseInt(stepperBtn.dataset.delta, 10);
      if (id && delta) {
        Cart.updateQty(id, delta);
      }
      return;
    }

    // 3. Generic Add to Cart Action Buttons
    const addActionBtn = e.target.closest('[data-action="add-to-cart"]');
    if (addActionBtn) {
      e.preventDefault();
      e.stopPropagation();
      handleActionAdd(addActionBtn);
      return;
    }

    // 4. PDP Quantity Stepper Buttons
    const pdpMinus = e.target.closest('#pdp-qty-minus');
    if (pdpMinus) {
      e.preventDefault();
      const q = document.getElementById('pdp-qty-val');
      if (q) {
        const cur = parseInt(q.textContent, 10) || 1;
        q.textContent = Math.max(1, cur - 1);
      }
      return;
    }
    const pdpPlus = e.target.closest('#pdp-qty-plus');
    if (pdpPlus) {
      e.preventDefault();
      const q = document.getElementById('pdp-qty-val');
      if (q) {
        const cur = parseInt(q.textContent, 10) || 1;
        q.textContent = cur + 1;
      }
      return;
    }

    // 5. PDP Buy Now Button
    const pdpBuyNow = e.target.closest('#pdp-buy-now-btn');
    if (pdpBuyNow) {
      e.preventDefault();
      const addBtn = document.getElementById('pdp-add-to-cart-btn') || document.querySelector('.add-to-cart-btn');
      if (addBtn) {
        addBtn.click();
      }
      setTimeout(() => {
        window.location.href = '/checkout';
      }, 100);
      return;
    }
    // 6. Open Auth Modal Trigger (e.g. Header Login / Signup)
    const openAuthBtn = e.target.closest('.open-auth-modal');
    if (openAuthBtn) {
      e.preventDefault();
      const authModal = document.getElementById('auth-modal-backdrop');
      if (authModal) {
        authModal.classList.add('active');
        const phoneInput = document.getElementById('auth-phone-input');
        if (phoneInput) setTimeout(() => phoneInput.focus(), 150);
      }
      return;
    }
  });

  // ----------------------------------------------------------- Mobile & OTP Auth Modal (Features 2 & 3 - Images 2 & 3)
  function initAuthModal() {
    const modal = document.getElementById('auth-modal-backdrop');
    const closeBtn = document.getElementById('close-auth-modal');
    const stepPhone = document.getElementById('auth-step-phone');
    const stepOtp = document.getElementById('auth-step-otp');
    const phoneInput = document.getElementById('auth-phone-input');
    const btnContinue = document.getElementById('auth-btn-continue');
    const btnVerify = document.getElementById('auth-btn-verify');
    const changePhoneBtn = document.getElementById('auth-change-phone-btn');
    const resendBtn = document.getElementById('auth-resend-otp-btn');
    const timerDisplay = document.getElementById('otp-timer-display');
    const phoneDisplay = document.getElementById('auth-otp-phone-display');
    const otpFields = document.querySelectorAll('.otp-digit-field');

    if (!modal) return;

    let countdownInterval = null;
    let currentPhone = '';

    function getCsrfToken() {
      return document.querySelector('meta[name="csrf-token"]')?.content || '';
    }

    function resetModal() {
      if (stepPhone) stepPhone.style.display = 'block';
      if (stepOtp) stepOtp.style.display = 'none';
      if (phoneInput) phoneInput.value = '';
      if (btnContinue) {
        btnContinue.disabled = true;
        btnContinue.classList.remove('active');
        btnContinue.textContent = 'Continue';
      }
      if (btnVerify) {
        btnVerify.disabled = true;
        btnVerify.classList.remove('active');
        btnVerify.textContent = 'Verify';
      }
      otpFields.forEach(f => f.value = '');
      clearInterval(countdownInterval);
    }

    if (closeBtn) {
      closeBtn.addEventListener('click', () => {
        modal.classList.remove('active');
        resetModal();
      });
    }

    modal.addEventListener('click', (e) => {
      if (e.target === modal) {
        modal.classList.remove('active');
        resetModal();
      }
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && modal.classList.contains('active')) {
        modal.classList.remove('active');
        resetModal();
      }
    });

    // Mobile input handling
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
    }

    function startTimer(seconds) {
      clearInterval(countdownInterval);
      let remaining = seconds;
      if (resendBtn) {
        resendBtn.disabled = true;
        resendBtn.classList.remove('enabled');
      }

      function renderTime() {
        const m = String(Math.floor(remaining / 60)).padStart(2, '0');
        const s = String(remaining % 60).padStart(2, '0');
        if (timerDisplay) timerDisplay.textContent = `${m}:${s}`;
      }

      renderTime();
      countdownInterval = setInterval(() => {
        remaining--;
        if (remaining <= 0) {
          clearInterval(countdownInterval);
          if (timerDisplay) timerDisplay.textContent = '00:00';
          if (resendBtn) {
            resendBtn.disabled = false;
            resendBtn.classList.add('enabled');
          }
        } else {
          renderTime();
        }
      }, 1000);
    }

    // Step 1 -> Send OTP
    if (btnContinue) {
      btnContinue.addEventListener('click', async () => {
        const phone = phoneInput ? phoneInput.value.replace(/\D/g, '') : '';
        if (phone.length !== 10) return;

        currentPhone = phone;
        btnContinue.disabled = true;
        btnContinue.textContent = 'Sending...';

        try {
          const res = await fetch('/api/auth/send-otp', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-Token': getCsrfToken()
            },
            body: JSON.stringify({ phone: phone })
          });

          const data = await res.json();
          if (data.success) {
            stepPhone.style.display = 'none';
            stepOtp.style.display = 'block';
            if (phoneDisplay) phoneDisplay.textContent = `+91 ${phone}`;

            // Clear and focus first OTP input
            otpFields.forEach(f => f.value = '');
            if (otpFields[0]) setTimeout(() => otpFields[0].focus(), 150);

            startTimer(30);
            Cart.showToast('OTP sent! Use demo code: 123456');
          } else {
            Cart.showToast(data.message || 'Could not send OTP. Try again.');
            btnContinue.disabled = false;
            btnContinue.textContent = 'Continue';
          }
        } catch (err) {
          Cart.showToast('Network error while requesting OTP.');
          btnContinue.disabled = false;
          btnContinue.textContent = 'Continue';
        }
      });
    }

    // Step 2 OTP Inputs keyboard navigation & paste handling
    otpFields.forEach((field, index) => {
      field.addEventListener('input', (e) => {
        const val = e.target.value.replace(/\D/g, '');
        e.target.value = val ? val.slice(-1) : '';

        if (e.target.value && index < otpFields.length - 1) {
          otpFields[index + 1].focus();
        }

        checkOtpComplete();
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
          checkOtpComplete();
        }
      });
    });

    function checkOtpComplete() {
      const code = Array.from(otpFields).map(f => f.value).join('');
      if (code.length === 6) {
        btnVerify.disabled = false;
        btnVerify.classList.add('active');
      } else {
        btnVerify.disabled = true;
        btnVerify.classList.remove('active');
      }
    }

    // Edit Phone Link (back to step 1)
    if (changePhoneBtn) {
      changePhoneBtn.addEventListener('click', () => {
        clearInterval(countdownInterval);
        stepOtp.style.display = 'none';
        stepPhone.style.display = 'block';
        if (phoneInput) setTimeout(() => phoneInput.focus(), 100);
        btnContinue.disabled = false;
        btnContinue.textContent = 'Continue';
      });
    }

    // Resend OTP
    if (resendBtn) {
      resendBtn.addEventListener('click', () => {
        if (resendBtn.disabled || !currentPhone) return;
        btnContinue.click();
      });
    }

    // Step 2 -> Verify OTP
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
              'X-CSRF-Token': getCsrfToken()
            },
            body: JSON.stringify({ phone: currentPhone, otp: code })
          });

          const data = await res.json();
          if (data.success) {
            // Update auth status
            document.body.dataset.userLoggedIn = 'true';
            const metaAuth = document.querySelector('meta[name="auth-status"]');
            if (metaAuth) metaAuth.content = 'logged_in';
            if (data.csrf_token) {
              const metaCsrf = document.querySelector('meta[name="csrf-token"]');
              if (metaCsrf) metaCsrf.content = data.csrf_token;
            }

            // Update Header "Login / Signup" to "Account"
            const loginBtns = document.querySelectorAll('.login-signup-btn');
            loginBtns.forEach(b => {
              b.outerHTML = `<a href="/account" class="header-action-pill account-pill">
                <span class="avatar-circle">${(data.user?.name || 'U').charAt(0).toUpperCase()}</span>
                <span>${data.user?.name || 'Account'}</span>
              </a>`;
            });

            modal.classList.remove('active');
            resetModal();
            Cart.showToast(`Welcome! Signed in as ${data.user?.name || 'Customer'}`);

            // Resume pending cart action if any
            if (window.supplyKaroPendingAdd) {
              const pending = window.supplyKaroPendingAdd;
              window.supplyKaroPendingAdd = null;

              if (hasUserSetLocation()) {
                Cart.addItem(pending);
              } else {
                const locModal = document.getElementById('location-access-backdrop') || document.getElementById('location-modal-backdrop');
                if (locModal) {
                  window.supplyKaroPendingAdd = pending;
                  locModal.classList.add('active');
                }
              }
            }
          } else {
            Cart.showToast(data.message || 'Verification failed. Try again.');
            btnVerify.disabled = false;
            btnVerify.textContent = 'Verify';
          }
        } catch (err) {
          Cart.showToast('Network error during verification.');
          btnVerify.disabled = false;
          btnVerify.textContent = 'Verify';
        }
      });
    }
  }

  // ----------------------------------------------------------- Pending Cart Action Recovery
  function restorePendingCartAction() {
    if (!isUserLoggedIn()) return;
    try {
      const saved = sessionStorage.getItem('supplykaro_pending_cart_item');
      if (saved) {
        sessionStorage.removeItem('supplykaro_pending_cart_item');
        const item = JSON.parse(saved);
        if (item && item.id) {
          setTimeout(() => {
            Cart.addItem(item);
            Cart.showToast(`Welcome back! Added ${item.name} to your basket.`);
          }, 450);
        }
      }
    } catch (e) {}
  }

  // ----------------------------------------------------------- Off-Canvas Mobile Menu Drawer
  function initMobileMenu() {
    const backdrop = document.getElementById('mobile-menu-backdrop');
    const openBtn = document.getElementById('open-mobile-menu-btn');
    const closeBtn = document.querySelector('.close-mobile-menu');

    if (!backdrop) return;

    function openMenu() {
      backdrop.classList.add('active');
      document.body.style.overflow = 'hidden';
    }

    function closeMenu() {
      backdrop.classList.remove('active');
      document.body.style.overflow = '';
    }

    if (openBtn) {
      openBtn.addEventListener('click', (e) => {
        e.preventDefault();
        openMenu();
      });
    }

    if (closeBtn) {
      closeBtn.addEventListener('click', closeMenu);
    }

    backdrop.addEventListener('click', (e) => {
      if (e.target === backdrop) {
        closeMenu();
      }
    });

    document.querySelectorAll('.mobile-menu-link').forEach(link => {
      link.addEventListener('click', closeMenu);
    });

    // Global listener for auth modal triggers across mobile nav / drawer
    document.addEventListener('click', (e) => {
      if (e.target.closest('.open-auth-modal-btn')) {
        e.preventDefault();
        closeMenu();
        window.SupplyKaro.openAuthModal();
      }
    });
  }

  // ----------------------------------------------------------- DOM Ready
  function onReady(fn) {
    if (document.readyState === 'interactive' || document.readyState === 'complete') {
      setTimeout(fn, 0);
    } else {
      document.addEventListener('DOMContentLoaded', fn);
    }
  }

  onReady(() => {
    Cart.init();
    initHeroCarousel();
    initLocationAccessModal();
    initLocationSelector();
    initAuthModal();
    initSearchOverlay();
    initCartDrawer();
    initMobileMenu();
    initRailScroll();
    initAuthEnhancements();
    initAdminStudioHelpers();
    initPdpCart();
    Cart.render();
    Cart.syncProductCards();
    restorePendingCartAction();
  });

  // Global API (Backwards Compatibility & Direct UI Controls)
  window.SupplyKaro = {
    Cart: Cart,
    handleFastAdd: function(id, btn) {
      handleFastAdd(btn || (typeof id === 'string' ? document.querySelector(`.fast-add-btn[data-id="${id}"]`) : id));
    },
    handleBuyNow: handleBuyNow,
    openAuthModal: function() {
      const modal = document.getElementById('auth-modal-backdrop');
      if (modal) {
        modal.classList.add('active');
        const input = document.getElementById('auth-phone-input');
        if (input) setTimeout(() => input.focus(), 150);
      }
    },
    openLocationAccessModal: function() {
      const modal = document.getElementById('location-access-backdrop');
      if (modal) modal.classList.add('active');
    },
    openLocationModal: function() {
      const modal = document.getElementById('location-modal-backdrop');
      if (modal) modal.classList.add('active');
    },
    resetLocation: function() {
      localStorage.removeItem('supplykaro_location_set');
      localStorage.removeItem('supplykaro_delivery_city');
      localStorage.removeItem('supplykaro_delivery_pincode');
      location.reload();
    }
  };

})();

