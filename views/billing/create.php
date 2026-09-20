<?php
/**
 * Create Tax Invoice View (Multi-Item Dynamic Billing)
 */
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 d-flex align-items-center gap-2">
            <span class="p-2 bg-success-subtle text-success rounded-3 fs-5 d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                <i class="bi bi-receipt"></i>
            </span>
            <span>Generate Multi-Item Tax Invoice</span>
        </h4>
        <p class="text-muted small mb-0">Create comprehensive GST tax invoices with multiple line items, service charges, spare parts, and automated tax calculations.</p>
    </div>
    <a href="<?= BASE_URL ?>/billing" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
        <i class="bi bi-arrow-left me-1"></i> Back to Invoices
    </a>
</div>

<form method="POST" action="<?= BASE_URL ?>/billing/create" id="invoiceForm">
    <input type="hidden" name="csrf_token" value="<?= Request::csrfToken() ?>">

    <!-- Section 1: Customer & Invoice Details -->
    <div class="card shadow-sm border-0 rounded-4 mb-4 overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
            <span class="badge bg-primary rounded-circle p-2" style="width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center;">1</span>
            <span class="fw-bold text-dark fs-6">Customer & Invoice Meta</span>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label small fw-bold text-dark">Select Customer / Client <span class="text-danger">*</span></label>
                    <select name="customer_id" id="customerSelect" class="form-select" required>
                        <option value="">-- Choose Customer --</option>
                        <?php foreach ($customers as $c): ?>
                            <option value="<?= $c['id'] ?>" data-gstin="<?= htmlspecialchars($c['gstin'] ?? 'Unregistered') ?>">
                                <?= htmlspecialchars($c['company_name']) ?> (<?= htmlspecialchars($c['customer_code']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold text-dark">Link to AMC Contract (Optional)</label>
                    <select name="contract_id" class="form-select">
                        <option value="">-- None (Standalone / Ad-hoc) --</option>
                        <?php foreach ($contracts as $ct): ?>
                            <option value="<?= $ct['id'] ?>">
                                <?= htmlspecialchars($ct['contract_number']) ?> - <?= htmlspecialchars($ct['title']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-bold text-dark">Invoice Category <span class="text-danger">*</span></label>
                    <select name="invoice_type" class="form-select">
                        <option value="AMC">AMC Annual / Quarterly Contract</option>
                        <option value="PAID_SERVICE">Paid Service / Repair Work</option>
                        <option value="SPARE_SALE">Spare Part Replacement Sale</option>
                        <option value="OTHER">General Consulting / Other</option>
                    </select>
                </div>

                <div class="col-md-3 col-sm-6">
                    <label class="form-label small fw-bold text-dark">Invoice Date <span class="text-danger">*</span></label>
                    <input type="date" name="invoice_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="col-md-3 col-sm-6">
                    <label class="form-label small fw-bold text-dark">Payment Due Date <span class="text-danger">*</span></label>
                    <input type="date" name="due_date" class="form-control" value="<?= date('Y-m-d', strtotime('+15 days')) ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold text-dark">Invoice Terms / Notes</label>
                    <input type="text" name="notes" class="form-control" placeholder="e.g. Payment due within 15 days by NEFT/RTGS or UPI.">
                </div>
            </div>
        </div>
    </div>

    <!-- Section 2: Dynamic Multi-Item Table -->
    <div class="card shadow-sm border-0 rounded-4 mb-4 overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary rounded-circle p-2" style="width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center;">2</span>
                <span class="fw-bold text-dark fs-6">Invoice Line Items & Goods/Services</span>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold" id="addItemBtn">
                    <i class="bi bi-plus-lg me-1"></i> Add Line Item
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="itemsTable">
                <thead class="table-light">
                    <tr>
                        <th style="width: 3%;" class="ps-3 text-center">#</th>
                        <th style="width: 40%;">Description & Presets</th>
                        <th style="width: 12%;">HSN/SAC</th>
                        <th style="width: 10%;">Qty</th>
                        <th style="width: 14%;">Unit Rate (₹)</th>
                        <th style="width: 10%;">GST %</th>
                        <th style="width: 13%;" class="text-end pe-3">Line Total (₹)</th>
                        <th style="width: 4%;"></th>
                    </tr>
                </thead>
                <tbody id="itemsContainer">
                    <!-- Default Row 1 -->
                    <tr class="item-row">
                        <td class="ps-3 text-center text-muted row-index fw-bold">1</td>
                        <td>
                            <input type="text" name="items[0][description]" class="form-control form-control-sm item-desc mb-1" placeholder="e.g. Annual IT Support AMC Q1 for 25 Nodes" required>
                            <div class="d-flex gap-1 flex-wrap">
                                <span class="text-xs text-muted me-1">Quick Presets:</span>
                                <button type="button" class="badge bg-light text-dark border text-decoration-none btn-preset" data-desc="AMC Comprehensive Annual Maintenance Support" data-hsn="9987" data-rate="18" data-price="25000">AMC Plan</button>
                                <button type="button" class="badge bg-light text-dark border text-decoration-none btn-preset" data-desc="On-Site Emergency Hardware Breakdown Service" data-hsn="9987" data-rate="18" data-price="1500">Breakdown Call</button>
                                <button type="button" class="badge bg-light text-dark border text-decoration-none btn-preset" data-desc="Preventive Maintenance Health Checkup & Cleaning" data-hsn="9987" data-rate="18" data-price="2000">PM Visit</button>
                                <?php if (!empty($spares)): ?>
                                    <div class="dropdown d-inline">
                                        <button class="badge bg-primary-subtle text-primary border border-primary text-decoration-none dropdown-toggle border-0" type="button" data-bs-toggle="dropdown">
                                            + From Spare Catalog
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 p-1" style="max-height: 220px; overflow-y: auto; font-size: 12px;">
                                            <?php foreach ($spares as $sp): ?>
                                                <li>
                                                    <a class="dropdown-item py-1 text-truncate spare-item-preset" href="#" 
                                                       data-desc="<?= htmlspecialchars($sp['name'] . ' (' . $sp['sku'] . ')') ?>" 
                                                       data-hsn="<?= htmlspecialchars($sp['hsn_code'] ?? '8473') ?>" 
                                                       data-rate="<?= (float)($sp['gst_rate'] ?? 18) ?>" 
                                                       data-price="<?= (float)($sp['selling_price'] ?? 0) ?>">
                                                        <?= htmlspecialchars($sp['name']) ?> - ₹<?= number_format($sp['selling_price'], 2) ?>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <input type="text" name="items[0][hsn_sac]" class="form-control form-control-sm item-hsn font-monospace" value="9987" placeholder="9987">
                        </td>
                        <td>
                            <input type="number" name="items[0][quantity]" class="form-control form-control-sm item-qty text-center" value="1" min="1" required>
                        </td>
                        <td>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">₹</span>
                                <input type="number" step="0.01" name="items[0][unit_price]" class="form-control item-price" placeholder="0.00" value="0.00" required>
                            </div>
                        </td>
                        <td>
                            <select name="items[0][gst_rate]" class="form-select form-select-sm item-gst">
                                <option value="18" selected>18%</option>
                                <option value="12">12%</option>
                                <option value="5">5%</option>
                                <option value="28">28%</option>
                                <option value="0">0% (Exempt)</option>
                            </select>
                        </td>
                        <td class="text-end pe-3">
                            <span class="fw-bold text-dark item-line-total">₹0.00</span>
                            <div class="text-xs text-muted item-tax-breakdown">CGST: ₹0 | SGST: ₹0</div>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-danger border-0 btn-remove-item disabled" title="Remove Item">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section 3: Summary & Submission -->
    <div class="row g-4 mb-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                <h6 class="fw-bold text-dark mb-3"><i class="bi bi-info-circle text-primary me-2"></i>Taxation Breakdown & GST Note</h6>
                <p class="text-muted small mb-2">
                    For Intra-State supplies within the home state, standard 50-50 CGST and SGST split is automatically applied to each item.
                </p>
                <div class="p-3 bg-light rounded-3 border text-xs text-muted">
                    <div><strong>Standard SAC Codes:</strong> <code>9987</code> (IT Support / AMC / Maintenance Services), <code>9983</code> (IT Infrastructure Consulting)</div>
                    <div class="mt-1"><strong>Standard HSN Codes:</strong> <code>8473</code> (Parts and accessories of computers & workstations)</div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Invoice Financial Summary</h6>
                
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Taxable Subtotal:</span>
                    <strong class="text-dark" id="summarySubtotal">₹0.00</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Central GST (CGST):</span>
                    <span class="text-muted" id="summaryCgst">₹0.00</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">State GST (SGST):</span>
                    <span class="text-muted" id="summarySgst">₹0.00</span>
                </div>
                <div class="d-flex justify-content-between border-top pt-2 mt-2">
                    <span class="fw-bold text-primary fs-6">Grand Total (INR):</span>
                    <strong class="text-primary fs-5" id="summaryGrandTotal">₹0.00</strong>
                </div>

                <div class="d-grid mt-4 gap-2">
                    <button type="submit" class="btn btn-primary btn-lg fw-bold rounded-pill shadow-sm">
                        <i class="bi bi-receipt-cutoff me-1"></i> Generate & Issue Tax Invoice
                    </button>
                    <a href="<?= BASE_URL ?>/billing" class="btn btn-light border rounded-pill">Cancel</a>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var itemsContainer = document.getElementById('itemsContainer');
    var addItemBtn = document.getElementById('addItemBtn');
    var rowIndex = 1;

    // Recalculate full totals
    function recalculateInvoice() {
        var subtotal = 0;
        var totalCgst = 0;
        var totalSgst = 0;

        var rows = document.querySelectorAll('.item-row');
        rows.forEach(function(row, idx) {
            var indexBadge = row.querySelector('.row-index');
            if (indexBadge) indexBadge.textContent = idx + 1;

            var qtyInput = row.querySelector('.item-qty');
            var priceInput = row.querySelector('.item-price');
            var gstSelect = row.querySelector('.item-gst');
            var totalDisplay = row.querySelector('.item-line-total');
            var taxBreakdown = row.querySelector('.item-tax-breakdown');
            var removeBtn = row.querySelector('.btn-remove-item');

            if (removeBtn) {
                if (rows.length > 1) {
                    removeBtn.classList.remove('disabled');
                } else {
                    removeBtn.classList.add('disabled');
                }
            }

            var qty = parseFloat(qtyInput.value) || 0;
            var price = parseFloat(priceInput.value) || 0;
            var gstRate = parseFloat(gstSelect.value) || 0;

            var lineSubtotal = qty * price;
            var halfGst = gstRate / 2;
            var cgst = (lineSubtotal * halfGst) / 100;
            var sgst = (lineSubtotal * halfGst) / 100;
            var lineGrand = lineSubtotal + cgst + sgst;

            subtotal += lineSubtotal;
            totalCgst += cgst;
            totalSgst += sgst;

            if (totalDisplay) {
                totalDisplay.textContent = '₹' + lineGrand.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            }
            if (taxBreakdown) {
                taxBreakdown.textContent = 'CGST: ₹' + cgst.toFixed(2) + ' | SGST: ₹' + sgst.toFixed(2);
            }
        });

        var grandTotal = subtotal + totalCgst + totalSgst;

        document.getElementById('summarySubtotal').textContent = '₹' + subtotal.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('summaryCgst').textContent = '₹' + totalCgst.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('summarySgst').textContent = '₹' + totalSgst.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('summaryGrandTotal').textContent = '₹' + grandTotal.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    // Attach listeners to a row
    function attachRowEvents(row) {
        row.querySelectorAll('.item-qty, .item-price, .item-gst').forEach(function(input) {
            input.addEventListener('input', recalculateInvoice);
            input.addEventListener('change', recalculateInvoice);
        });

        // Preset buttons
        row.querySelectorAll('.btn-preset').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var descInput = row.querySelector('.item-desc');
                var hsnInput = row.querySelector('.item-hsn');
                var priceInput = row.querySelector('.item-price');
                var gstSelect = row.querySelector('.item-gst');

                if (descInput) descInput.value = this.getAttribute('data-desc');
                if (hsnInput) hsnInput.value = this.getAttribute('data-hsn');
                if (priceInput) priceInput.value = this.getAttribute('data-price');
                if (gstSelect) gstSelect.value = this.getAttribute('data-rate');
                recalculateInvoice();
            });
        });

        // Spare item presets
        row.querySelectorAll('.spare-item-preset').forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                var descInput = row.querySelector('.item-desc');
                var hsnInput = row.querySelector('.item-hsn');
                var priceInput = row.querySelector('.item-price');
                var gstSelect = row.querySelector('.item-gst');

                if (descInput) descInput.value = this.getAttribute('data-desc');
                if (hsnInput) hsnInput.value = this.getAttribute('data-hsn');
                if (priceInput) priceInput.value = this.getAttribute('data-price');
                if (gstSelect) gstSelect.value = this.getAttribute('data-rate');
                recalculateInvoice();
            });
        });

        // Remove row button
        var removeBtn = row.querySelector('.btn-remove-item');
        if (removeBtn) {
            removeBtn.addEventListener('click', function() {
                if (document.querySelectorAll('.item-row').length > 1) {
                    row.remove();
                    recalculateInvoice();
                }
            });
        }
    }

    // Initialize first row
    var firstRow = document.querySelector('.item-row');
    if (firstRow) {
        attachRowEvents(firstRow);
    }

    // Add new item row
    if (addItemBtn) {
        addItemBtn.addEventListener('click', function() {
            var index = document.querySelectorAll('.item-row').length;
            var newRow = document.createElement('tr');
            newRow.className = 'item-row';
            newRow.innerHTML = `
                <td class="ps-3 text-center text-muted row-index fw-bold">${index + 1}</td>
                <td>
                    <input type="text" name="items[${index}][description]" class="form-control form-control-sm item-desc mb-1" placeholder="Item / Service description" required>
                    <div class="d-flex gap-1 flex-wrap">
                        <span class="text-xs text-muted me-1">Presets:</span>
                        <button type="button" class="badge bg-light text-dark border text-decoration-none btn-preset" data-desc="AMC Comprehensive Annual Maintenance Support" data-hsn="9987" data-rate="18" data-price="25000">AMC Plan</button>
                        <button type="button" class="badge bg-light text-dark border text-decoration-none btn-preset" data-desc="On-Site Emergency Hardware Breakdown Service" data-hsn="9987" data-rate="18" data-price="1500">Breakdown Call</button>
                        <button type="button" class="badge bg-light text-dark border text-decoration-none btn-preset" data-desc="Preventive Maintenance Health Checkup & Cleaning" data-hsn="9987" data-rate="18" data-price="2000">PM Visit</button>
                    </div>
                </td>
                <td>
                    <input type="text" name="items[${index}][hsn_sac]" class="form-control form-control-sm item-hsn font-monospace" value="9987" placeholder="9987">
                </td>
                <td>
                    <input type="number" name="items[${index}][quantity]" class="form-control form-control-sm item-qty text-center" value="1" min="1" required>
                </td>
                <td>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">₹</span>
                        <input type="number" step="0.01" name="items[${index}][unit_price]" class="form-control item-price" placeholder="0.00" value="0.00" required>
                    </div>
                </td>
                <td>
                    <select name="items[${index}][gst_rate]" class="form-select form-select-sm item-gst">
                        <option value="18" selected>18%</option>
                        <option value="12">12%</option>
                        <option value="5">5%</option>
                        <option value="28">28%</option>
                        <option value="0">0% (Exempt)</option>
                    </select>
                </td>
                <td class="text-end pe-3">
                    <span class="fw-bold text-dark item-line-total">₹0.00</span>
                    <div class="text-xs text-muted item-tax-breakdown">CGST: ₹0 | SGST: ₹0</div>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger border-0 btn-remove-item" title="Remove Item">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            `;

            itemsContainer.appendChild(newRow);
            attachRowEvents(newRow);
            recalculateInvoice();
        });
    }

    recalculateInvoice();
});
</script>

