<?php
/**
 * Register New Machine / IT Asset View
 */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Register New Hardware / IT Asset</h4>
        <p class="text-muted small mb-0">Record full hardware specifications, serial number, user assignment, and generate asset QR tag.</p>
    </div>
    <a href="<?= BASE_URL ?>/machines" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Assets
    </a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <form method="POST" action="<?= BASE_URL ?>/machines/create">
            <input type="hidden" name="csrf_token" value="<?= Request::csrfToken() ?>">

            <h6 class="fw-bold text-uppercase fs-8 text-primary mb-3"><i class="bi bi-building me-1"></i> Customer & Location Mapping</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Select Customer Company *</label>
                    <select name="customer_id" id="customerSelect" class="form-select" required onchange="onCustomerChange(this.value)">
                        <option value="">-- Choose Customer --</option>
                        <?php foreach ($customers as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= ($selectedCustomerId == $c['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['company_name']) ?> (<?= $c['customer_code'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Service Location / Branch *</label>
                    <select name="location_id" id="locationSelect" class="form-select" required>
                        <option value="">-- Select Location --</option>
                        <?php foreach ($locations as $loc): ?>
                            <option value="<?= $loc['id'] ?>"><?= htmlspecialchars($loc['location_name']) ?> (<?= $loc['city'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Asset Category / Type *</label>
                    <select name="asset_type_id" class="form-select" required>
                        <?php foreach ($assetTypes as $at): ?>
                            <option value="<?= $at['id'] ?>"><?= htmlspecialchars($at['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <h6 class="fw-bold text-uppercase fs-8 text-primary mb-3"><i class="bi bi-cpu me-1"></i> Hardware Specifications</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Make / Manufacturer *</label>
                    <input type="text" name="make" class="form-control" required placeholder="e.g. Dell, HP, Lenovo, Cisco">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Model Name / Number *</label>
                    <input type="text" name="model" class="form-control" required placeholder="e.g. OptiPlex 7090, ThinkPad T14">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Serial Number (S/N) *</label>
                    <input type="text" name="serial_number" class="form-control" required placeholder="Unique Serial Number">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Custom Asset Tag / Barcode</label>
                    <input type="text" name="asset_tag" class="form-control" placeholder="e.g. TC-WS-105">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Processor / CPU</label>
                    <input type="text" name="processor" class="form-control" placeholder="e.g. Intel Core i7-12700">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Installed RAM</label>
                    <input type="text" name="ram" class="form-control" placeholder="e.g. 16 GB DDR4">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Storage / Hard Drive</label>
                    <input type="text" name="storage" class="form-control" placeholder="e.g. 512GB NVMe SSD">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Operating System</label>
                    <input type="text" name="operating_system" class="form-control" placeholder="e.g. Windows 11 Pro">
                </div>
            </div>

            <h6 class="fw-bold text-uppercase fs-8 text-primary mb-3"><i class="bi bi-geo-alt me-1"></i> Physical Placement & User Assignment</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Building / Wing</label>
                    <input type="text" name="building" class="form-control" placeholder="e.g. Tower B">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Floor</label>
                    <input type="text" name="floor" class="form-control" placeholder="e.g. 4th Floor">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Department / Bay</label>
                    <input type="text" name="department" class="form-control" placeholder="e.g. Accounts / Trading Bay">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Assigned Employee / User</label>
                    <input type="text" name="assigned_employee" class="form-control" placeholder="e.g. John Doe">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">IP Address</label>
                    <input type="text" name="ip_address" class="form-control" placeholder="192.168.1.100">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">MAC Address</label>
                    <input type="text" name="mac_address" class="form-control" placeholder="00:1B:44:11:3A:B7">
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 border-top pt-3">
                <a href="<?= BASE_URL ?>/machines" class="btn btn-light border">Cancel</a>
                <button type="submit" class="btn btn-primary px-4 fw-bold">
                    <i class="bi bi-qr-code me-1"></i> Register Asset & Generate QR Tag
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function onCustomerChange(customerId) {
    if (!customerId) return;
    window.location.href = `${App.baseUrl}/machines/create?customer_id=${customerId}`;
}
</script>
