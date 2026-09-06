<?php /** @var App\Support\View $view */ $view->extend('layouts/admin', ['title' => 'Admin Operations Dashboard']); ?>
<?php $view->start('content'); ?>

<!-- Metrics Overview -->
<div class="admin-metrics-grid">
  <div class="admin-stat-card">
    <div class="stat-icon-wrap" style="background:#EBF7EB;color:#008000;">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
        <path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>
      </svg>
    </div>
    <div>
      <div class="stat-number tnum"><?= number_format($stats['products']) ?></div>
      <div class="stat-label">Published Catalog Products</div>
    </div>
  </div>

  <div class="admin-stat-card">
    <div class="stat-icon-wrap" style="background:#FEF3C7;color:#D97706;">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect width="20" height="14" x="2" y="7" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
      </svg>
    </div>
    <div>
      <div class="stat-number tnum"><?= number_format($stats['pending_businesses']) ?></div>
      <div class="stat-label">Pending B2B Verifications</div>
    </div>
  </div>

  <div class="admin-stat-card">
    <div class="stat-icon-wrap" style="background:#E0F2FE;color:#0284C7;">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
      </svg>
    </div>
    <div>
      <div class="stat-number tnum"><?= number_format($stats['customers']) ?></div>
      <div class="stat-label">Registered Customers</div>
    </div>
  </div>

  <div class="admin-stat-card">
    <div class="stat-icon-wrap" style="background:#FEE2E2;color:#DC2626;">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
      </svg>
    </div>
    <div>
      <div class="stat-number tnum"><?= number_format($stats['low_stock']) ?></div>
      <div class="stat-label">Low Stock Inventory Alerts</div>
    </div>
  </div>
</div>

<div class="admin-dashboard-two-col" style="display:grid;grid-template-columns:2fr 1fr;gap:24px;margin-top:24px;">
  
  <!-- Left Action Cards -->
  <div>
    <div class="studio-card">
      <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
        <div style="font-weight:800;font-size:1.15rem;color:var(--text);display:flex;align-items:center;gap:8px;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
          </svg>
          <span>Product &amp; Catalog Studio</span>
        </div>
        <a href="/admin/products/create" class="btn btn-primary btn-sm">+ Add Listing</a>
      </div>
      <p class="small muted" style="margin-bottom:16px;">
        Create new products, upload high-res packaging photography, manage master cartons, and adjust wholesale tier prices.
      </p>
      <div class="flex gap-2">
        <a href="/admin/products" class="btn btn-secondary btn-sm">Open Master Catalog Table →</a>
        <a href="/admin/products/create" class="btn btn-primary btn-sm">+ Create New Product</a>
      </div>
    </div>

    <div class="studio-card" style="margin-top:20px;">
      <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
        <div style="font-weight:800;font-size:1.15rem;color:var(--text);display:flex;align-items:center;gap:8px;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect width="20" height="14" x="2" y="7" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
          </svg>
          <span>B2B Trade Account Approvals</span>
        </div>
        <?php if ($stats['pending_businesses'] > 0): ?>
          <span class="badge badge-warning"><?= (int) $stats['pending_businesses'] ?> awaiting verification</span>
        <?php endif; ?>
      </div>
      <p class="small muted" style="margin-bottom:16px;">
        Verify restaurant, cafe, and cloud kitchen GST numbers. Approving an account instantly activates trade wholesale pricing and GST input credit for the buyer.
      </p>
      <?php if ($gate->allows('b2b.view')): ?>
        <a class="btn btn-primary btn-sm" href="/admin/business-accounts">Open Verification Queue →</a>
      <?php endif; ?>
    </div>
  </div>

  <!-- Right Quick Links & System Health -->
  <div>
    <div class="studio-card">
      <h3 style="font-size:1rem;margin-bottom:12px;">Quick Shortcuts</h3>
      <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:10px;">
        <li><a href="/admin/products/create" class="auth-link-bold flex items-center gap-2">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Create New Product Listing
        </a></li>
        <li><a href="/admin/products" class="auth-link-bold flex items-center gap-2">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/></svg>
          View All <?= number_format($stats['products']) ?> Catalog SKUs
        </a></li>
        <li><a href="/admin/business-accounts" class="auth-link-bold flex items-center gap-2">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="7" rx="2"/></svg>
          B2B Verification Queue
        </a></li>
        <li><a href="/" target="_blank" class="auth-link-bold flex items-center gap-2">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/></svg>
          View Live Customer Storefront ↗
        </a></li>
      </ul>
    </div>

    <div class="studio-card" style="margin-top:20px;background:#F8FAF7;">
      <div style="font-size:0.82rem;font-weight:700;color:var(--primary-dark);margin-bottom:6px;">SUPPLYKARO COMMERCE ENGINE</div>
      <p class="tiny muted" style="line-height:1.5;">
        Running on 3-tier catalog architecture (Products → Variants → Variant Packs). Seamlessly compatible with Razorpay payment gateways and automated GST invoice issuance.
      </p>
    </div>
  </div>

</div>

<?php $view->stop(); ?>
