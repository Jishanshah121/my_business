<?php /** @var App\Support\View $view */ $view->extend('layouts/admin', ['title' => 'Edit Listing · ' . $product['name']]); ?>
<?php $view->start('content'); ?>

<div class="admin-studio-header">
  <div>
    <p class="small muted"><a href="/admin/products">← Back to Catalog</a></p>
    <h2>Edit Product Listing</h2>
    <p class="small muted">Update specifications, wholesale base rates, carton pack breakdowns, or merchandising badges.</p>
  </div>
</div>

<form method="post" action="/admin/products/<?= (int)$product['id'] ?>/update" enctype="multipart/form-data" class="admin-listing-form" id="productEditForm">
  <?= $view->raw($csrf->field()) ?>

  <div class="studio-layout-grid">
    
    <!-- LEFT MAIN COLUMN -->
    <div class="studio-main-col">
      
      <!-- 1. VITAL INFO -->
      <div class="studio-card">
        <div class="studio-card-head">
          <span class="step-num">1</span>
          <div>
            <h3>Vital Product Details</h3>
            <p class="tiny muted">Basic identifiers, statutory GST classification & category mapping</p>
          </div>
        </div>

        <div class="field">
          <label for="name">Product Title / Name <span class="req">*</span></label>
          <input 
            type="text" 
            id="name" 
            name="name" 
            value="<?= $view->e($product['name']) ?>" 
            required 
            autocomplete="off"
          >
        </div>

        <div class="row row-2">
          <div class="field">
            <label for="category_id">Primary Category <span class="req">*</span></label>
            <select id="category_id" name="category_id" required>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= (int)$cat['id'] ?>" <?= ($product['category_id'] == $cat['id'] ? 'selected' : '') ?>>
                  <?= $view->e($cat['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="field">
            <label for="brand_id">Brand / Collection</label>
            <select id="brand_id" name="brand_id">
              <option value="">SupplyKaro Essentials (Default)</option>
              <?php foreach ($brands as $b): ?>
                <option value="<?= (int)$b['id'] ?>" <?= ($product['brand_id'] == $b['id'] ? 'selected' : '') ?>>
                  <?= $view->e($b['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="row row-2">
          <div class="field">
            <label for="hsn_code">HSN Code <span class="req">*</span></label>
            <input type="text" id="hsn_code" name="hsn_code" value="<?= $view->e($product['hsn_code']) ?>" required style="font-family:var(--font-mono);">
          </div>

          <div class="field">
            <label for="material">Material Composition</label>
            <input type="text" id="material" name="material" value="<?= $view->e($product['material'] ?? '') ?>">
          </div>
        </div>
      </div>

      <!-- 2. WHOLESALE PRICING -->
      <div class="studio-card">
        <div class="studio-card-head">
          <span class="step-num">2</span>
          <div>
            <h3>Wholesale Pricing</h3>
            <p class="tiny muted">Base unit prices</p>
          </div>
        </div>

        <div class="row row-2">
          <div class="field">
            <label for="base_unit_price">Wholesale Rate Per Base Unit (₹ Excl. GST) <span class="req">*</span></label>
            <input 
              type="number" 
              step="0.01" 
              id="base_unit_price" 
              name="base_unit_price" 
              value="<?= number_format((float)($variant['base_unit_price'] ?? 1.0), 2, '.', '') ?>" 
              required 
              style="font-size:1.1rem;font-weight:700;color:#008000;"
            >
          </div>

          <div class="field">
            <label for="mrp_unit_price">Reference MRP Per Base Unit (₹)</label>
            <input 
              type="number" 
              step="0.01" 
              id="mrp_unit_price" 
              name="mrp_unit_price" 
              value="<?= number_format((float)($variant['mrp_unit_price'] ?? 1.5), 2, '.', '') ?>" 
              style="font-size:1.1rem;font-weight:700;"
            >
          </div>
        </div>
      </div>

      <!-- 3. SEARCH KEYWORDS & DESCRIPTION -->
      <div class="studio-card">
        <div class="studio-card-head">
          <span class="step-num">3</span>
          <div>
            <h3>Discovery & Search Keywords</h3>
            <p class="tiny muted">Keywords used by search algorithms</p>
          </div>
        </div>

        <div class="field">
          <label for="search_keywords">Search Keywords & Hinglish Synonyms</label>
          <input 
            type="text" 
            id="search_keywords" 
            name="search_keywords" 
            value="<?= $view->e($product['search_keywords'] ?? '') ?>"
          >
        </div>

        <div class="field">
          <label for="short_description">Short Summary</label>
          <input 
            type="text" 
            id="short_description" 
            name="short_description" 
            value="<?= $view->e($product['short_description'] ?? '') ?>"
          >
        </div>

        <div class="field">
          <label for="long_description">Detailed Product Description</label>
          <textarea 
            id="long_description" 
            name="long_description" 
            rows="4"
          ><?= $view->e($product['long_description'] ?? '') ?></textarea>
        </div>
      </div>

    </div>

    <!-- RIGHT SIDEBAR COLUMN -->
    <div class="studio-side-col">
      
      <!-- IMAGE UPLOADER -->
      <div class="studio-card">
        <div class="studio-card-head">
          <span class="step-num">3</span>
          <div>
            <h3>Product Media</h3>
            <p class="tiny muted">1:1 Square High-Resolution Product Photo</p>
          </div>
        </div>

        <div class="studio-upload-box" id="dropzoneBox">
          <input type="file" id="image_file" name="image_file" accept="image/*" class="studio-file-input">
          <div class="upload-drop-content">
            <span class="upload-icon">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/>
              </svg>
            </span>
            <strong>Click to upload replacement photo</strong>
            <span class="tiny muted">Supports JPG, PNG, WebP up to 5MB</span>
          </div>
        </div>

        <div class="image-live-preview-box" style="margin-top:16px;">
          <div class="tiny bold" style="margin-bottom:6px;">CURRENT PRIMARY PHOTO:</div>
          <div class="preview-img-container">
            <img 
              id="liveImagePreview" 
              src="<?= $view->e($images[0]['path'] ?? '/assets/images/products/butterpaper.png') ?>" 
              alt="<?= $view->e($product['name']) ?>"
            >
          </div>
        </div>
      </div>

      <!-- MERCHANDISING FLAGS -->
      <div class="studio-card">
        <div class="studio-card-head">
          <span class="step-num">4</span>
          <div>
            <h3>Storefront Badges</h3>
          </div>
        </div>

        <div class="flags-list">
          <label class="checkbox-row">
            <input type="checkbox" name="is_eco" value="1" <?= !empty($product['is_eco']) ? 'checked' : '' ?>>
            <div>
              <strong>100% Eco-Friendly</strong>
            </div>
          </label>

          <label class="checkbox-row">
            <input type="checkbox" name="is_bestseller" value="1" <?= !empty($product['is_bestseller']) ? 'checked' : '' ?>>
            <div>
              <strong>Bestseller Badge</strong>
            </div>
          </label>

          <label class="checkbox-row">
            <input type="checkbox" name="is_featured" value="1" <?= !empty($product['is_featured']) ? 'checked' : '' ?>>
            <div>
              <strong>Featured Listing</strong>
            </div>
          </label>

          <label class="checkbox-row">
            <input type="checkbox" name="is_new_arrival" value="1" <?= !empty($product['is_new_arrival']) ? 'checked' : '' ?>>
            <div>
              <strong>New Arrival</strong>
            </div>
          </label>
        </div>
      </div>

      <!-- PUBLISH STATUS & SUBMIT -->
      <div class="studio-card studio-publish-card">
        <div class="field">
          <label for="status">Listing Status</label>
          <select id="status" name="status">
            <option value="published" <?= ($product['status'] === 'published' ? 'selected' : '') ?>>Published (Live)</option>
            <option value="draft" <?= ($product['status'] === 'draft' ? 'selected' : '') ?>>Draft (Hidden)</option>
            <option value="archived" <?= ($product['status'] === 'archived' ? 'selected' : '') ?>>Archived</option>
          </select>
        </div>

        <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top:14px;">
          <span>Update Listing →</span>
        </button>
        <a href="/admin/products" class="btn btn-secondary btn-block" style="margin-top:8px;">Cancel</a>
      </div>

    </div>

  </div>
</form>

<?php $view->stop(); ?>
