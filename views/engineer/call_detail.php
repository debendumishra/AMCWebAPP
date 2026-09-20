<?php
/**
 * Field Engineer Call Detail & Journey Action View
 */
$status = $call['status'];
?>
<!-- Back Button -->
<div class="mb-3">
    <a href="<?= BASE_URL ?>/engineer" class="text-decoration-none text-muted small fw-bold">
        <i class="bi bi-arrow-left"></i> Back to Schedule
    </a>
</div>

<!-- Workflow Stepper -->
<div class="card border-0 shadow-sm rounded-4 p-3 mb-3 bg-white">
    <div class="workflow-stepper">
        <div class="workflow-step <?= in_array($status, ['ASSIGNED', 'ENGINEER_ACCEPTED', 'ON_THE_WAY', 'ARRIVED', 'DIAGNOSIS', 'SPARE_REQUIRED', 'SPARE_APPROVED', 'SPARE_ISSUED', 'REPAIR_IN_PROGRESS', 'RESOLVED', 'CLOSED']) ? 'completed active' : '' ?>">
            <div class="step-circle"><i class="bi bi-check2"></i></div>
            <span>Accept</span>
        </div>
        <div class="workflow-step <?= in_array($status, ['ON_THE_WAY', 'ARRIVED', 'DIAGNOSIS', 'SPARE_REQUIRED', 'SPARE_APPROVED', 'SPARE_ISSUED', 'REPAIR_IN_PROGRESS', 'RESOLVED', 'CLOSED']) ? 'completed active' : '' ?>">
            <div class="step-circle"><i class="bi bi-bicycle"></i></div>
            <span>En Route</span>
        </div>
        <div class="workflow-step <?= in_array($status, ['ARRIVED', 'DIAGNOSIS', 'SPARE_REQUIRED', 'SPARE_APPROVED', 'SPARE_ISSUED', 'REPAIR_IN_PROGRESS', 'RESOLVED', 'CLOSED']) ? 'completed active' : '' ?>">
            <div class="step-circle"><i class="bi bi-geo-alt"></i></div>
            <span>On Site</span>
        </div>
        <div class="workflow-step <?= in_array($status, ['RESOLVED', 'CLOSED']) ? 'completed active' : '' ?>">
            <div class="step-circle"><i class="bi bi-file-earmark-check"></i></div>
            <span>Done</span>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Left Column: Details & Asset Information -->
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 p-3 mb-3 bg-white h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="badge bg-primary fs-7"><?= $call['call_number'] ?></span>
                <span class="badge-status badge-status-<?= strtolower($status) ?>"><?= str_replace('_', ' ', $status) ?></span>
            </div>

            <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($call['company_name']) ?></h5>
            <div class="small text-muted mb-3"><i class="bi bi-geo-alt-fill text-danger me-1"></i><?= htmlspecialchars($call['location_name']) ?> (<?= htmlspecialchars($call['location_address']) ?>)</div>

            <!-- Quick Navigation & Phone Links -->
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <a href="tel:<?= $call['caller_mobile'] ?>" class="btn btn-sm btn-outline-success w-100 py-2 fw-bold">
                        <i class="bi bi-telephone-fill me-1"></i> Call Client
                    </a>
                </div>
                <div class="col-6">
                    <a href="https://maps.google.com/?q=<?= urlencode($call['location_address'] . ', ' . $call['city']) ?>" target="_blank" class="btn btn-sm btn-outline-danger w-100 py-2 fw-bold">
                        <i class="bi bi-cursor-fill me-1"></i> Navigate (Map)
                    </a>
                </div>
            </div>

            <!-- Breakdown Complaint Box -->
            <div class="p-3 bg-light rounded-3 mb-3">
                <label class="text-xs text-uppercase text-muted fw-bold d-block mb-1">Reported Issue:</label>
                <div class="text-dark small fw-semibold"><?= nl2br(htmlspecialchars($call['reported_issue'] ?? $call['problem_description'] ?? 'General hardware repair')) ?></div>
            </div>

            <!-- Machine Specs If Available -->
            <?php if (!empty($call['asset_code'])): ?>
                <div class="p-3 bg-light rounded-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong class="small text-dark"><?= htmlspecialchars($call['make'] . ' ' . $call['model']) ?></strong>
                        <span class="badge bg-dark"><?= $call['asset_code'] ?></span>
                    </div>
                    <div class="text-xs text-muted">S/N: <code><?= htmlspecialchars($call['serial_number']) ?></code></div>
                    <div class="text-xs text-muted">CPU: <?= htmlspecialchars($call['processor'] ?? 'Standard') ?> | RAM: <?= htmlspecialchars($call['ram'] ?? '8GB') ?></div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right Column: Actions & Spares -->
    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 p-3 mb-3 bg-white">
            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-lightning-charge-fill text-primary me-1"></i> Action & Journey Controls</h6>

            <!-- Spares Requisition Status Box (If any requested/issued) -->
            <?php if (!empty($callSpares)): ?>
                <div class="p-3 bg-light-subtle border rounded-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-xs text-uppercase fw-bold text-dark"><i class="bi bi-box-seam text-warning me-1"></i> Requisitioned Spare Parts:</span>
                    </div>
                    <?php foreach ($callSpares as $csp): ?>
                        <div class="d-flex justify-content-between align-items-center small py-1 border-bottom">
                            <div>
                                <strong><?= htmlspecialchars($csp['spare_name']) ?></strong>
                                <div class="text-xs text-muted"><?= $csp['sku'] ?> (Req #<?= $csp['request_number'] ?>)</div>
                            </div>
                            <span class="badge <?= $csp['req_status'] === 'APPROVED' || $csp['req_status'] === 'ISSUED' ? 'bg-success' : 'bg-warning text-dark' ?>">
                                <?= $csp['req_status'] ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Spare Issued Alert -->
            <?php if ($status === 'SPARE_ISSUED'): ?>
                <div class="alert alert-info border-info d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-box-seam-fill fs-4 text-info"></i>
                    <div class="text-xs">
                        <strong>Warehouse Spare Part Issued!</strong><br>
                        Please install the replacement hardware and collect customer e-signature on the Digital Job Card.
                    </div>
                </div>
            <?php endif; ?>

            <!-- Dynamic Journey & Work Actions -->
            <div class="d-grid gap-2">
                <?php if ($status === 'ASSIGNED'): ?>
                    <button type="button" class="btn btn-primary btn-touch-action" onclick="submitJourneyAction('accept')">
                        <i class="bi bi-check-circle-fill me-1"></i> Accept Ticket & Start Journey
                    </button>
                <?php elseif ($status === 'ENGINEER_ACCEPTED'): ?>
                    <button type="button" class="btn btn-info text-white btn-touch-action" onclick="submitJourneyAction('start_journey')">
                        <i class="bi bi-send-fill me-1"></i> Start Journey (On The Way)
                    </button>
                <?php elseif ($status === 'ON_THE_WAY'): ?>
                    <button type="button" class="btn btn-warning text-dark btn-touch-action" onclick="submitJourneyAction('arrive')">
                        <i class="bi bi-geo-alt-fill me-1"></i> Check-in GPS Arrival at Site
                    </button>
                <?php elseif (in_array($status, ['ARRIVED', 'DIAGNOSIS', 'SPARE_REQUIRED', 'SPARE_APPROVED', 'SPARE_ISSUED', 'REPAIR_IN_PROGRESS'])): ?>
                    <a href="<?= BASE_URL ?>/engineer/calls/<?= $call['id'] ?>/jobcard" class="btn btn-success btn-touch-action py-3 fw-bold shadow">
                        <i class="bi bi-pen-fill me-1"></i> Open Digital Job Card & E-Sign
                    </a>
                    <button type="button" class="btn btn-outline-warning btn-sm py-2" data-bs-toggle="modal" data-bs-target="#requestSpareModal">
                        <i class="bi bi-box-seam me-1"></i> Request Additional Spare Part
                    </button>
                <?php else: ?>
                    <div class="alert alert-success small text-center mb-2">
                        <i class="bi bi-check2-all me-1"></i> This job has been successfully completed and e-signed.
                    </div>
                    <a href="<?= BASE_URL ?>/engineer/calls/<?= $call['id'] ?>/print-jobcard" target="_blank" class="btn btn-outline-primary btn-touch-action py-2 fw-bold">
                        <i class="bi bi-printer-fill me-1"></i> Print / View Signed Job Card
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Request Spare Modal -->
<div class="modal fade" id="requestSpareModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <form method="POST" action="<?= BASE_URL ?>/engineer/calls/<?= $call['id'] ?>/request-spare">
                <input type="hidden" name="csrf_token" value="<?= Request::csrfToken() ?>">
                <div class="modal-header bg-dark text-white p-3">
                    <h6 class="modal-title fw-bold"><i class="bi bi-box-seam text-warning me-2"></i>Request Spare Part</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Select Required Spare Item *</label>
                        <select name="spare_item_id" class="form-select" required>
                            <option value="">-- Choose Spare Item --</option>
                            <?php foreach ($spares as $sp): ?>
                                <option value="<?= $sp['id'] ?>">
                                    <?= htmlspecialchars($sp['name']) ?> (<?= $sp['sku'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Quantity Required *</label>
                        <input type="number" name="quantity" class="form-control" value="1" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Fault Diagnosis / Reason</label>
                        <textarea name="remarks" class="form-control" rows="2" placeholder="e.g. Existing HDD has bad sectors and clicking noise..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light p-3">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning fw-bold">Submit Requisition</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Hidden Form for Journey GPS Check-ins -->
<form id="journeyForm" method="POST" action="<?= BASE_URL ?>/engineer/calls/<?= $call['id'] ?>/journey" style="display: none;">
    <input type="hidden" name="csrf_token" value="<?= Request::csrfToken() ?>">
    <input type="hidden" name="action" id="journeyAction">
    <input type="hidden" name="latitude" id="journeyLat">
    <input type="hidden" name="longitude" id="journeyLng">
    <input type="hidden" name="accuracy" id="journeyAcc">
</form>

<script>
function submitJourneyAction(action) {
    document.getElementById('journeyAction').value = action;
    
    // Attempt to capture browser GPS
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                document.getElementById('journeyLat').value = pos.coords.latitude;
                document.getElementById('journeyLng').value = pos.coords.longitude;
                document.getElementById('journeyAcc').value = pos.coords.accuracy;
                document.getElementById('journeyForm').submit();
            },
            (err) => {
                console.warn('GPS capture warning:', err.message);
                document.getElementById('journeyForm').submit();
            },
            { timeout: 5000, enableHighAccuracy: true }
        );
    } else {
        document.getElementById('journeyForm').submit();
    }
}
</script>
