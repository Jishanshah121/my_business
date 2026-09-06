<?php /** @var App\Support\View $view */ $view->extend('layouts/app'); ?>
<?php $view->start('content'); ?>
<div class="wrap account-dashboard-wrap">

  <!-- Top Breadcrumb & Session Actions -->
  <div class="account-top-bar">
    <nav class="breadcrumbs" aria-label="Breadcrumbs">
      <a href="/">Home</a>
      <span class="sep">/</span>
      <span>My Account</span>
    </nav>
    <div class="account-top-actions">
      <form method="post" action="/logout" style="margin:0;display:inline-block;">
        <?= $view->raw($csrf->field()) ?>
        <button type="submit" class="account-signout-btn" aria-label="Sign out of account">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
            <polyline points="16 17 21 12 16 7"></polyline>
            <line x1="21" y1="12" x2="9" y2="12"></line>
          </svg>
          <span>Sign out</span>
        </button>
      </form>
    </div>
  </div>

  <!-- Profile Hero Banner Card -->
  <div class="account-hero-card">
    <div class="account-hero-left">
      <div class="account-avatar-circle">
        <?= $view->e($user->initials()) ?>
      </div>
      <div class="account-hero-info">
        <div class="account-name-row">
          <h1 class="account-user-name"><?= $view->e($user->fullName()) ?></h1>
          <?php if ($profile !== null && $profile->isApproved()): ?>
            <span class="account-badge account-badge-b2b">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
              Verified B2B Member
            </span>
          <?php elseif ($profile !== null && $profile->isPending()): ?>
            <span class="account-badge account-badge-pending">
              B2B Review Pending
            </span>
          <?php else: ?>
            <span class="account-badge account-badge-retail">
              Retail Member
            </span>
          <?php endif; ?>
        </div>

        <div class="account-contact-meta">
          <span class="contact-meta-item">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            <?= $view->e($user->email) ?>
            <?php if ($user->hasVerifiedEmail()): ?>
              <span class="verified-dot" title="Email Confirmed">✓</span>
            <?php else: ?>
              <a href="/email/verify" class="verify-alert-link">(Verify now)</a>
            <?php endif; ?>
          </span>

          <span class="contact-meta-item">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            <?= $view->e($user->phone ?? 'No mobile added') ?>
          </span>

          <span class="contact-meta-item open-location-modal" style="cursor: pointer;" title="Change Location">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
            <span id="account-city-label">Delivering to Bokaro ▼</span>
          </span>
        </div>
      </div>
    </div>

    <!-- Quick Stats Tiles (Real Computed) -->
    <?php 
      $activeOrdersCount = 0;
      foreach ($orders as $o) {
        if (in_array($o['status'], ['pending', 'confirmed', 'processing', 'packed', 'shipped', 'out_for_delivery'], true)) {
          $activeOrdersCount++;
        }
      }
      $totalOrdersCount = count($orders);
      $totalAddressesCount = count($addresses);
    ?>
    <div class="account-hero-stats">
      <div class="stat-box">
        <div class="stat-number"><?= $activeOrdersCount ?> Active</div>
        <div class="stat-label">In Transit / Processing</div>
      </div>
      <div class="stat-box">
        <div class="stat-number"><?= $totalOrdersCount ?> Orders</div>
        <div class="stat-label">Lifetime Placed</div>
      </div>
      <div class="stat-box">
        <div class="stat-number"><?= $totalAddressesCount ?> Locations</div>
        <div class="stat-label">Saved Outlets</div>
      </div>
      <div class="stat-box">
        <div class="stat-number">100%</div>
        <div class="stat-label">GST ITC Compliant</div>
      </div>
    </div>
  </div>

  <!-- Unverified Email Notification Banner -->
  <?php if (!$user->hasVerifiedEmail()): ?>
    <div class="account-banner-alert">
      <div class="banner-alert-icon">⚠️</div>
      <div class="banner-alert-body">
        <strong>Please confirm your email address.</strong>
        <span>We send order tracking receipts, dispatch alerts, and official GST tax invoices to <?= $view->e($user->email) ?>.</span>
      </div>
      <a href="/email/verify" class="btn btn-sm btn-primary">Confirm Email</a>
    </div>
  <?php endif; ?>

  <!-- Interactive Account Tab Navigation -->
  <div class="account-tabs-wrapper">
    <div class="account-nav-tabs" role="tablist">
      <button type="button" class="account-tab-btn active" data-target="#tab-orders" role="tab" aria-selected="true">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
        <span>Orders &amp; Reorders</span>
        <span class="tab-count-badge"><?= $totalOrdersCount ?></span>
      </button>

      <button type="button" class="account-tab-btn" data-target="#tab-addresses" role="tab" aria-selected="false">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
        <span>Saved Addresses</span>
        <span class="tab-count-badge"><?= $totalAddressesCount ?></span>
      </button>

      <button type="button" class="account-tab-btn" data-target="#tab-business" role="tab" aria-selected="false">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
        <span>Business &amp; GST Invoicing</span>
        <?php if ($profile !== null && $profile->isApproved()): ?>
          <span class="tab-status-pill green">Active</span>
        <?php endif; ?>
      </button>

      <button type="button" class="account-tab-btn" data-target="#tab-profile" role="tab" aria-selected="false">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        <span>Personal Details</span>
      </button>

      <button type="button" class="account-tab-btn" data-target="#tab-security" role="tab" aria-selected="false">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        <span>Sign-in &amp; Security</span>
      </button>
    </div>
  </div>

  <!-- TAB PANELS CONTAINER -->
  <div class="account-content-container">

    <!-- ============================================================== TAB 1: ORDERS & REORDERS -->
    <div class="account-tab-pane active" id="tab-orders">
      
      <?php if (empty($orders)): ?>
        <!-- Real Clean Empty State for Orders -->
        <div class="account-empty-state">
          <div class="empty-state-icon">📦</div>
          <h3 class="empty-state-title">No orders placed yet</h3>
          <p class="empty-state-desc">
            Your active shipments, live delivery tracking, GST tax invoices, and 1-click reorder options will appear here once you place an order.
          </p>
          <div style="margin-top: 20px;">
            <a href="/categories" class="btn btn-primary" style="padding: 12px 28px; font-size: 0.95rem;">
              Browse Packaging Catalog &rarr;
            </a>
          </div>
        </div>
      <?php else: ?>
        <!-- Dynamic Orders Loop from Real Database -->
        <?php foreach ($orders as $index => $order): ?>
          <?php 
            $status = (string) $order['status'];
            $isDelivered = ($status === 'delivered');
            $isInTransit = in_array($status, ['shipped', 'out_for_delivery'], true);
            $isPacked = ($status === 'packed' || $status === 'processing');
            $isConfirmed = ($status === 'confirmed' || $status === 'pending');
            $badgeClass = $isDelivered ? 'delivered' : 'in-transit';
            $statusTitle = match($status) {
              'delivered'        => 'Delivered',
              'out_for_delivery' => 'Out for Delivery',
              'shipped'          => 'Shipped in Transit',
              'packed'           => 'Packed at Warehouse',
              'processing'       => 'Processing Order',
              'cancelled'        => 'Cancelled',
              default            => 'Order Confirmed'
            };
            $orderDateStr = date('d M Y, h:i A', strtotime((string) $order['created_at']));
            $orderGrandTotal = number_format((float) $order['grand_total'], 2);
          ?>
          <div class="order-card <?= $isDelivered ? 'past-order-card' : 'active-order-card' ?>" id="order-card-<?= $order['id'] ?>">
            <div class="order-card-header">
              <div>
                <span class="order-badge <?= $badgeClass ?>">
                  <?php if (!$isDelivered): ?><span class="pulse-dot"></span><?php endif; ?>
                  <?= $view->e($statusTitle) ?>
                </span>
                <span class="order-id"><?= $view->e($order['order_number']) ?></span>
                <span class="order-date">&middot; Placed <?= $view->e($orderDateStr) ?></span>
              </div>
              <div class="order-header-right">
                <span class="order-total-price">&#8377;<?= $view->e($orderGrandTotal) ?></span>
                <span class="order-tax-tag"><?= $view->e($order['payment_method'] ?? 'Paid via Online UPI') ?> &middot; Incl. 18% GST</span>
              </div>
            </div>

            <!-- Stepper -->
            <div class="order-stepper-wrap">
              <div class="order-stepper">
                <div class="stepper-step completed">
                  <div class="step-circle">&#10003;</div>
                  <div class="step-label">Order Confirmed</div>
                  <div class="step-time"><?= date('d M', strtotime((string) $order['created_at'])) ?></div>
                </div>
                <div class="stepper-line <?= ($isPacked || $isInTransit || $isDelivered) ? 'completed' : '' ?>"></div>
                
                <div class="stepper-step <?= ($isPacked || $isInTransit || $isDelivered) ? 'completed' : ($isConfirmed ? 'active' : '') ?>">
                  <div class="step-circle">&#10003;</div>
                  <div class="step-label">Packed at Hub</div>
                  <div class="step-time">Hub Verified</div>
                </div>
                <div class="stepper-line <?= ($isInTransit || $isDelivered) ? 'completed' : ($isPacked ? 'active' : '') ?>"></div>

                <div class="stepper-step <?= $isDelivered ? 'completed' : ($isInTransit ? 'active' : '') ?>">
                  <div class="step-circle">&#128666;</div>
                  <div class="step-label">Out for Delivery</div>
                  <div class="step-time">Bokaro Hub</div>
                </div>
                <div class="stepper-line <?= $isDelivered ? 'completed' : '' ?>"></div>

                <div class="stepper-step <?= $isDelivered ? 'completed' : '' ?>">
                  <div class="step-circle">&#128230;</div>
                  <div class="step-label">Delivered</div>
                  <div class="step-time"><?= $isDelivered ? 'Delivered' : 'Est. Delivery' ?></div>
                </div>
              </div>
            </div>

            <!-- Line items -->
            <div class="order-items-list">
              <?php foreach (($order['items'] ?? []) as $item): ?>
                <div class="order-item-row">
                  <img src="<?= $view->e($item['image_path'] ?? '/assets/images/placeholder/cups.svg') ?>" alt="Product" class="order-item-thumb">
                  <div class="order-item-details">
                    <div class="order-item-title"><?= $view->e($item['product_name']) ?></div>
                    <div class="order-item-meta"><?= $view->e($item['pack_label']) ?> &middot; HSN <?= $view->e($item['hsn_code']) ?> &middot; 18% GST</div>
                  </div>
                  <div class="order-item-pricing">
                    <span class="order-item-qty">Qty: <?= (int) $item['pack_qty'] ?></span>
                    <span class="order-item-amount">&#8377;<?= number_format((float) $item['line_total'], 2) ?></span>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>

            <!-- Footer Actions -->
            <div class="order-card-footer">
              <div class="order-delivery-address">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                <span>Delivering to: <strong><?= $view->e($order['customer_note'] ?? 'Primary Store Outlet') ?> (Bokaro Steel City)</strong></span>
              </div>
              <div class="order-card-actions">
                <?php
                  $reorderPayload = [];
                  foreach (($order['items'] ?? []) as $it) {
                    $reorderPayload[] = [
                      'id'    => $it['sku'],
                      'name'  => $it['product_name'],
                      'price' => (float) $it['pack_price'],
                      'pack'  => $it['pack_label'],
                      'pcs'   => (int) $it['pieces_per_pack'],
                      'qty'   => (int) $it['pack_qty'],
                      'image' => $it['image_path'] ?? '/assets/images/placeholder/cups.svg',
                    ];
                  }
                ?>
                <!-- Real 1-Click Reorder Button -->
                <button 
                  type="button" 
                  class="btn btn-sm btn-primary real-reorder-btn" 
                  data-order-items='<?= htmlspecialchars(json_encode($reorderPayload), ENT_QUOTES, 'UTF-8') ?>'
                >
                  &#128257; Re-order in 1 Click
                </button>
                
                <!-- Real Printable GST Tax Invoice Button -->
                <button 
                  type="button" 
                  class="btn btn-sm btn-outline open-invoice-btn" 
                  data-order='<?= htmlspecialchars(json_encode($order), ENT_QUOTES, 'UTF-8') ?>'
                  data-customer-name="<?= $view->e($user->fullName()) ?>"
                  data-customer-phone="<?= $view->e($user->phone ?? '+91 98765 43210') ?>"
                >
                  &#128196; GST Tax Invoice
                </button>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>

    </div>

    <!-- ============================================================== TAB 2: SAVED ADDRESSES -->
    <div class="account-tab-pane" id="tab-addresses" style="display: none;">
      <div class="tab-pane-header">
        <div>
          <h2 class="section-title">Saved Delivery Locations</h2>
          <p class="section-subtitle">Manage commercial delivery points for your cafes, central prep kitchens, warehouses, or outlets.</p>
        </div>
        <button type="button" class="btn btn-primary" onclick="openAddressModal()">
          + Add New Address
        </button>
      </div>

      <?php if (empty($addresses)): ?>
        <div class="account-empty-state">
          <div class="empty-state-icon">📍</div>
          <h3 class="empty-state-title">No delivery addresses saved yet</h3>
          <p class="empty-state-desc">Save your commercial kitchen, warehouse, cafe outlet, or catering address for quick 1-click checkout.</p>
          <button type="button" class="btn btn-primary" onclick="openAddressModal()">+ Add Delivery Address</button>
        </div>
      <?php else: ?>
        <div class="addresses-grid">
          <?php foreach ($addresses as $addr): ?>
            <div class="address-card <?= !empty($addr['is_default_shipping']) ? 'default-address' : '' ?>" id="addr-card-<?= $addr['id'] ?>">
              <div class="address-card-top">
                <?php if (!empty($addr['is_default_shipping'])): ?>
                  <span class="address-tag default">Default Outlet</span>
                <?php else: ?>
                  <span class="address-tag secondary"><?= $view->e($addr['label'] ?? 'Secondary Location') ?></span>
                <?php endif; ?>
                <span class="address-type-label"><?= $view->e($addr['company_name'] ?? 'Commercial Outlet') ?></span>
              </div>
              <h3 class="address-recipient-name"><?= $view->e($addr['contact_name']) ?></h3>
              <p class="address-text">
                <?= $view->e($addr['line1']) ?><br>
                <?php if (!empty($addr['line2'])): ?><?= $view->e($addr['line2']) ?><br><?php endif; ?>
                <?= $view->e($addr['city']) ?>, <?= $view->e($addr['state_name']) ?> - <strong><?= $view->e($addr['pincode']) ?></strong>
              </p>
              <div class="address-contact-phone">
                <span>&#128222; Contact: <?= $view->e($addr['contact_phone']) ?></span>
                <?php if (!empty($addr['gstin'])): ?>
                  <span>&#128203; GSTIN: <strong class="mono"><?= $view->e($addr['gstin']) ?></strong></span>
                <?php endif; ?>
              </div>
              <div class="address-card-actions">
                <?php if (empty($addr['is_default_shipping'])): ?>
                  <form method="post" action="/account/addresses/<?= $addr['id'] ?>/default" style="margin:0;">
                    <?= $view->raw($csrf->field()) ?>
                    <button type="submit" class="btn btn-sm btn-outline">Set as Default</button>
                  </form>
                <?php endif; ?>

                <button 
                  type="button" 
                  class="btn btn-sm btn-outline edit-address-btn" 
                  data-address='<?= htmlspecialchars(json_encode($addr), ENT_QUOTES, 'UTF-8') ?>'
                >
                  Edit
                </button>

                <form method="post" action="/account/addresses/<?= $addr['id'] ?>/delete" style="margin:0;" onsubmit="return confirm('Remove this delivery address?');">
                  <?= $view->raw($csrf->field()) ?>
                  <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- ============================================================== TAB 3: BUSINESS PROFILE & GST -->
    <div class="account-tab-pane" id="tab-business" style="display: none;">
      <?php if ($profile === null): ?>
        <!-- B2B Upgrade Pitch Banner -->
        <div class="b2b-upgrade-banner">
          <div class="b2b-upgrade-content">
            <span class="b2b-chip">Commercial &amp; B2B Account</span>
            <h2 class="b2b-title">Unlock Wholesale Pricing &amp; 100% GST Tax Credit</h2>
            <p class="b2b-desc">
              Are you running a cafe, cloud kitchen, QSR, bakery, catering service, or corporate pantry? 
              Register your business profile to immediately unlock up to <strong>28% wholesale trade discounts</strong>, 
              priority pallet delivery, and monthly GST input credit invoices.
            </p>
            <div class="b2b-perks-grid">
              <div class="b2b-perk-item">
                <span class="perk-icon">💰</span>
                <div>
                  <strong>Wholesale Volume Pricing</strong>
                  <p class="small muted">Pay less as you scale with master carton tiers.</p>
                </div>
              </div>
              <div class="b2b-perk-item">
                <span class="perk-icon">📑</span>
                <div>
                  <strong>100% Compliant GST Invoicing</strong>
                  <p class="small muted">Claim your Input Tax Credit (ITC) effortlessly each month.</p>
                </div>
              </div>
              <div class="b2b-perk-item">
                <span class="perk-icon">💳</span>
                <div>
                  <strong>Net-30 Credit Line</strong>
                  <p class="small muted">Flexible credit facilities for verified enterprises.</p>
                </div>
              </div>
            </div>
            <div style="margin-top: 24px;">
              <a href="/register/business" class="btn btn-primary" style="padding: 12px 28px; font-size: 1rem;">
                Register Your Business Profile Now →
              </a>
            </div>
          </div>
        </div>
      <?php else: ?>
        <!-- Verified Business Profile Details -->
        <div class="business-profile-card">
          <div class="profile-card-top">
            <div>
              <h2 class="company-name"><?= $view->e($profile->companyName) ?></h2>
              <p class="company-type"><?= $view->e($profile->businessTypeName ?? 'Commercial Food Service Enterprise') ?></p>
            </div>
            <span class="account-badge account-badge-b2b">
              <?= $view->e($profile->statusLabel()) ?>
            </span>
          </div>

          <?php if ($profile->isApproved()): ?>
            <div class="alert alert-success" style="margin-top: 16px;">
              <span><strong>Trade Pricing Active:</strong> Your business account is verified and receiving automated bulk tier discounts on all orders.</span>
            </div>
          <?php elseif ($profile->isPending()): ?>
            <div class="alert alert-warning" style="margin-top: 16px;">
              <span><strong>Verification In Progress:</strong> Our compliance team is verifying your GSTIN &amp; business documents within 24 hours.</span>
            </div>
          <?php endif; ?>

          <div class="business-info-table-wrap">
            <table class="business-info-table">
              <tbody>
                <tr>
                  <th>Contact Person</th>
                  <td><strong><?= $view->e($profile->contactPerson) ?></strong></td>
                </tr>
                <tr>
                  <th>Official Contact Phone</th>
                  <td class="mono"><?= $view->e($profile->contactPhone) ?></td>
                </tr>
                <tr>
                  <th>Registered GSTIN</th>
                  <td class="mono">
                    <span class="gst-tag"><?= $view->e($profile->gstin ?? 'Not Provided') ?></span>
                    <span class="small muted">&middot; Verified for 18% Input Tax Credit</span>
                  </td>
                </tr>
                <tr>
                  <th>PAN Number</th>
                  <td class="mono"><?= $view->e($profile->pan ?? '—') ?></td>
                </tr>
                <tr>
                  <th>FSSAI License</th>
                  <td class="mono"><?= $view->e($profile->fssaiLicence ?? 'CPCB / Green Compliant') ?></td>
                </tr>
                <?php if ($profile->expectedMonthlySpend !== null): ?>
                  <tr>
                    <th>Expected Monthly Volume</th>
                    <td><strong>&#8377;<?= $view->e(number_format((float) $profile->expectedMonthlySpend, 2)) ?></strong></td>
                  </tr>
                <?php endif; ?>
                <?php if ($profile->creditEnabled): ?>
                  <tr>
                    <th>Credit Facility</th>
                    <td>
                      <span class="badge badge-success">Approved</span>
                      <strong>&#8377;<?= $view->e(number_format((float) $profile->creditLimit, 2)) ?></strong> (Net <?= (int) $profile->creditDays ?> days terms)
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <!-- ============================================================== TAB 4: PERSONAL DETAILS -->
    <div class="account-tab-pane" id="tab-profile" style="display: none;">
      <div class="tab-card">
        <div class="tab-card-header">
          <h2 class="section-title">Personal Information</h2>
          <p class="section-subtitle">Update your personal contact details used for dispatch SMS and order notifications.</p>
        </div>

        <form method="post" action="/account/profile" class="account-form" novalidate>
          <?= $view->raw($csrf->field()) ?>

          <div class="form-row-2">
            <div class="form-group">
              <label for="first_name">First Name <span class="required">*</span></label>
              <input type="text" name="first_name" id="first_name" class="form-control" value="<?= $view->e($old['first_name'] ?? $user->firstName) ?>" required>
              <?php if (isset($errors['first_name'])): ?>
                <div class="error-feedback"><?= $view->e($errors['first_name'][0]) ?></div>
              <?php endif; ?>
            </div>

            <div class="form-group">
              <label for="last_name">Last Name</label>
              <input type="text" name="last_name" id="last_name" class="form-control" value="<?= $view->e($old['last_name'] ?? ($user->lastName ?? '')) ?>">
              <?php if (isset($errors['last_name'])): ?>
                <div class="error-feedback"><?= $view->e($errors['last_name'][0]) ?></div>
              <?php endif; ?>
            </div>
          </div>

          <div class="form-row-2">
            <div class="form-group">
              <label for="phone">Mobile Number</label>
              <div class="phone-input-group">
                <span class="phone-prefix">+91</span>
                <input type="tel" name="phone" id="phone" class="form-control" maxlength="10" value="<?= $view->e($old['phone'] ?? ($user->phone ?? '')) ?>" placeholder="10-digit mobile number">
              </div>
              <div class="form-hint">Used for live WhatsApp &amp; SMS delivery tracking.</div>
              <?php if (isset($errors['phone'])): ?>
                <div class="error-feedback"><?= $view->e($errors['phone'][0]) ?></div>
              <?php endif; ?>
            </div>

            <div class="form-group">
              <label for="email_display">Email Address</label>
              <input type="email" id="email_display" class="form-control disabled" value="<?= $view->e($user->email) ?>" disabled>
              <div class="form-hint">
                <?php if ($user->hasVerifiedEmail()): ?>
                  <span class="text-success">✓ Confirmed.</span> Contact support to update your email.
                <?php else: ?>
                  <span class="text-warning">Not confirmed yet.</span> <a href="/email/verify">Send confirmation link</a>.
                <?php endif; ?>
              </div>
            </div>
          </div>

          <div class="form-group checkbox-group" style="margin-top: 12px;">
            <label class="custom-checkbox">
              <input type="checkbox" name="marketing_opt_in" value="1" <?= $user->marketingOptIn ? 'checked' : '' ?>>
              <span>Send me updates on bulk trade discounts, seasonal festival packaging, and new eco-friendly arrivals.</span>
            </label>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save Profile Changes</button>
          </div>
        </form>
      </div>
    </div>

    <!-- ============================================================== TAB 5: SIGN-IN & SECURITY -->
    <div class="account-tab-pane" id="tab-security" style="display: none;">
      <div class="grid-2-col">
        
        <!-- Password Change Card -->
        <div class="tab-card">
          <div class="tab-card-header">
            <h2 class="section-title">Change Password</h2>
            <p class="section-subtitle">Ensure your account is using a strong password.</p>
          </div>

          <form method="post" action="/account/security/password" class="account-form" novalidate>
            <?= $view->raw($csrf->field()) ?>

            <div class="form-group">
              <label for="current_password">Current Password <span class="required">*</span></label>
              <input type="password" name="current_password" id="current_password" class="form-control" autocomplete="current-password" required>
              <?php if (isset($errors['current_password'])): ?>
                <div class="error-feedback"><?= $view->e($errors['current_password'][0]) ?></div>
              <?php endif; ?>
            </div>

            <div class="form-group">
              <label for="password">New Password <span class="required">*</span></label>
              <input type="password" name="password" id="password" class="form-control" autocomplete="new-password" required>
              <div class="form-hint">Minimum 8 characters with at least one number or symbol.</div>
              <?php if (isset($errors['password'])): ?>
                <div class="error-feedback"><?= $view->e($errors['password'][0]) ?></div>
              <?php endif; ?>
            </div>

            <div class="form-group">
              <label for="password_confirmation">Confirm New Password <span class="required">*</span></label>
              <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" autocomplete="new-password" required>
            </div>

            <div class="form-actions">
              <button type="submit" class="btn btn-primary">Update Password</button>
            </div>
          </form>
        </div>

        <!-- Active Sessions & Login Activity -->
        <div class="tab-card">
          <div class="tab-card-header">
            <h2 class="section-title">Active Devices &amp; Security</h2>
            <p class="section-subtitle">Review active sign-in sessions for your account.</p>
          </div>

          <div class="active-session-box">
            <div class="session-icon">💻</div>
            <div class="session-info">
              <strong>Current Active Session</strong>
              <p class="small muted">This Web Browser · Logged in today</p>
            </div>
            <span class="badge badge-success">Current</span>
          </div>

          <div style="margin-top: 24px;">
            <form method="post" action="/account/security/sessions">
              <?= $view->raw($csrf->field()) ?>
              <button type="submit" class="btn btn-outline-danger" style="width: 100%;">
                Sign out of all other devices
              </button>
            </form>
            <p class="tiny muted" style="margin-top: 8px; text-align: center;">
              This revokes access tokens on any other phones, tablets, or shared computers.
            </p>
          </div>

          <?php if (!empty($attempts)): ?>
            <div style="margin-top: 28px;">
              <h4 class="small bold" style="text-transform: uppercase; margin-bottom: 8px; color: var(--text-muted);">Recent Sign-In Activity</h4>
              <div class="table-wrap">
                <table class="tiny" style="width: 100%;">
                  <thead>
                    <tr><th>Status</th><th>IP</th><th>Date</th></tr>
                  </thead>
                  <tbody>
                    <?php foreach ($attempts as $a): ?>
                      <tr>
                        <td>
                          <?php $success = (int) ($a['successful'] ?? 0) === 1; ?>
                          <span class="badge badge-<?= $success ? 'success' : 'danger' ?>" style="font-size:0.7rem;">
                            <?= $success ? 'Success' : 'Failed' ?>
                          </span>
                        </td>
                        <td class="mono"><?= $view->e($a['ip'] ?? '—') ?></td>
                        <td><?= $view->e(isset($a['created_at']) ? date('d M H:i', strtotime((string) $a['created_at'])) : '—') ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          <?php endif; ?>

        </div>
      </div>
    </div>

  </div>

  <!-- Dedicated Support Desk Banner -->
  <div class="account-support-banner">
    <div class="support-banner-left">
      <div class="support-icon-badge">💬</div>
      <div>
        <h3 class="support-title">Need Immediate Supply Assistance or Custom Printing?</h3>
        <p class="support-subtitle">Our B2B key account managers are available Monday to Saturday, 8 AM – 8 PM.</p>
      </div>
    </div>
    <div class="support-banner-actions">
      <a href="https://wa.me/919876543210" target="_blank" class="btn btn-whatsapp">
        <span>Chat on WhatsApp</span>
      </a>
      <a href="tel:1800787759" class="btn btn-outline">
        <span>📞 1800-SUPPLY</span>
      </a>
    </div>
  </div>

</div>

<!-- ==================================================================== -->
<!-- ADD / EDIT ADDRESS MODAL -->
<!-- ==================================================================== -->
<div id="address-modal-backdrop" class="modal-backdrop">
  <div class="modal-content address-modal-card">
    <div class="modal-header">
      <h3 id="address-modal-title">Add New Delivery Location</h3>
      <button type="button" class="modal-close" onclick="closeAddressModal()">&times;</button>
    </div>
    <form id="address-modal-form" method="post" action="/account/addresses">
      <?= $view->raw($csrf->field()) ?>

      <div class="form-row-2">
        <div class="form-group">
          <label>Address Label / Type *</label>
          <input type="text" name="label" id="addr_label" class="form-control" placeholder="e.g. Main Outlet, Kitchen, Warehouse" value="Main Outlet" required>
        </div>
        <div class="form-group">
          <label>Registered Company / Outlet Name</label>
          <input type="text" name="company_name" id="addr_company" class="form-control" placeholder="e.g. Cafe Royale Pvt Ltd">
        </div>
      </div>

      <div class="form-row-2">
        <div class="form-group">
          <label>Contact Person Full Name *</label>
          <input type="text" name="contact_name" id="addr_name" class="form-control" value="<?= $view->e($user->fullName()) ?>" required>
        </div>
        <div class="form-group">
          <label>Mobile Number (for Delivery OTP) *</label>
          <input type="tel" name="contact_phone" id="addr_phone" class="form-control" value="<?= $view->e($user->phone ?? '+91 98765 43210') ?>" required>
        </div>
      </div>

      <div class="form-group">
        <label>Street Address / Unit / Shed *</label>
        <input type="text" name="line1" id="addr_line1" class="form-control" placeholder="Plot No, Shop No, Commercial Complex" required>
      </div>

      <div class="form-row-2">
        <div class="form-group">
          <label>Area / Locality</label>
          <input type="text" name="line2" id="addr_line2" class="form-control" placeholder="Sector, Industrial Area">
        </div>
        <div class="form-group">
          <label>Nearby Landmark</label>
          <input type="text" name="landmark" id="addr_landmark" class="form-control" placeholder="Near Cargo Gate, City Centre">
        </div>
      </div>

      <div class="form-row-3">
        <div class="form-group">
          <label>City *</label>
          <input type="text" name="city" id="addr_city" class="form-control" value="Bokaro Steel City" required>
        </div>
        <div class="form-group">
          <label>State *</label>
          <input type="text" name="state_name" id="addr_state" class="form-control" value="Jharkhand" required>
          <input type="hidden" name="state_code" id="addr_state_code" value="20">
        </div>
        <div class="form-group">
          <label>Pincode *</label>
          <input type="text" name="pincode" id="addr_pincode" class="form-control" maxlength="6" value="827004" required>
        </div>
      </div>

      <div class="form-group">
        <label>Branch GSTIN (Optional, for tax invoices to this site)</label>
        <input type="text" name="gstin" id="addr_gstin" class="form-control" placeholder="20AAAAA0000A1Z5">
      </div>

      <div class="form-group" style="margin-top: 10px;">
        <label class="custom-checkbox">
          <input type="checkbox" name="is_default_shipping" id="addr_is_default" value="1" checked>
          <span>Set as default delivery address</span>
        </label>
      </div>

      <div class="form-actions modal-footer-actions">
        <button type="button" class="btn btn-outline" onclick="closeAddressModal()">Cancel</button>
        <button type="submit" class="btn btn-primary" id="address-submit-btn">Save Address</button>
      </div>
    </form>
  </div>
</div>

<!-- ==================================================================== -->
<!-- GST TAX INVOICE MODAL -->
<!-- ==================================================================== -->
<div id="invoice-modal-backdrop" class="modal-backdrop">
  <div class="modal-content invoice-modal-card">
    <div class="modal-header invoice-header-bar">
      <div>
        <h3 style="margin:0;">Tax Invoice</h3>
        <span class="small muted">Rule 46 of CGST Rules, 2017 &middot; Original for Recipient</span>
      </div>
      <div style="display:flex;gap:8px;">
        <button type="button" class="btn btn-sm btn-primary" onclick="window.print()">&#128424; Print / PDF</button>
        <button type="button" class="modal-close" onclick="closeInvoiceModal()">&times;</button>
      </div>
    </div>
    
    <div class="invoice-printable-body" id="invoice-body-content">
      <!-- Injected via JavaScript from data-order -->
    </div>
  </div>
</div>

<!-- Client-side Tab Switcher, Address Modal, Reorder & Invoice Script -->
<script>
function openAddressModal(data = null) {
  const modal = document.getElementById('address-modal-backdrop');
  const title = document.getElementById('address-modal-title');
  const form = document.getElementById('address-modal-form');
  const submitBtn = document.getElementById('address-submit-btn');

  if (!modal) return;

  if (data) {
    title.textContent = 'Edit Delivery Address';
    form.action = `/account/addresses/${data.id}/edit`;
    submitBtn.textContent = 'Update Address';
    document.getElementById('addr_label').value = data.label || 'Main Outlet';
    document.getElementById('addr_company').value = data.company_name || '';
    document.getElementById('addr_name').value = data.contact_name || '';
    document.getElementById('addr_phone').value = data.contact_phone || '';
    document.getElementById('addr_line1').value = data.line1 || '';
    document.getElementById('addr_line2').value = data.line2 || '';
    document.getElementById('addr_landmark').value = data.landmark || '';
    document.getElementById('addr_city').value = data.city || '';
    document.getElementById('addr_state').value = data.state_name || 'Jharkhand';
    document.getElementById('addr_pincode').value = data.pincode || '';
    document.getElementById('addr_gstin').value = data.gstin || '';
    document.getElementById('addr_is_default').checked = !!parseInt(data.is_default_shipping);
  } else {
    title.textContent = 'Add New Delivery Location';
    form.action = '/account/addresses';
    submitBtn.textContent = 'Save Address';
    form.reset();
  }

  modal.classList.add('active');
}

function closeAddressModal() {
  const modal = document.getElementById('address-modal-backdrop');
  if (modal) modal.classList.remove('active');
}

function closeInvoiceModal() {
  const modal = document.getElementById('invoice-modal-backdrop');
  if (modal) modal.classList.remove('active');
}

document.addEventListener('DOMContentLoaded', () => {
  const tabs = document.querySelectorAll('.account-tab-btn');
  const panes = document.querySelectorAll('.account-tab-pane');

  function activateTab(targetId) {
    tabs.forEach(t => {
      const isMatch = t.dataset.target === targetId;
      t.classList.toggle('active', isMatch);
      t.setAttribute('aria-selected', isMatch ? 'true' : 'false');
    });
    panes.forEach(p => {
      const isMatch = '#' + p.id === targetId;
      p.style.display = isMatch ? 'block' : 'none';
      p.classList.toggle('active', isMatch);
    });
  }

  tabs.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const target = btn.dataset.target;
      activateTab(target);
      if (history.pushState) {
        history.pushState(null, null, target);
      }
    });
  });

  if (window.location.hash) {
    activateTab(window.location.hash);
  }

  // Edit Address button triggers
  document.querySelectorAll('.edit-address-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      try {
        const data = JSON.parse(btn.dataset.address);
        openAddressModal(data);
      } catch (err) {
        console.error(err);
      }
    });
  });

  // Real 1-Click Reorder Buttons
  document.querySelectorAll('.real-reorder-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      try {
        const items = JSON.parse(btn.dataset.orderItems || '[]');
        if (!items || items.length === 0) {
          SupplyKaro.Cart.showToast('No items found in this order.');
          return;
        }

        if (window.SupplyKaro && window.SupplyKaro.Cart) {
          items.forEach(it => window.SupplyKaro.Cart.addItem(it));
          window.SupplyKaro.Cart.showToast(`Reordered ${items.length} item(s)! Added to your shopping basket.`);
        }
      } catch (err) {
        console.error(err);
      }
    });
  });

  // Real GST Tax Invoice Viewer
  document.querySelectorAll('.open-invoice-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      try {
        const order = JSON.parse(btn.dataset.order || '{}');
        const custName = btn.dataset.customerName || 'Customer';
        const custPhone = btn.dataset.customerPhone || '';
        const modal = document.getElementById('invoice-modal-backdrop');
        const container = document.getElementById('invoice-body-content');

        if (!modal || !container) return;

        let itemsHtml = '';
        let totalQty = 0;
        (order.items || []).forEach((item, idx) => {
          totalQty += parseInt(item.pack_qty || 1);
          itemsHtml += `
            <tr>
              <td>${idx + 1}</td>
              <td><strong>${item.product_name}</strong><br><small class="muted">${item.pack_label}</small></td>
              <td>${item.hsn_code || '48191000'}</td>
              <td>${item.pack_qty}</td>
              <td>₹${parseFloat(item.pack_price).toFixed(2)}</td>
              <td>₹${parseFloat(item.taxable_value).toFixed(2)}</td>
              <td>9% (₹${parseFloat(item.cgst_amount).toFixed(2)})</td>
              <td>9% (₹${parseFloat(item.sgst_amount).toFixed(2)})</td>
              <td style="text-align:right;font-weight:700;">₹${parseFloat(item.line_total).toFixed(2)}</td>
            </tr>
          `;
        });

        container.innerHTML = `
          <div class="invoice-box">
            <div class="invoice-header">
              <div>
                <h2 style="margin:0;color:var(--primary);font-size:1.6rem;font-weight:800;">SupplyKaro</h2>
                <div class="small" style="margin-top:4px;">
                  SupplyKaro Technologies Pvt. Ltd.<br>
                  Plot 14-B, Commercial Industrial Zone, Bokaro - 827004, Jharkhand<br>
                  <strong>GSTIN: 20AAGCS1234F1Z5</strong> &middot; State: Jharkhand (20)<br>
                  Email: accounts@supplykaro.com &middot; Support: 1800-SUPPLY
                </div>
              </div>
              <div style="text-align:right;">
                <h3 style="margin:0;color:#0F172A;">TAX INVOICE</h3>
                <div class="small" style="margin-top:4px;">
                  <strong>Invoice No:</strong> INV-${order.order_number || '2026'}<br>
                  <strong>Order No:</strong> ${order.order_number || ''}<br>
                  <strong>Invoice Date:</strong> ${order.placed_at || order.created_at || 'Today'}<br>
                  <strong>Payment Mode:</strong> ${order.payment_method || 'Online UPI'} (Paid)
                </div>
              </div>
            </div>

            <div class="invoice-parties" style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin:20px 0;padding:16px;background:#F8FAFC;border-radius:8px;">
              <div>
                <strong style="text-transform:uppercase;font-size:0.75rem;color:#64748B;">Billed &amp; Shipped To:</strong>
                <div style="font-size:0.92rem;margin-top:4px;">
                  <strong>${custName}</strong><br>
                  ${order.customer_note || 'Commercial Store Delivery Address'}<br>
                  Bokaro Steel City, Jharkhand - 827004<br>
                  Phone: ${custPhone}<br>
                  ${order.buyer_gstin ? `<strong>Buyer GSTIN:</strong> <span class="mono">${order.buyer_gstin}</span>` : '<span class="muted">Buyer GSTIN: Unregistered Consumer</span>'}
                </div>
              </div>
              <div>
                <strong style="text-transform:uppercase;font-size:0.75rem;color:#64748B;">Place of Supply &amp; Terms:</strong>
                <div style="font-size:0.92rem;margin-top:4px;">
                  <strong>Place of Supply:</strong> Jharkhand (20)<br>
                  <strong>Reverse Charge:</strong> No<br>
                  <strong>Dispatch Via:</strong> SupplyKaro Express Ground<br>
                  <strong>HSN Category:</strong> 4819 / 4823 (Food Grade Packaging)
                </div>
              </div>
            </div>

            <table class="invoice-items-table" style="width:100%;border-collapse:collapse;margin:16px 0;">
              <thead>
                <tr style="background:#F1F5F9;border-bottom:2px solid #CBD5E1;text-align:left;font-size:0.8rem;">
                  <th style="padding:8px;">#</th>
                  <th style="padding:8px;">Description</th>
                  <th style="padding:8px;">HSN</th>
                  <th style="padding:8px;">Qty</th>
                  <th style="padding:8px;">Rate</th>
                  <th style="padding:8px;">Taxable</th>
                  <th style="padding:8px;">CGST</th>
                  <th style="padding:8px;">SGST</th>
                  <th style="padding:8px;text-align:right;">Amount</th>
                </tr>
              </thead>
              <tbody style="font-size:0.86rem;">
                ${itemsHtml}
              </tbody>
              <tfoot style="border-top:2px solid #CBD5E1;font-size:0.9rem;">
                <tr>
                  <th colspan="5" style="padding:8px;text-align:right;">Taxable Value:</th>
                  <td colspan="4" style="padding:8px;text-align:right;font-weight:700;">₹${parseFloat(order.taxable_value || 0).toFixed(2)}</td>
                </tr>
                <tr>
                  <th colspan="5" style="padding:8px;text-align:right;">Total GST (CGST 9% + SGST 9%):</th>
                  <td colspan="4" style="padding:8px;text-align:right;font-weight:700;">₹${parseFloat(order.tax_total || 0).toFixed(2)}</td>
                </tr>
                <tr style="background:#ECFDF5;font-size:1.1rem;color:#065F46;">
                  <th colspan="5" style="padding:10px;text-align:right;font-weight:800;">Grand Total (INR):</th>
                  <td colspan="4" style="padding:10px;text-align:right;font-weight:800;">₹${parseFloat(order.grand_total || 0).toFixed(2)}</td>
                </tr>
              </tfoot>
            </table>

            <div style="margin-top:24px;padding-top:16px;border-top:1px dashed #CBD5E1;display:flex;justify-content:space-between;align-items:center;">
              <div class="small muted">
                This is a computer-generated tax invoice verified under Rule 46 of CGST Rules, 2017.<br>
                Thank you for choosing SupplyKaro for your commercial supplies!
              </div>
              <div style="text-align:right;">
                <div style="font-size:0.8rem;font-weight:700;color:#0F172A;">For SupplyKaro Technologies Pvt. Ltd.</div>
                <div style="margin-top:28px;border-top:1px solid #94A3B8;display:inline-block;padding-top:4px;font-size:0.75rem;color:#64748B;">
                  Authorized Signatory
                </div>
              </div>
            </div>
          </div>
        `;

        modal.classList.add('active');
      } catch (err) {
        console.error(err);
      }
    });
  });
});
</script>

<?php $view->stop(); ?>
