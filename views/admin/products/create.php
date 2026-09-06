<?php /** @var App\Support\View $view */ $view->extend('layouts/admin', ['title' => 'Create New Product Listing']); ?>
<?php $view->start('content'); ?>

<div class="admin-studio-header">
  <div>
    <p class="small muted"><a href="/admin/products">← Back to Catalog</a></p>
    <h2>Add New Marketplace Listing</h2>
    <p class="small muted">Create a multi-pack wholesale product with automated GST compliance, stock units, and master carton breakdown.</p>
  </div>
</div>

<form method="post" action="/admin/products" enctype="multipart/form-data" class="admin-listing-form" id="productCreateForm">
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
            placeholder="e.g. Ripple Wall Insulated Kraft Paper Cups (250 ml)" 
            required 
            autocomplete="off"
          >
          <div class="hint">Use a descriptive name including volume/size and primary material.</div>
        </div>

        <div class="row row-2">
          <div class="field">
            <label for="category_id">Primary Category <span class="req">*</span></label>
            <select id="category_id" name="category_id" required>
              <option value="">Choose Category</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= (int)$cat['id'] ?>"><?= $view->e($cat['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="field">
            <label for="brand_id">Brand / Collection</label>
            <select id="brand_id" name="brand_id">
              <option value="">SupplyKaro Essentials (Default)</option>
              <?php foreach ($brands as $b): ?>
                <option value="<?= (int)$b['id'] ?>"><?= $view->e($b['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="row row-3">
          <div class="field">
            <label for="sku_root">SKU Root (Prefix)</label>
            <input type="text" id="sku_root" name="sku_root" placeholder="e.g. RC-CUP" style="text-transform:uppercase;">
            <div class="hint">Auto-generated if left blank.</div>
          </div>

          <div class="field">
            <label for="hsn_code">HSN Code <span class="req">*</span></label>
            <input type="text" id="hsn_code" name="hsn_code" value="4823" placeholder="4823" required style="font-family:var(--font-mono);">
            <div class="hint">18% GST default</div>
          </div>

          <div class="field">
            <label for="unit_type">Base Unit</label>
            <select id="unit_type" name="unit_type">
              <option value="piece">Piece (Pcs)</option>
              <option value="roll">Roll</option>
              <option value="sheet">Sheet</option>
              <option value="box">Box</option>
              <option value="pack">Pack</option>
            </select>
          </div>
        </div>

        <div class="field">
          <label for="material">Material Composition</label>
          <input type="text" id="material" name="material" placeholder="e.g. 3-Layer Insulated Virgin Kraft Pulp, 280 GSM">
        </div>
      </div>

      <!-- 2. WHOLESALE PRICING & PACK SIZES -->
      <div class="studio-card">
        <div class="studio-card-head">
          <span class="step-num">2</span>
          <div>
            <h3>Wholesale Pricing & Master Carton Packs</h3>
            <p class="tiny muted">Base unit prices and automated carton quantity tier derivations</p>
          </div>
        </div>

        <div class="row row-2">
          <div class="field">
            <label for="variant_name">Default Variant Name</label>
            <input type="text" id="variant_name" name="variant_name" value="250 ml (Standard Brown)" required>
          </div>

          <div class="field">
            <label for="variant_code">Variant Code</label>
            <input type="text" id="variant_code" name="variant_code" value="250K" required style="text-transform:uppercase;font-family:var(--font-mono);">
          </div>
        </div>

        <div class="pricing-calc-box">
          <div class="row row-2">
            <div class="field">
              <label for="base_unit_price">Wholesale Rate Per Base Unit (₹ Excl. GST) <span class="req">*</span></label>
              <input 
                type="number" 
                step="0.01" 
                id="base_unit_price" 
                name="base_unit_price" 
                value="2.80" 
                required 
                class="calc-trigger"
                style="font-size:1.1rem;font-weight:700;color:#008000;"
              >
              <div class="hint">True base cost per single piece/unit</div>
            </div>

            <div class="field">
              <label for="mrp_unit_price">Reference MRP Per Base Unit (₹)</label>
              <input 
                type="number" 
                step="0.01" 
                id="mrp_unit_price" 
                name="mrp_unit_price" 
                value="4.50" 
                class="calc-trigger"
                style="font-size:1.1rem;font-weight:700;"
              >
              <div class="hint">Printed retail benchmark price</div>
            </div>
          </div>
        </div>

        <!-- Pack 1 & Pack 2 Breakdown -->
        <div class="packs-config-grid" style="margin-top:16px;">
          <!-- Pack 1 -->
          <div class="pack-card">
            <div class="pack-badge">PACK 1 (RETAIL SLEEVE)</div>
            <div class="row row-2" style="margin-top:10px;">
              <div class="field">
                <label>Pieces / Pack</label>
                <input type="number" id="pack_1_qty" name="pack_1_qty" value="50" min="1" class="calc-trigger">
              </div>
              <div class="field">
                <label>Display Label</label>
                <input type="text" id="pack_1_label" name="pack_1_label" value="Sleeve of 50 pcs">
              </div>
            </div>
            <div class="pack-price-summary" id="pack1Summary">
              Pack Price: <strong>₹140.00</strong> (MRP ₹225.00)
            </div>
          </div>

          <!-- Pack 2 -->
          <div class="pack-card">
            <div class="pack-badge" style="background:#1E4C3A;">PACK 2 (MASTER CARTON)</div>
            <div class="row row-2" style="margin-top:10px;">
              <div class="field">
                <label>Pieces / Carton</label>
                <input type="number" id="pack_2_qty" name="pack_2_qty" value="500" min="0" class="calc-trigger">
              </div>
              <div class="field">
                <label>Display Label</label>
                <input type="text" id="pack_2_label" name="pack_2_label" value="Master Carton of 500 pcs">
              </div>
            </div>
            <div class="pack-price-summary" id="pack2Summary">
              Carton Price: <strong>₹1,400.00</strong> (MRP ₹2,250.00)
            </div>
          </div>
        </div>
      </div>

      <!-- 3. SEARCH KEYWORDS & DESCRIPTION -->
      <div class="studio-card">
        <div class="studio-card-head">
          <span class="step-num">3</span>
          <div>
            <h3>Discovery & Search Keywords</h3>
            <p class="tiny muted">Hinglish and trade synonyms used by Amazon / Flipkart / Meesho search algorithms</p>
          </div>
        </div>

        <div class="field">
          <label for="search_keywords">Search Keywords & Hinglish Synonyms</label>
          <input 
            type="text" 
            id="search_keywords" 
            name="search_keywords" 
            placeholder="e.g. ripple cup, chai glass, coffee cup, garam chai dona, disposable cup"
          >
          <div class="hint">Comma separated terms. Customers often search trade slang like "dona", "glass", "thali".</div>
        </div>

        <div class="field">
          <label for="short_description">Short Summary</label>
          <input 
            type="text" 
            id="short_description" 
            name="short_description" 
            placeholder="e.g. Triple-layer heat-insulating kraft paper cups for piping hot tea and coffee."
          >
        </div>

        <div class="field">
          <label for="long_description">Detailed Product Description & Specifications</label>
          <textarea 
            id="long_description" 
            name="long_description" 
            rows="4" 
            placeholder="Explain heat resistance, food-grade certification, recommended temperature range, and takeaway suitability..."
          ></textarea>
        </div>
      </div>

    </div>

    <!-- RIGHT SIDEBAR COLUMN (Media, Merchandising & Submit) -->
    <div class="studio-side-col">
      
      <!-- IMAGE UPLOADER -->
      <div class="studio-card">
        <div class="studio-card-head">
          <span class="step-num">3</span>
          <div>
            <h3>Product Media</h3>
            <p class="tiny muted">1:1 Square High-Res Photography</p>
          </div>
        </div>

        <!-- Dropzone File Upload -->
        <div class="studio-upload-box" id="dropzoneBox">
          <input type="file" id="image_file" name="image_file" accept="image/*" class="studio-file-input">
          <div class="upload-drop-content">
            <span class="upload-icon">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/>
              </svg>
            </span>
            <strong>Click to upload local image</strong>
            <span class="tiny muted">Supports WebP, JPG, PNG up to 5MB</span>
          </div>
        </div>

        <!-- Live 1:1 Image Preview Card -->
        <div class="image-live-preview-box" style="margin-top:16px;">
          <div class="tiny bold" style="margin-bottom:6px;">LIVE PREVIEW (1:1 Aspect):</div>
          <div class="preview-img-container">
            <img 
              id="liveImagePreview" 
              src="/assets/images/products/butterpaper.png" 
              alt="Product Preview"
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
            <p class="tiny muted">Badges shown on search & catalog</p>
          </div>
        </div>

        <div class="flags-list">
          <label class="checkbox-row">
            <input type="checkbox" name="is_eco" value="1" checked>
            <div>
              <strong>100% Eco-Friendly</strong>
              <span class="tiny muted">Biodegradable / Compostable tag</span>
            </div>
          </label>

          <label class="checkbox-row">
            <input type="checkbox" name="is_bestseller" value="1" checked>
            <div>
              <strong>Bestseller Badge</strong>
              <span class="tiny muted">Displayed in top demand ribbons</span>
            </div>
          </label>

          <label class="checkbox-row">
            <input type="checkbox" name="is_featured" value="1">
            <div>
              <strong>Featured Listing</strong>
              <span class="tiny muted">Pinned on homepage spotlight</span>
            </div>
          </label>

          <label class="checkbox-row">
            <input type="checkbox" name="is_new_arrival" value="1">
            <div>
              <strong>New Arrival</strong>
              <span class="tiny muted">Tagged in latest launches</span>
            </div>
          </label>
        </div>
      </div>

      <!-- PUBLISH STATUS & SUBMIT -->
      <div class="studio-card studio-publish-card">
        <div class="field">
          <label for="status">Listing Status</label>
          <select id="status" name="status">
            <option value="published">Published (Live on Storefront)</option>
            <option value="draft">Draft (Hidden from Customers)</option>
          </select>
        </div>

        <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top:14px;">
          <span>Publish Listing to Catalog →</span>
        </button>
        <a href="/admin/products" class="btn btn-secondary btn-block" style="margin-top:8px;">Cancel</a>
      </div>

    </div>

  </div>
</form>

<?php $view->stop(); ?>
