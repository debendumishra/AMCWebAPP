<?php
/**
 * Suppliers Directory & Creation View
 */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Hardware Suppliers & Vendors</h4>
        <p class="text-muted small mb-0">Manage hardware component distributors, contact details, and GSTIN identification.</p>
    </div>
    <button type="button" class="btn btn-primary btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
        <i class="bi bi-plus-circle me-1"></i> Add Supplier
    </button>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Supplier / Vendor Name</th>
                    <th>Contact Person</th>
                    <th>Phone / Email</th>
                    <th>GSTIN</th>
                    <th>City & State</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($suppliers)): ?>
                    <tr><td colspan="6" class="text-center text-muted p-4">No suppliers registered.</td></tr>
                <?php else: ?>
                    <?php foreach ($suppliers as $s): ?>
                        <tr>
                            <td><strong class="text-dark"><?= htmlspecialchars($s['name']) ?></strong></td>
                            <td><?= htmlspecialchars($s['contact_person'] ?? 'Account Manager') ?></td>
                            <td>
                                <div><a href="tel:<?= $s['mobile'] ?>"><?= $s['mobile'] ?></a></div>
                                <div class="text-xs text-muted"><?= htmlspecialchars($s['email'] ?? '') ?></div>
                            </td>
                            <td><code><?= htmlspecialchars($s['gstin'] ?? 'Unregistered') ?></code></td>
                            <td><?= htmlspecialchars($s['city'] ?? 'Mumbai') ?>, <?= htmlspecialchars($s['state'] ?? 'MH') ?></td>
                            <td><span class="badge bg-success">Active</span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Supplier Modal -->
<div class="modal fade" id="addSupplierModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <form method="POST" action="<?= BASE_URL ?>/purchases/suppliers">
                <input type="hidden" name="csrf_token" value="<?= Request::csrfToken() ?>">
                <div class="modal-header bg-dark text-white p-3">
                    <h6 class="modal-title fw-bold"><i class="bi bi-truck text-primary me-2"></i>Add Hardware Supplier</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Supplier Company Name *</label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. CompTech Hardware Distributors">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Contact Person</label>
                            <input type="text" name="contact_person" class="form-control" placeholder="Sales Executive">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Mobile Phone *</label>
                            <input type="text" name="mobile" class="form-control" required placeholder="98XXXXXXXX">
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">GSTIN</label>
                            <input type="text" name="gstin" class="form-control" placeholder="27AAACA0000A1Z5">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Email</label>
                            <input type="email" name="email" class="form-control" placeholder="sales@vendor.com">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Address *</label>
                        <textarea name="address" class="form-control" rows="2" required placeholder="Street / Area / Market"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light p-3">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Save Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>
