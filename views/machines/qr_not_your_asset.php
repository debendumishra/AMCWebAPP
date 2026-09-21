<?php
/**
 * Cross-Tenant Asset Access Restriction View
 */
?>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden text-center p-4">
                <div class="my-3">
                    <div class="rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                        <i class="bi bi-shield-x fs-1"></i>
                    </div>
                </div>

                <div class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1 rounded-pill mx-auto mb-3 fw-bold">
                    <i class="bi bi-lock-fill me-1"></i> Restricted Asset
                </div>

                <h4 class="fw-bold text-dark mb-2">Not Your Hardware Asset</h4>
                <p class="text-muted small mb-4">
                    The scanned asset <code class="text-danger fw-bold bg-light px-2 py-1 rounded"><?= htmlspecialchars($machine['asset_code'] ?? $scannedCode ?? 'Unknown') ?></code> is registered under another customer organization. For data security, you cannot view technical details or raise support complaints on assets that do not belong to your company account.
                </p>

                <div class="d-flex flex-column gap-2 mb-4">
                    <button type="button" class="btn btn-primary rounded-pill fw-semibold shadow-sm py-2" onclick="openQrScannerModal()">
                        <i class="bi bi-camera me-1"></i> Scan Another QR Code
                    </button>
                    <a href="<?= BASE_URL ?>/customer/machines" class="btn btn-outline-dark rounded-pill fw-medium py-2">
                        <i class="bi bi-pc-display me-1"></i> View My Registered Assets
                    </a>
                    <a href="<?= BASE_URL ?>/customer" class="btn btn-light rounded-pill text-muted py-2">
                        <i class="bi bi-house me-1"></i> Return to Dashboard
                    </a>
                </div>

                <div class="border-top pt-3 text-start bg-light rounded-3 p-3">
                    <div class="text-xs fw-bold text-muted text-uppercase mb-1"><i class="bi bi-info-circle me-1 text-primary"></i> Need Help?</div>
                    <p class="text-xs text-muted mb-0">
                        If this hardware belongs to your office but was recently relocated or purchased, please contact our helpdesk coordinator to transfer and map the asset code to your client account.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
