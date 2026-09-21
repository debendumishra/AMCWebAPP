<?php
/**
 * Service Call Overview & Timeline Command View
 */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <div class="d-flex align-items-center gap-2">
            <h4 class="fw-bold mb-0"><?= $call['call_number'] ?></h4>
            <span class="badge bg-light text-dark border"><?= $call['call_type'] ?></span>
            <span class="badge <?= $call['priority'] === 'CRITICAL' ? 'bg-danger' : ($call['priority'] === 'HIGH' ? 'bg-warning text-dark' : 'bg-info-subtle text-info') ?>">
                <?= $call['priority'] ?>
            </span>
            <span class="badge-status badge-status-<?= strtolower($call['status']) ?>">
                <?= str_replace('_', ' ', $call['status']) ?>
            </span>
        </div>
        <p class="text-muted small mb-0 mt-1">
            <i class="bi bi-building"></i> <?= htmlspecialchars($call['company_name']) ?> | 
            <i class="bi bi-clock"></i> Logged: <strong><?= date('d M Y, h:i A', strtotime($call['created_at'])) ?></strong>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/calls/<?= $call['id'] ?>/print-jobcard" target="_blank" class="btn btn-outline-secondary btn-sm" title="Print / Export Digital Job Card">
            <i class="bi bi-printer-fill me-1"></i> Print Job Card
        </a>
        <?php
        $callMobile = preg_replace('/[^0-9]/', '', $call['caller_mobile'] ?? $call['customer_mobile'] ?? '');
        if (strlen($callMobile) === 10) $callMobile = '91' . $callMobile;
        $callWaMsg = "🛠️ Field Service Job Card - Ticket #{$call['call_number']}: " . BASE_URL . "/calls/{$call['id']}/print-jobcard";
        ?>
        <a href="https://api.whatsapp.com/send?text=<?= urlencode($callWaMsg) ?><?= !empty($callMobile) ? '&phone=' . $callMobile : '' ?>" target="_blank" class="btn btn-outline-success btn-sm" title="Share via WhatsApp">
            <i class="bi bi-whatsapp me-1"></i> WhatsApp
        </a>
        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#assignEngineerModal">
            <i class="bi bi-person-plus me-1"></i> <?= empty($call['engineer_name']) ? 'Assign Engineer' : 'Reassign Tech' ?>
        </button>
        <button type="button" class="btn btn-outline-dark btn-sm" data-bs-toggle="modal" data-bs-target="#changeStatusModal">
            <i class="bi bi-arrow-repeat me-1"></i> Update Status
        </button>
    </div>
</div>

<!-- SLA Countdown Banner -->
<?php if ($call['status'] !== 'CLOSED' && $call['status'] !== 'RESOLVED' && !empty($call['sla_resolution_deadline'])): ?>
    <div class="alert alert-light border shadow-sm d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-stopwatch-fill text-primary fs-4"></i>
            <div>
                <strong class="text-dark">SLA Resolution Target:</strong> <?= date('d M Y, h:i A', strtotime($call['sla_resolution_deadline'])) ?>
                <div class="text-xs text-muted">Response Target: <?= date('h:i A', strtotime($call['sla_response_deadline'])) ?></div>
            </div>
        </div>
        <div data-sla-deadline="<?= $call['sla_resolution_deadline'] ?>" class="fs-6">
            <span class="spinner-border spinner-border-sm text-secondary"></span>
        </div>
    </div>
<?php endif; ?>

<div class="row g-4 mb-4">
    <!-- Left Column: Customer, Machine & Problem Info -->
    <div class="col-lg-7">
        <!-- Problem Description Card -->
        <div class="card mb-4">
            <div class="card-header bg-white py-3">
                <span class="fw-bold text-dark"><i class="bi bi-exclamation-octagon text-danger me-2"></i>Reported Breakdown Complaint</span>
            </div>
            <div class="card-body">
                <p class="fs-6 text-dark mb-3"><?= nl2br(htmlspecialchars($call['reported_issue'])) ?></p>
                <div class="row g-2 small text-muted border-top pt-2">
                    <div class="col-6">
                        <strong>Caller:</strong> <?= htmlspecialchars($call['caller_name']) ?>
                    </div>
                    <div class="col-6">
                        <strong>Phone:</strong> <a href="tel:<?= $call['caller_mobile'] ?>"><?= $call['caller_mobile'] ?></a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Machine / Asset Card -->
        <div class="card mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark"><i class="bi bi-pc-display text-primary me-2"></i>Target Hardware Machine</span>
                <?php if (!empty($call['machine_id'])): ?>
                    <a href="<?= BASE_URL ?>/machines/<?= $call['machine_id'] ?>" class="btn btn-xs btn-outline-primary">View Asset</a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (!empty($call['asset_code'])): ?>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="text-muted text-xs text-uppercase fw-bold">Device</label>
                            <div class="fw-bold"><?= htmlspecialchars($call['make'] . ' ' . $call['model']) ?></div>
                        </div>
                        <div class="col-6">
                            <label class="text-muted text-xs text-uppercase fw-bold">Serial Number</label>
                            <div><code><?= htmlspecialchars($call['serial_number']) ?></code></div>
                        </div>
                        <div class="col-6">
                            <label class="text-muted text-xs text-uppercase fw-bold">Asset Tag</label>
                            <div><?= htmlspecialchars($call['asset_tag'] ?? 'N/A') ?></div>
                        </div>
                        <div class="col-6">
                            <label class="text-muted text-xs text-uppercase fw-bold">AMC Status</label>
                            <div>
                                <?php if (!empty($call['contract_number'])): ?>
                                    <span class="badge bg-success-subtle text-success"><?= $call['contract_number'] ?> (Labour: Covered, Spares: <?= $call['is_spares_covered'] ? 'Free' : 'Billed' ?>)</span>
                                <?php else: ?>
                                    <span class="badge bg-warning-subtle text-warning">Non-AMC (Chargeable)</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-muted fst-italic">No specific asset attached to this service ticket.</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Service Report Card (If Generated) -->
        <?php if (!empty($serviceReport)): ?>
            <div class="card mb-4 border-success border-opacity-50">
                <div class="card-header bg-success-subtle py-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-success"><i class="bi bi-file-earmark-check-fill me-2"></i>Signed Digital Job Card (<?= $serviceReport['report_number'] ?>)</span>
                    <span class="badge bg-success">Customer Signed</span>
                </div>
                <div class="card-body">
                    <div class="mb-2"><strong>Diagnosis:</strong> <?= htmlspecialchars($serviceReport['diagnosis']) ?></div>
                    <div class="mb-3"><strong>Action Taken:</strong> <?= htmlspecialchars($serviceReport['action_taken']) ?></div>
                    <?php if (!empty($serviceReport['customer_signature_data'])): ?>
                        <div class="small text-muted mb-1">Customer E-Signature:</div>
                        <div class="p-2 bg-light rounded d-inline-block border">
                            <img src="<?= $serviceReport['customer_signature_data'] ?>" alt="Signature" style="max-height: 60px;">
                        </div>
                        <div class="text-xs text-muted mt-1">Signed by: <strong><?= htmlspecialchars($serviceReport['customer_signed_name'] ?? 'Client Rep') ?></strong> on <?= $serviceReport['signed_at'] ?></div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Right Column: Assigned Tech, Location & Live Timeline -->
    <div class="col-lg-5">
        <!-- Assigned Engineer Box -->
        <div class="card mb-4">
            <div class="card-header bg-white py-3">
                <span class="fw-bold text-dark"><i class="bi bi-person-badge text-primary me-2"></i>Assigned Field Engineer</span>
            </div>
            <div class="card-body">
                <?php if (!empty($call['engineer_name'])): ?>
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-circle bg-primary text-white fw-bold d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <?= strtoupper(substr($call['engineer_name'], 0, 1)) ?>
                        </div>
                        <div>
                            <div class="fw-bold fs-6 text-dark"><?= htmlspecialchars($call['engineer_name']) ?></div>
                            <div class="text-xs text-muted">ID: <?= $call['engineer_code'] ?? 'ENG' ?></div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="tel:<?= $call['engineer_mobile'] ?>" class="btn btn-sm btn-outline-primary flex-grow-1">
                            <i class="bi bi-telephone me-1"></i> <?= $call['engineer_mobile'] ?>
                        </a>
                        <a href="https://wa.me/91<?= $call['engineer_mobile'] ?>?text=Ticket%20<?= $call['call_number'] ?>" target="_blank" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-whatsapp"></i>
                        </a>
                    </div>
                <?php else: ?>
                    <div class="text-center p-3 text-muted">
                        <i class="bi bi-person-x fs-3 text-danger d-block mb-1"></i>
                        No technician assigned yet.
                        <div class="mt-2">
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#assignEngineerModal">Assign Technician</button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Service Location Details -->
        <div class="card mb-4">
            <div class="card-header bg-white py-3">
                <span class="fw-bold text-dark"><i class="bi bi-geo-alt text-danger me-2"></i>Service Site Location</span>
            </div>
            <div class="card-body">
                <div class="fw-bold text-dark"><?= htmlspecialchars($call['location_name']) ?></div>
                <div class="small text-muted mb-2"><?= htmlspecialchars($call['location_address']) ?>, <?= htmlspecialchars($call['city']) ?></div>
                <?php if (!empty($call['loc_lat']) && !empty($call['loc_lng'])): ?>
                    <a href="https://maps.google.com/?q=<?= $call['loc_lat'] ?>,<?= $call['loc_lng'] ?>" target="_blank" class="btn btn-xs btn-outline-danger">
                        <i class="bi bi-map-fill me-1"></i> Google Maps Navigation
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Status History Timeline -->
        <div class="card">
            <div class="card-header bg-white py-3">
                <span class="fw-bold text-dark"><i class="bi bi-clock-history text-primary me-2"></i>Live Journey & Status Timeline</span>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <?php foreach ($timeline as $t): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker <?= $t['new_status'] === 'CLOSED' || $t['new_status'] === 'RESOLVED' ? 'success' : 'primary' ?>"></div>
                            <div class="timeline-title">
                                <span class="badge-status badge-status-<?= strtolower($t['new_status']) ?>"><?= str_replace('_', ' ', $t['new_status']) ?></span>
                            </div>
                            <?php if (!empty($t['remarks'])): ?>
                                <div class="text-xs text-dark mt-1"><?= htmlspecialchars($t['remarks']) ?></div>
                            <?php endif; ?>
                            <?php if (!empty($t['latitude']) && !empty($t['longitude'])): ?>
                                <div class="mt-1">
                                    <a href="https://maps.google.com/?q=<?= $t['latitude'] ?>,<?= $t['longitude'] ?>" target="_blank" class="badge bg-light text-primary border text-decoration-none py-1 px-2" style="font-size: 11px;">
                                        <i class="bi bi-geo-alt-fill text-danger me-1"></i> GPS Check-in: <?= number_format((float)$t['latitude'], 5) ?>, <?= number_format((float)$t['longitude'], 5) ?> (Open Map)
                                    </a>
                                </div>
                            <?php endif; ?>
                            <div class="timeline-time mt-1">
                                <?= date('d M, h:i A', strtotime($t['created_at'])) ?> by <?= htmlspecialchars($t['user_name'] ?? 'System') ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assign Engineer Modal -->
<div class="modal fade" id="assignEngineerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <form method="POST" action="<?= BASE_URL ?>/calls/<?= $call['id'] ?>/assign">
                <input type="hidden" name="csrf_token" value="<?= Request::csrfToken() ?>">
                <div class="modal-header bg-dark text-white p-3">
                    <h6 class="modal-title fw-bold"><i class="bi bi-person-plus text-primary me-2"></i>Assign Technician</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <label class="form-label small fw-bold">Select Field Engineer *</label>
                    <select name="engineer_id" class="form-select" required>
                        <?php foreach ($engineers as $eng): ?>
                            <option value="<?= $eng['id'] ?>" <?= ($call['assigned_engineer_id'] == $eng['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($eng['name']) ?> (<?= $eng['active_calls_count'] ?> active jobs)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="modal-footer bg-light p-3">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Assign Call</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Change Status Modal -->
<div class="modal fade" id="changeStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <form method="POST" action="<?= BASE_URL ?>/calls/<?= $call['id'] ?>/status">
                <input type="hidden" name="csrf_token" value="<?= Request::csrfToken() ?>">
                <div class="modal-header bg-dark text-white p-3">
                    <h6 class="modal-title fw-bold"><i class="bi bi-arrow-repeat text-primary me-2"></i>Update Ticket Status</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">New Status *</label>
                        <select name="status" class="form-select" required>
                            <option value="ACKNOWLEDGED" <?= $call['status'] === 'ACKNOWLEDGED' ? 'selected' : '' ?>>ACKNOWLEDGED</option>
                            <option value="ASSIGNED" <?= $call['status'] === 'ASSIGNED' ? 'selected' : '' ?>>ASSIGNED</option>
                            <option value="ON_THE_WAY" <?= $call['status'] === 'ON_THE_WAY' ? 'selected' : '' ?>>ON THE WAY</option>
                            <option value="ARRIVED" <?= $call['status'] === 'ARRIVED' ? 'selected' : '' ?>>ARRIVED</option>
                            <option value="DIAGNOSIS" <?= $call['status'] === 'DIAGNOSIS' ? 'selected' : '' ?>>DIAGNOSIS</option>
                            <option value="SPARE_REQUIRED" <?= $call['status'] === 'SPARE_REQUIRED' ? 'selected' : '' ?>>SPARE REQUIRED</option>
                            <option value="REPAIR_IN_PROGRESS" <?= $call['status'] === 'REPAIR_IN_PROGRESS' ? 'selected' : '' ?>>REPAIR IN PROGRESS</option>
                            <option value="RESOLVED" <?= $call['status'] === 'RESOLVED' ? 'selected' : '' ?>>RESOLVED</option>
                            <option value="CLOSED" <?= $call['status'] === 'CLOSED' ? 'selected' : '' ?>>CLOSED</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Audit Remarks / Notes</label>
                        <textarea name="remarks" class="form-control" rows="2" placeholder="Explain the reason or activity performed..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light p-3">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Save Status Transition</button>
                </div>
            </form>
        </div>
    </div>
</div>
