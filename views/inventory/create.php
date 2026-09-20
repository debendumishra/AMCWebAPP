<?php
/**
 * Create Spare Item View
 */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Add New Spare Part Master</h4>
        <p class="text-muted small mb-0">Define hardware component, brand, specification, pricing, and initial stock.</p>
    </div>
    <a href="<?= BASE_URL ?>/inventory" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Inventory
    </a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <form method="POST" action="<?= BASE_URL ?>/inventory/create">
            <input type="hidden" name="csrf_token" value="<?= Request::csrfToken() ?>">

            <h6 class="fw-bold text-uppercase fs-8 text-primary mb-3"><i class="bi bi-box me-1"></i> Component Information</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Spare Part Name *</label>
                    <input type="text" name="name" class="form-control" required placeholder="e.g. Kingston 16GB DDR4 3200MHz RAM">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">SKU Code *</label>
                    <input type="text" name="sku" class="form-control" required placeholder="e.g. SP-RAM-KIN-16GD4">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">HSN/SAC Code</label>
                    <input type="text" name="hsn_code" class="form-control" value="8473">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Category *</label>
                    <select name="category_id" class="form-select" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Brand / Manufacturer</label>
                    <select name="brand_id" class="form-select">
                        <option value="">-- Generic / None --</option>
                        <?php foreach ($brands as $b): ?>
                            <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Capacity / Specification</label>
                    <input type="text" name="capacity_spec" class="form-control" placeholder="e.g. 16 GB / 1 TB / 550W">
                </div>
            </div>

            <h6 class="fw-bold text-uppercase fs-8 text-primary mb-3"><i class="bi bi-currency-rupee me-1"></i> Pricing & Inventory Control</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Purchase Cost (INR) *</label>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" step="0.01" name="purchase_price" class="form-control" required placeholder="0.00">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Selling / Billable Rate (INR) *</label>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" step="0.01" name="selling_price" class="form-control" required placeholder="0.00">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">GST Rate (%)</label>
                    <input type="number" step="0.01" name="gst_rate" class="form-control" value="18.00">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Initial Opening Stock</label>
                    <input type="number" name="opening_stock" class="form-control" value="10">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Min Reorder Level</label>
                    <input type="number" name="min_stock_level" class="form-control" value="5">
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 border-top pt-3">
                <a href="<?= BASE_URL ?>/inventory" class="btn btn-light border">Cancel</a>
                <button type="submit" class="btn btn-primary px-4 fw-bold">Save Spare Part</button>
            </div>
        </form>
    </div>
</div>
