<?php /** @var App\Support\View $view */ $view->extend('layouts/admin', ['title' => 'Catalog & Inventory Management']); ?>
<?php $view->start('content'); ?>

<!-- Top Metrics Ribbon -->
<div class="admin-metrics-grid">
  <div class="admin-stat-card">
    <div class="stat-icon-wrap" style="background:#EBF7EB;color:#008000;">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
        <path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>
      </svg>
    </div>
    <div>
      <div class="stat-number tnum"><?= number_format($counts['total']) ?></div>
      <div class="stat-label">Total Catalog Products</div>
    </div>
  </div>

  <div class="admin-stat-card">
    <div class="stat-icon-wrap" style="background:#E0F2FE;color:#0284C7;">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/>
      </svg>
    </div>
    <div>
      <div class="stat-number tnum"><?= number_format($counts['published']) ?></div>
      <div class="stat-label">Active on Live Store</div>
    </div>
  </div>

  <div class="admin-stat-card">
    <div class="stat-icon-wrap" style="background:#FEF3C7;color:#D97706;">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
      </svg>
    </div>
    <div>
      <div class="stat-number tnum"><?= number_format($counts['draft']) ?></div>
      <div class="stat-label">Draft / Unpublished</div>
    </div>
  </div>

  <div class="admin-stat-card">
    <div class="stat-icon-wrap" style="background:#ECFDF5;color:#059669;">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/><path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/>
      </svg>
    </div>
    <div>
      <div class="stat-number tnum"><?= number_format($counts['eco']) ?></div>
      <div class="stat-label">Eco-Certified Products</div>
    </div>
  </div>
</div>

<!-- Toolbar: Search, Filters & Actions -->
<div class="admin-toolbar-card">
  <form method="get" action="/admin/products" class="admin-filter-form">
    <!-- Status Tabs -->
    <div class="admin-filter-tabs">
      <a href="/admin/products?status=all" class="filter-tab <?= ($status === 'all' ? 'active' : '') ?>">All Products (<?= (int)$counts['total'] ?>)</a>
      <a href="/admin/products?status=published" class="filter-tab <?= ($status === 'published' ? 'active' : '') ?>">Active (<?= (int)$counts['published'] ?>)</a>
      <a href="/admin/products?status=draft" class="filter-tab <?= ($status === 'draft' ? 'active' : '') ?>">Drafts (<?= (int)$counts['draft'] ?>)</a>
    </div>

    <div class="admin-filter-inputs">
      <!-- Category Filter -->
      <select name="category_id" class="admin-select" onchange="this.form.submit()">
        <option value="">All Categories</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= (int)$cat['id'] ?>" <?= ($selectedCategoryId === (int)$cat['id'] ? 'selected' : '') ?>>
            <?= $view->e($cat['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <!-- Search Box -->
      <div class="admin-search-wrap">
        <input 
          type="text" 
          name="q" 
          value="<?= $view->e($search) ?>" 
          placeholder="Search product name, SKU, HSN..."
          class="admin-search-input"
        >
        <button type="submit" class="admin-search-btn">Search</button>
      </div>

      <?php if ($search !== '' || $selectedCategoryId !== null || $status !== 'all'): ?>
        <a href="/admin/products" class="btn btn-secondary btn-sm">Reset</a>
      <?php endif; ?>

      <a href="/admin/products/create" class="btn btn-primary btn-sm flex items-center gap-1" style="margin-left: 8px;">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        <span>Add New Product</span>
      </a>
    </div>
  </form>
</div>

<!-- Products Master Table -->
<div class="admin-card-table-wrap">
  <table class="admin-data-table">
    <thead>
      <tr>
        <th style="width: 320px;">Product & SKU Root</th>
        <th>Category</th>
        <th>HSN / Tax</th>
        <th>Variants & Packs</th>
        <th>Base Price / MRP</th>
        <th>Status</th>
        <th style="text-align: right; width: 160px;">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($products)): ?>
        <tr>
          <td colspan="7" class="admin-empty-table">
            <div class="empty-state-inner">
              <div style="margin-bottom: 12px;">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--border-dark)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
                  <path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>
                </svg>
              </div>
              <strong>No products found in this filter</strong>
              <p class="small muted">Try adjusting your search criteria or add your first product listing.</p>
              <a href="/admin/products/create" class="btn btn-primary btn-sm" style="margin-top: 12px;">+ Add New Product</a>
            </div>
          </td>
        </tr>
      <?php else: ?>
        <?php foreach ($products as $p): ?>
          <tr>
            <!-- Thumbnail + Title + SKU -->
            <td>
              <div class="admin-product-cell">
                <img 
                  src="<?= $view->e($p['image_path'] ?? 'https://images.unsplash.com/photo-1577937927133-66ef06acdf18?w=100&auto=format&fit=crop&q=80') ?>" 
                  alt="<?= $view->e($p['name']) ?>" 
                  class="admin-table-thumb"
                >
                <div class="admin-product-meta">
                  <strong class="admin-product-name"><?= $view->e($p['name']) ?></strong>
                  <div class="admin-sku-pill">
                    <span class="mono">SKU: <?= $view->e($p['sku_root']) ?></span>
                    <?php if (!empty($p['is_eco'])): ?>
                      <span class="pill-badge pill-eco">ECO</span>
                    <?php endif; ?>
                    <?php if (!empty($p['is_bestseller'])): ?>
                      <span class="pill-badge pill-bestseller">BESTSELLER</span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </td>

            <!-- Category -->
            <td>
              <span class="admin-category-badge"><?= $view->e($p['category_name'] ?? 'General') ?></span>
            </td>

            <!-- HSN Code -->
            <td>
              <div class="mono small" style="font-weight: 700; color: #334155;">HSN: <?= $view->e($p['hsn_code']) ?></div>
              <div class="tiny muted">GST 18%</div>
            </td>

            <!-- Variants Count -->
            <td>
              <span class="admin-count-badge"><?= (int)$p['variant_count'] ?> Variant(s)</span>
              <div class="tiny muted" style="margin-top:3px;">Unit: <?= $view->e($p['unit_type'] ?? 'piece') ?></div>
            </td>

            <!-- Price Breakdown -->
            <td>
              <div class="admin-price-cell">
                <span class="price-val tnum">₹<?= number_format((float)($p['min_price'] ?? 0), 2) ?></span>
                <?php if (!empty($p['min_mrp']) && (float)$p['min_mrp'] > (float)$p['min_price']): ?>
                  <span class="mrp-val tnum">MRP ₹<?= number_format((float)$p['min_mrp'], 2) ?></span>
                <?php endif; ?>
              </div>
            </td>

            <!-- Status Pill -->
            <td>
              <?php if ($p['status'] === 'published'): ?>
                <span class="status-pill status-active">● Published</span>
              <?php elseif ($p['status'] === 'draft'): ?>
                <span class="status-pill status-draft">● Draft</span>
              <?php else: ?>
                <span class="status-pill status-archived">● Archived</span>
              <?php endif; ?>
            </td>

            <!-- Actions -->
            <td style="text-align: right;">
              <div class="admin-table-actions">
                <a href="/products/<?= $view->e($p['slug']) ?>" target="_blank" class="admin-act-btn" title="View on Customer Storefront">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>
                  </svg>
                </a>
                <a href="/admin/products/<?= (int)$p['id'] ?>/edit" class="admin-act-btn" title="Edit Product">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                  </svg>
                </a>
                <form method="post" action="/admin/products/<?= (int)$p['id'] ?>/delete" style="display:inline;" onsubmit="return confirm('Are you sure you want to archive this product?');">
                  <?= $view->raw($csrf->field()) ?>
                  <button type="submit" class="admin-act-btn btn-delete" title="Archive Product">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                    </svg>
                  </button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php $view->stop(); ?>
