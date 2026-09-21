<?php
/**
 * Unrecognized QR Code / Asset Not Found View
 */
?>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden text-center p-4">
                <div class="my-3">
                    <div class="rounded-circle bg-warning-subtle text-warning d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                        <i class="bi bi-qr-code-scan fs-1"></i>
                    </div>
                </div>

                <h4 class="fw-bold text-dark mb-2">Asset QR Not Recognized</h4>
                <p class="text-muted small mb-4">
                    The scanned QR sticker or code <code class="text-danger fw-semibold bg-light px-2 py-1 rounded"><?= htmlspecialchars($scannedCode ?? 'Unknown') ?></code> is not associated with any active hardware asset in the database.
                </p>

                <div class="d-flex flex-column gap-2 mb-4">
                    <button type="button" class="btn btn-primary btn-lg rounded-pill fw-semibold shadow-sm" onclick="openQrScannerModal()">
                        <i class="bi bi-camera me-1"></i> Scan Another QR Sticker
                    </button>
                    <?php if (Auth::role() === ROLE_ENGINEER): ?>
                        <a href="<?= BASE_URL ?>/engineer/calls/create" class="btn btn-outline-dark rounded-pill fw-medium">
                            <i class="bi bi-plus-circle me-1"></i> Create Call (Select Manually)
                        </a>
                        <a href="<?= BASE_URL ?>/engineer" class="btn btn-light rounded-pill text-muted">
                            <i class="bi bi-house me-1"></i> Return to Dashboard
                        </a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/machines" class="btn btn-outline-dark rounded-pill fw-medium">
                            <i class="bi bi-pc-display me-1"></i> Browse Assets Directory
                        </a>
                        <a href="<?= BASE_URL ?>/dashboard" class="btn btn-light rounded-pill text-muted">
                            <i class="bi bi-house me-1"></i> Return to Dashboard
                        </a>
                    <?php endif; ?>
                </div>

                <div class="border-top pt-3 text-start">
                    <div class="text-xs fw-bold text-muted text-uppercase mb-2"><i class="bi bi-info-circle me-1"></i> Troubleshooting Tips</div>
                    <ul class="text-xs text-muted mb-0 ps-3">
                        <li>Ensure good lighting and hold the camera steady over the QR sticker.</li>
                        <li>Verify if the asset has been registered under <strong>Machines / Assets</strong>.</li>
                        <li>You can also search directly by <strong>Asset Code</strong> (e.g. <code>AST-2026-000001</code>) or <strong>Serial Number</strong>.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
