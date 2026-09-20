<?php
/**
 * Field Technician Digital Job Card View with Pre-Sign Preview & WhatsApp Sharing
 */
$cleanMobile = preg_replace('/[^0-9]/', '', $call['caller_mobile'] ?? $call['customer_mobile'] ?? '');
if (strlen($cleanMobile) === 10) {
    $cleanMobile = '91' . $cleanMobile;
}

$jobCardUrl = BASE_URL . "/engineer/calls/" . $call['id'] . "/print-jobcard";

$waMessage = "🛠️ *FIELD SERVICE JOB CARD REPORT*\n"
    . "━━━━━━━━━━━━━━━━━━━━\n"
    . "📋 *Report #:* " . ($serviceReport['report_number'] ?? 'DRAFT') . "\n"
    . "🎫 *Ticket #:* " . $call['call_number'] . "\n"
    . "🏢 *Customer:* " . ($call['company_name'] ?? '') . "\n"
    . "📍 *Location:* " . ($call['location_name'] ?? '') . "\n"
    . "💻 *Asset:* " . trim(($call['make'] ?? '') . ' ' . ($call['model'] ?? '')) . " (S/N: " . ($call['serial_number'] ?? 'N/A') . ")\n"
    . "🔧 *Engineer:* " . ($serviceReport['engineer_name'] ?? $call['engineer_name'] ?? 'Rahul Sharma') . "\n"
    . "✅ *Status:* " . ($call['status'] ?? 'RESOLVED') . "\n"
    . "━━━━━━━━━━━━━━━━━━━━\n"
    . "📄 *Digital Job Card & PDF Link:*\n"
    . $jobCardUrl . "\n"
    . "━━━━━━━━━━━━━━━━━━━━\n"
    . "_" . Setting::get('company_name', 'AMC Services') . "_";

$waDirectLink = "https://api.whatsapp.com/send?text=" . urlencode($waMessage) . (!empty($cleanMobile) ? "&phone=" . $cleanMobile : "");
?>

<!-- Back Link & Action Bar -->
<div class="mb-3 d-flex justify-content-between align-items-center">
    <a href="<?= BASE_URL ?>/engineer/calls/<?= $call['id'] ?>" class="text-decoration-none text-muted small fw-bold">
        <i class="bi bi-arrow-left"></i> Back to Task
    </a>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3" onclick="openJobCardPreview()">
            <i class="bi bi-eye-fill me-1"></i> Preview Job Card
        </button>
        <?php if (!empty($serviceReport['signed_at'])): ?>
            <a href="<?= BASE_URL ?>/engineer/calls/<?= $call['id'] ?>/print-jobcard?action=share" target="_blank" class="btn btn-whatsapp btn-sm rounded-pill px-3 fw-bold" style="background-color: #25D366; color: white;">
                <i class="bi bi-whatsapp me-1"></i> WhatsApp PDF
            </a>
            <a href="<?= BASE_URL ?>/engineer/calls/<?= $call['id'] ?>/print-jobcard?action=download" target="_blank" class="btn btn-dark btn-sm rounded-pill px-3 fw-bold">
                <i class="bi bi-file-earmark-pdf-fill text-danger me-1"></i> Download PDF
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
    <div class="card-header bg-dark text-white p-3 d-flex justify-content-between align-items-center">
        <div>
            <h6 class="fw-bold mb-0 text-white"><i class="bi bi-file-earmark-medical-fill text-info me-2"></i>Digital Job Card</h6>
            <span class="text-xs text-info">Call: <?= $call['call_number'] ?></span>
        </div>
        <span class="badge bg-primary"><?= htmlspecialchars($call['call_type'] ?? 'AMC') ?> Service Report</span>
    </div>

    <div class="card-body p-3">
        <form method="POST" action="<?= BASE_URL ?>/engineer/calls/<?= $call['id'] ?>/submit-jobcard" id="jobCardForm">
            <input type="hidden" name="csrf_token" value="<?= Request::csrfToken() ?>">
            <input type="hidden" name="customer_signature_data" id="customer_signature_data">
            <input type="hidden" name="is_draft" id="is_draft_input" value="0">

            <!-- Customer & Asset Summary -->
            <div class="p-3 bg-light rounded-3 mb-3 small">
                <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($call['company_name']) ?></div>
                <div class="text-muted"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($call['location_name']) ?></div>
                <?php if (!empty($call['asset_code'])): ?>
                    <div class="text-muted mt-1">Asset: <strong><?= htmlspecialchars(($call['make'] ?? '') . ' ' . ($call['model'] ?? '')) ?></strong> (S/N: <code><?= htmlspecialchars($call['serial_number'] ?? 'N/A') ?></code>)</div>
                <?php endif; ?>
            </div>

            <!-- Replaced Spares & Parts Consumed (If any) -->
            <?php if (!empty($callSpares)): ?>
                <div class="p-3 bg-warning-subtle border border-warning rounded-3 mb-3">
                    <label class="small fw-bold text-dark d-flex align-items-center mb-2">
                        <i class="bi bi-box-seam-fill text-warning me-1"></i> Replaced Spare Parts & Components:
                    </label>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered bg-white mb-0 text-xs align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Part Description</th>
                                    <th>SKU / Code</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Coverage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($callSpares as $sp): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($sp['spare_name']) ?></strong>
                                            <?php if (!empty($sp['capacity_spec'])): ?>
                                                <span class="text-muted">(<?= htmlspecialchars($sp['capacity_spec']) ?>)</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><code><?= htmlspecialchars($sp['sku']) ?></code></td>
                                        <td class="text-center fw-bold"><?= (int)$sp['requested_qty'] ?> <?= htmlspecialchars($sp['unit'] ?? 'PCS') ?></td>
                                        <td class="text-end">
                                            <?php if (empty($sp['is_chargeable'])): ?>
                                                <span class="badge bg-success-subtle text-success border border-success">Under AMC</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning">Chargeable</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Technician Diagnosis -->
            <div class="mb-3">
                <label class="form-label small fw-bold text-dark">Technical Diagnosis & Observations *</label>
                <textarea name="diagnosis" id="diagnosis" class="form-control" rows="3" required placeholder="Explain root cause of hardware/software fault..."><?= htmlspecialchars($serviceReport['diagnosis'] ?? '') ?></textarea>
            </div>

            <!-- Action Taken -->
            <div class="mb-3">
                <label class="form-label small fw-bold text-dark">Action Taken / Resolution Performed *</label>
                <textarea name="action_taken" id="action_taken" class="form-control" rows="3" required placeholder="Details of repair, component replacement, dust cleaning, OS configuration..."><?= htmlspecialchars($serviceReport['action_taken'] ?? '') ?></textarea>
            </div>

            <!-- Pre-Sign Verification & Preview Alert Bar -->
            <div class="card border-primary border-opacity-25 bg-primary bg-opacity-10 p-3 rounded-4 mb-3">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <div class="fw-bold text-primary text-xs"><i class="bi bi-shield-check me-1"></i> Customer Verification Ready?</div>
                        <div class="text-muted text-xxs">Show draft job card to the customer before taking sign-off.</div>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" onclick="openJobCardPreview()">
                        <i class="bi bi-eye-fill me-1"></i> Preview Job Card
                    </button>
                </div>
            </div>

            <!-- Customer E-Signature Pad Section -->
            <div class="mb-3" id="signatureSection">
                <label class="form-label small fw-bold text-dark">Customer Representative Name *</label>
                <input type="text" name="customer_signed_name" id="customer_signed_name" class="form-control mb-2" required placeholder="Full Name of Signatory" value="<?= htmlspecialchars($serviceReport['customer_signed_name'] ?? $call['caller_name']) ?>">

                <label class="form-label small fw-bold text-dark">Customer Touch E-Signature *</label>
                <div class="signature-container">
                    <canvas id="signature-canvas" class="signature-canvas"></canvas>
                    <div class="signature-actions">
                        <span class="text-xs text-muted d-flex align-items-center"><i class="bi bi-pen me-1"></i> Sign above using finger/stylus</span>
                        <button type="button" class="btn btn-xs btn-outline-danger" id="clear-signature">
                            <i class="bi bi-eraser me-1"></i> Clear
                        </button>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-success btn-touch-action" onclick="setDraftMode(0)">
                    <i class="bi bi-check2-circle"></i> Complete Work & Submit Report
                </button>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm py-2 flex-grow-1" onclick="saveOfflineDraft()">
                        <i class="bi bi-save me-1"></i> Save Draft (Offline)
                    </button>
                    <button type="button" class="btn btn-outline-success btn-sm py-2 px-3 fw-bold" onclick="shareDraftViaWhatsApp()">
                        <i class="bi bi-whatsapp me-1"></i> Share WhatsApp
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Interactive Pre-Sign Job Card Preview Modal -->
<div class="modal fade" id="jobCardPreviewModal" tabindex="-1" aria-labelledby="jobCardPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-dark text-white p-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-text-fill text-info fs-5"></i>
                    <div>
                        <h6 class="modal-title fw-bold mb-0 text-white" id="jobCardPreviewModalLabel">Job Card Draft Preview</h6>
                        <span class="text-xs text-info">Review before customer touch signature</span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3 bg-light">
                <!-- Preview Document Box -->
                <div class="bg-white p-3 rounded-3 shadow-sm border position-relative">
                    <div class="badge bg-warning text-dark border border-warning rounded-pill px-3 py-1 mb-2 text-xs">
                        <i class="bi bi-eye-fill me-1"></i> PREVIEW MODE - PENDING SIGNATURE
                    </div>

                    <!-- Client & Asset Header -->
                    <div class="row g-2 border-bottom pb-2 mb-2 text-xs">
                        <div class="col-7">
                            <span class="text-muted d-block">Customer:</span>
                            <strong class="text-dark fs-6"><?= htmlspecialchars($call['company_name']) ?></strong>
                            <div class="text-muted"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($call['location_name']) ?></div>
                        </div>
                        <div class="col-5 text-end">
                            <span class="text-muted d-block">Ticket #:</span>
                            <strong class="text-primary fs-6"><?= htmlspecialchars($call['call_number']) ?></strong>
                            <div class="text-muted">Type: <?= htmlspecialchars($call['call_type'] ?? 'AMC') ?></div>
                        </div>
                    </div>

                    <!-- Machine Details -->
                    <div class="bg-light p-2 rounded-2 mb-2 text-xs border">
                        <div class="row">
                            <div class="col-6">
                                <span class="text-muted">Asset Code:</span> <strong><?= htmlspecialchars($call['asset_code'] ?? 'N/A') ?></strong>
                            </div>
                            <div class="col-6">
                                <span class="text-muted">Make / Model:</span> <strong><?= htmlspecialchars(($call['make'] ?? '') . ' ' . ($call['model'] ?? '')) ?></strong>
                            </div>
                            <div class="col-12 mt-1">
                                <span class="text-muted">Serial Number:</span> <code><?= htmlspecialchars($call['serial_number'] ?? 'N/A') ?></code>
                            </div>
                        </div>
                    </div>

                    <!-- Problem Reported -->
                    <div class="mb-2 text-xs">
                        <span class="text-muted fw-bold d-block">Reported Issue:</span>
                        <div class="p-2 bg-light rounded text-secondary"><?= htmlspecialchars($call['reported_issue'] ?? $call['problem_description'] ?? 'General complaint') ?></div>
                    </div>

                    <!-- Live Diagnosis -->
                    <div class="mb-2 text-xs">
                        <span class="text-primary fw-bold d-block"><i class="bi bi-search me-1"></i>Technician Diagnosis:</span>
                        <div class="p-2 border rounded bg-white" id="previewDiagnosisText">
                            <em>No diagnosis entered yet</em>
                        </div>
                    </div>

                    <!-- Live Action Taken -->
                    <div class="mb-2 text-xs">
                        <span class="text-success fw-bold d-block"><i class="bi bi-wrench me-1"></i>Action Taken & Resolution:</span>
                        <div class="p-2 border rounded bg-white" id="previewActionText">
                            <em>No action details entered yet</em>
                        </div>
                    </div>

                    <!-- Replaced Spares Table (if any) -->
                    <?php if (!empty($callSpares)): ?>
                        <div class="mb-2 text-xs">
                            <span class="text-warning-emphasis fw-bold d-block"><i class="bi bi-box-seam me-1"></i>Parts Replaced:</span>
                            <table class="table table-sm table-bordered bg-white mb-0 text-xxs">
                                <thead>
                                    <tr class="table-light">
                                        <th>Part Name</th>
                                        <th>Qty</th>
                                        <th>Coverage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($callSpares as $sp): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($sp['spare_name']) ?></td>
                                            <td><?= (int)$sp['requested_qty'] ?> <?= htmlspecialchars($sp['unit'] ?? 'PCS') ?></td>
                                            <td><?= empty($sp['is_chargeable']) ? 'Under AMC' : 'Chargeable' ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <!-- Signatory Info Preview -->
                    <div class="mt-3 pt-2 border-top text-xs">
                        <div class="row">
                            <div class="col-6">
                                <span class="text-muted d-block">Service Engineer:</span>
                                <strong><?= htmlspecialchars($call['engineer_name'] ?? 'Rahul Sharma') ?></strong>
                            </div>
                            <div class="col-6 text-end">
                                <span class="text-muted d-block">Customer Signatory:</span>
                                <strong id="previewSignatoryName"><?= htmlspecialchars($call['caller_name']) ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-white p-2 d-flex justify-content-between">
                <div class="d-flex gap-1">
                    <a href="<?= $jobCardUrl ?>?preview=1" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Open Preview
                    </a>
                    <a href="<?= $jobCardUrl ?>?preview=1&action=share" target="_blank" class="btn btn-sm btn-success rounded-pill px-3 fw-bold">
                        <i class="bi bi-whatsapp me-1"></i> WhatsApp PDF
                    </a>
                </div>
                <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold shadow-sm" data-bs-dismiss="modal" onclick="scrollToSignature()">
                    <i class="bi bi-pen-fill me-1"></i> Proceed to Sign
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let sigPad = null;
let previewModal = null;

document.addEventListener('DOMContentLoaded', () => {
    sigPad = new ElectronicSignaturePad('signature-canvas', 'clear-signature', 'customer_signature_data');

    // Auto-restore draft from IndexedDB if offline
    if (typeof OfflineManager !== 'undefined') {
        OfflineManager.getDraft('<?= $call['id'] ?>').then(draft => {
            if (draft) {
                if (!document.getElementById('diagnosis').value && draft.diagnosis) {
                    document.getElementById('diagnosis').value = draft.diagnosis;
                }
                if (!document.getElementById('action_taken').value && draft.action_taken) {
                    document.getElementById('action_taken').value = draft.action_taken;
                }
            }
        });
    }
});

function openJobCardPreview() {
    const diag = document.getElementById('diagnosis').value.trim() || 'Diagnosis details not yet provided.';
    const act = document.getElementById('action_taken').value.trim() || 'Action taken details not yet provided.';
    const signName = document.getElementById('customer_signed_name').value.trim() || 'Authorized Representative';

    document.getElementById('previewDiagnosisText').innerText = diag;
    document.getElementById('previewActionText').innerText = act;
    document.getElementById('previewSignatoryName').innerText = signName;

    const modalEl = document.getElementById('jobCardPreviewModal');
    if (!previewModal) {
        previewModal = new bootstrap.Modal(modalEl);
    }
    previewModal.show();
}

function scrollToSignature() {
    setTimeout(() => {
        const sigSection = document.getElementById('signatureSection');
        if (sigSection) {
            sigSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }, 300);
}

function shareDraftViaWhatsApp() {
    const text = <?= json_encode($waMessage) ?>;
    const shareUrl = <?= json_encode($jobCardUrl) ?>;

    if (navigator.share) {
        navigator.share({
            title: 'Field Service Job Card - <?= htmlspecialchars($call['call_number']) ?>',
            text: text,
            url: shareUrl
        }).catch(err => {
            console.log('Native share canceled, opening WhatsApp Web/App:', err);
            window.open(<?= json_encode($waDirectLink) ?>, '_blank');
        });
    } else {
        window.open(<?= json_encode($waDirectLink) ?>, '_blank');
    }
}

function setDraftMode(val) {
    document.getElementById('is_draft_input').value = val;
    if (sigPad) {
        sigPad.updateInput();
    }
}

function saveOfflineDraft() {
    const data = {
        diagnosis: document.getElementById('diagnosis').value,
        action_taken: document.getElementById('action_taken').value,
        customer_signed_name: document.getElementById('customer_signed_name').value
    };

    if (typeof OfflineManager !== 'undefined') {
        OfflineManager.saveDraft('<?= $call['id'] ?>', data).then(() => {
            App.toast('Draft saved locally in device memory.', 'success');
        }).catch(err => {
            App.toast('Error saving local draft: ' + err.message, 'danger');
        });
    } else {
        alert('Draft saved in local state.');
    }
}
</script>
