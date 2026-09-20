<?php
/**
 * Create Purchase Entry View
 */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Record Inward Purchase Entry</h4>
        <p class="text-muted small mb-0">Record vendor purchase invoice and automatically increment warehouse stock ledger.</p>
    </div>
    <a href="<?= BASE_URL ?>/purchases" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Purchases
    </a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <form method="POST" action="<?= BASE_URL ?>/purchases/create">
            <input type="hidden" name="csrf_token" value="<?= Request::csrfToken() ?>">

            <h6 class="fw-bold text-uppercase fs-8 text-primary mb-3"><i class="bi bi-truck me-1"></i> Vendor & Invoice Details</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Select Supplier / Vendor *</label>
                    <select name="supplier_id" class="form-select" required>
                        <option value="">-- Choose Supplier --</option>
                        <?php foreach ($suppliers as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?> (<?= $s['city'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Supplier Invoice Number *</label>
                    <input type="text" name="supplier_invoice_no" class="form-control" required placeholder="e.g. INV-2026/982">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Purchase Date *</label>
                    <input type="date" name="purchase_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
            </div>

            <h6 class="fw-bold text-uppercase fs-8 text-primary mb-3"><i class="bi bi-box-seam me-1"></i> Item & Quantity Inward</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Spare Part Item *</label>
                    <select name="spare_item_id" class="form-select" required>
                        <option value="">-- Choose Item --</option>
                        <?php foreach ($spares as $sp): ?>
                            <option value="<?= $sp['id'] ?>"><?= htmlspecialchars($sp['name']) ?> (<?= $sp['sku'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Inward Quantity *</label>
                    <input type="number" name="quantity" class="form-control" value="10" min="1" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Purchase Rate (Unit) *</label>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" step="0.01" name="rate" class="form-control" required placeholder="0.00">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">GST Rate (%)</label>
                    <input type="number" step="0.01" name="gst_percent" class="form-control" value="18.00">
                </div>
                <div class="col-12">
                    <label class="form-label small fw-bold">Remarks / Reference</label>
                    <input type="text" name="remarks" class="form-control" placeholder="Optional notes regarding delivery batch or warranty">
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 border-top pt-3">
                <a href="<?= BASE_URL ?>/purchases" class="btn btn-light border">Cancel</a>
                <button type="submit" class="btn btn-primary px-4 fw-bold">
                    <i class="bi bi-check2-circle me-1"></i> Save Purchase & Increment Stock
                </button>
            </div>
        </form>
    </div>
</div>
