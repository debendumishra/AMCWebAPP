<?php
/**
 * Create Customer View
 */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Create New Customer Master</h4>
        <p class="text-muted small mb-0">Register a new client company with primary head office location.</p>
    </div>
    <a href="<?= BASE_URL ?>/customers" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Customers
    </a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <form method="POST" action="<?= BASE_URL ?>/customers/create">
            <input type="hidden" name="csrf_token" value="<?= Request::csrfToken() ?>">

            <h6 class="fw-bold text-uppercase fs-8 text-primary mb-3"><i class="bi bi-building me-1"></i> Corporate Details</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Company / Organization Name *</label>
                    <input type="text" name="company_name" class="form-control" required placeholder="e.g. Acme Tech Solutions Pvt Ltd">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Customer Type *</label>
                    <select name="customer_type" class="form-select">
                        <option value="AMC">AMC Client</option>
                        <option value="PAID">On-Demand / Paid Call</option>
                        <option value="BOTH">Both (AMC & Chargeable)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">GSTIN Number</label>
                    <input type="text" name="gstin" class="form-control" placeholder="27AAAAA0000A1Z5">
                </div>
            </div>

            <h6 class="fw-bold text-uppercase fs-8 text-primary mb-3"><i class="bi bi-person-lines-fill me-1"></i> Primary Contact Person</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Contact Person Name *</label>
                    <input type="text" name="contact_person" class="form-control" required placeholder="e.g. Vikram Mehta">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Designation</label>
                    <input type="text" name="designation" class="form-control" placeholder="e.g. IT Manager / Admin Head">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Mobile Number *</label>
                    <input type="text" name="mobile" class="form-control" required placeholder="98XXXXXXXX">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Alternate Phone</label>
                    <input type="text" name="alternate_mobile" class="form-control" placeholder="022-XXXXXXXX">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Official Email *</label>
                    <input type="email" name="email" class="form-control" required placeholder="it@company.com">
                </div>
            </div>

            <h6 class="fw-bold text-uppercase fs-8 text-primary mb-3"><i class="bi bi-geo-alt me-1"></i> Primary Head Office Location</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Location Name</label>
                    <input type="text" name="location_name" class="form-control" value="Head Office" required>
                </div>
                <div class="col-md-8">
                    <label class="form-label small fw-bold">Full Address *</label>
                    <input type="text" name="billing_address" class="form-control" required placeholder="Floor, Building, Road, Area">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">City</label>
                    <input type="text" name="city" class="form-control" value="Mumbai" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">State</label>
                    <input type="text" name="state" class="form-control" value="Maharashtra" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">PIN Code</label>
                    <input type="text" name="pincode" class="form-control" value="400001" required>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 border-top pt-3">
                <a href="<?= BASE_URL ?>/customers" class="btn btn-light border">Cancel</a>
                <button type="submit" class="btn btn-primary px-4 fw-bold">Save Customer Profile</button>
            </div>
        </form>
    </div>
</div>
