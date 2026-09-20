<?php
/**
 * Master Tables Configuration View
 */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Master Tables Configuration</h4>
        <p class="text-muted small mb-0">Manage system lookups: Hardware Asset Types, Problem Categories, Contract Types, and Spare Brands.</p>
    </div>
    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addMasterModal">
        <i class="bi bi-plus-circle me-1"></i> Add Master Item
    </button>
</div>

<div class="row g-4">
    <!-- Asset Types -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-white py-3">
                <span class="fw-bold text-dark"><i class="bi bi-pc-display text-primary me-2"></i>Asset Types (<?= count($assetTypes) ?>)</span>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush small">
                    <?php foreach ($assetTypes as $at): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi <?= $at['icon'] ?? 'bi-pc' ?> me-2 text-primary"></i>
                                <strong><?= htmlspecialchars($at['name']) ?></strong>
                            </div>
                            <span class="badge bg-light text-dark border"><?= $at['category'] ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- Problem Categories -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-white py-3">
                <span class="fw-bold text-dark"><i class="bi bi-exclamation-triangle text-warning me-2"></i>Problem Categories (<?= count($problemCats) ?>)</span>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush small">
                    <?php foreach ($problemCats as $pc): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <strong><?= htmlspecialchars($pc['name']) ?></strong>
                            <span class="badge bg-success-subtle text-success">Active</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- Contract Types -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-white py-3">
                <span class="fw-bold text-dark"><i class="bi bi-shield-check text-success me-2"></i>AMC Contract Coverage Types (<?= count($contractTypes) ?>)</span>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush small">
                    <?php foreach ($contractTypes as $ct): ?>
                        <li class="list-group-item">
                            <div class="fw-bold"><?= htmlspecialchars($ct['name']) ?></div>
                            <div class="text-xs text-muted">Spares: <?= $ct['is_spares_covered'] ? 'Included' : 'Chargeable' ?> | Labour: Included</div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- Spare Brands -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-white py-3">
                <span class="fw-bold text-dark"><i class="bi bi-tags text-info me-2"></i>Spare Brands (<?= count($spareBrands) ?>)</span>
            </div>
            <div class="card-body p-0">
                <div class="p-3 d-flex flex-wrap gap-2">
                    <?php foreach ($spareBrands as $sb): ?>
                        <span class="badge bg-light text-dark border p-2"><?= htmlspecialchars($sb['name']) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Master Modal -->
<div class="modal fade" id="addMasterModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <form method="POST" action="<?= BASE_URL ?>/settings/masters">
                <input type="hidden" name="csrf_token" value="<?= Request::csrfToken() ?>">
                <div class="modal-header bg-dark text-white p-3">
                    <h6 class="modal-title fw-bold"><i class="bi bi-plus-circle text-primary me-2"></i>Add Master Entry</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Master Table Type *</label>
                        <select name="master_type" class="form-select" required>
                            <option value="asset_type">Asset Type</option>
                            <option value="problem_category">Problem Category</option>
                            <option value="spare_brand">Spare Part Brand</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Name / Title *</label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. Laser Scanner or Dell">
                    </div>
                </div>
                <div class="modal-footer bg-light p-3">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Save Master Record</button>
                </div>
            </form>
        </div>
    </div>
</div>
