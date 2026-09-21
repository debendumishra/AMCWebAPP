<?php
/**
 * Customer Self-Service Portal Layout with Desktop Navbar & Mobile Bottom Nav Bar
 */
$user = Auth::user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= $pageTitle ?? 'Customer Portal' ?> | <?= htmlspecialchars(Setting::get('company_name', 'Client Portal')) ?></title>
    <meta name="base-url" content="<?= BASE_URL ?>">
    <meta name="csrf-token" content="<?= Request::csrfToken() ?>">
    <link rel="manifest" href="<?= BASE_URL ?>/manifest.json">
    <meta name="theme-color" content="#0f172a">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/custom.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/mobile-engineer.css">
    <style>
        @media (max-width: 767.98px) {
            body {
                padding-bottom: 75px;
                background-color: #f8fafc;
            }
            .customer-main-content {
                padding: 1rem 0.85rem !important;
            }
        }
        @media (min-width: 768px) {
            .mobile-only-nav {
                display: none !important;
            }
        }
        .bottom-nav-raised-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #2563eb;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
            margin-top: -14px;
            transition: transform 0.15s ease;
        }
        .bottom-nav-raised-btn:active {
            transform: scale(0.92);
        }
    </style>
</head>
<body class="bg-light">

<!-- 1. DESKTOP NAVBAR (Hidden on Mobile) -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm d-none d-md-block">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= BASE_URL ?>/customer">
            <i class="bi bi-shield-check text-primary fs-4"></i>
            <span class="fw-bold tracking-wide"><?= htmlspecialchars(Setting::get('company_name', 'Client Portal')) ?></span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#customerNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="customerNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link <?= ($activeMenu ?? '') === 'dashboard' ? 'active' : '' ?>" href="<?= BASE_URL ?>/customer">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link <?= ($activeMenu ?? '') === 'machines' ? 'active' : '' ?>" href="<?= BASE_URL ?>/customer/machines">My Assets</a></li>
                <li class="nav-item"><a class="nav-link <?= ($activeMenu ?? '') === 'calls' ? 'active' : '' ?>" href="<?= BASE_URL ?>/customer/calls">Service Calls</a></li>
                <li class="nav-item"><a class="nav-link <?= ($activeMenu ?? '') === 'invoices' ? 'active' : '' ?>" href="<?= BASE_URL ?>/customer/invoices">Invoices & AMC</a></li>
            </ul>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3 fw-medium d-inline-flex align-items-center gap-1" onclick="openQrScannerModal()">
                    <i class="bi bi-qr-code-scan"></i> <span>Scan QR</span>
                </button>
                <a href="<?= BASE_URL ?>/customer/calls/create" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold">
                    <i class="bi bi-plus-circle me-1"></i> Raise Complaint
                </a>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-light dropdown-toggle rounded-pill px-3" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($user['name'] ?? 'User') ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
                        <li><span class="dropdown-item-text text-muted small"><i class="bi bi-building me-1"></i> Client Portal</span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- 2. MOBILE TOP STATUS BAR (Visible only on Mobile) -->
<div class="mobile-top-bar mobile-only-nav d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-2">
        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 13px;">
            <?= strtoupper(substr($user['name'] ?? 'C', 0, 1)) ?>
        </div>
        <div>
            <div class="fw-bold fs-7 text-white text-truncate" style="max-width: 140px;"><?= htmlspecialchars($user['name'] ?? 'Client') ?></div>
            <div class="text-xs text-info"><i class="bi bi-patch-check-fill me-1"></i>Customer Portal</div>
        </div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-sm btn-dark border border-secondary text-white rounded-pill px-2 py-1 text-xs" onclick="openQrScannerModal()" title="Scan Asset QR">
            <i class="bi bi-qr-code-scan text-info"></i> QR
        </button>
        <a href="<?= BASE_URL ?>/customer/calls/create" class="btn btn-sm btn-primary rounded-pill px-2 py-1 text-xs fw-bold">
            <i class="bi bi-plus-lg me-1"></i> New
        </a>
        <a href="<?= BASE_URL ?>/logout" class="text-white-50 fs-5" title="Logout"><i class="bi bi-box-arrow-right"></i></a>
    </div>
</div>

<!-- 3. MAIN CONTENT CONTAINER -->
<div class="container py-3 customer-main-content">
    <?php if (!empty($flashSuccess)): ?>
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 small rounded-3" role="alert">
            <i class="bi bi-check-circle-fill fs-5 text-success"></i>
            <div><?= $flashSuccess ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($flashError)): ?>
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 small rounded-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-5 text-danger"></i>
            <div><?= $flashError ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?= $content ?? '' ?>
</div>

<!-- 4. MOBILE BOTTOM ICON NAVIGATION BAR (Visible only on Mobile) -->
<nav class="bottom-nav mobile-only-nav">
    <a href="<?= BASE_URL ?>/customer" class="bottom-nav-item <?= ($activeMenu ?? '') === 'dashboard' ? 'active' : '' ?>">
        <i class="bi bi-grid-1x2-fill"></i>
        <span>Home</span>
    </a>
    <a href="<?= BASE_URL ?>/customer/machines" class="bottom-nav-item <?= ($activeMenu ?? '') === 'machines' ? 'active' : '' ?>">
        <i class="bi bi-pc-display"></i>
        <span>Assets</span>
    </a>
    <a href="<?= BASE_URL ?>/customer/calls/create" class="bottom-nav-item <?= ($activeMenu ?? '') === 'create_call' ? 'active' : '' ?>">
        <div class="bottom-nav-raised-btn">
            <i class="bi bi-plus-lg fs-5"></i>
        </div>
        <span class="text-primary font-bold">New Call</span>
    </a>
    <a href="<?= BASE_URL ?>/customer/calls" class="bottom-nav-item <?= ($activeMenu ?? '') === 'calls' ? 'active' : '' ?>">
        <i class="bi bi-headset"></i>
        <span>Calls</span>
    </a>
    <a href="<?= BASE_URL ?>/customer/invoices" class="bottom-nav-item <?= ($activeMenu ?? '') === 'invoices' ? 'active' : '' ?>">
        <i class="bi bi-receipt"></i>
        <span>Invoices</span>
    </a>
</nav>

<!-- QR Code Modal -->
<div class="modal fade" id="qrScannerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-dark text-white p-3 border-0">
                <h6 class="modal-title mb-0 fw-bold"><i class="bi bi-qr-code-scan text-info me-2"></i>Scan Machine Asset QR</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3 text-center">
                <div class="qr-scanner-box mb-3 position-relative rounded-3 overflow-hidden bg-black" style="min-height: 240px;">
                    <video id="qr-video" class="w-100 h-100 object-fit-cover"></video>
                    <div class="qr-scanner-guide"><div class="qr-scan-line"></div></div>
                </div>
                
                <div id="qrCameraNotice" class="mb-3"></div>

                <p class="text-muted small mb-3">Point camera at the QR sticker on the CPU, monitor, or server chassis.</p>

                <!-- Fallback manual input for damaged stickers or low-light -->
                <div class="border-top pt-3 text-start">
                    <label class="text-xs text-muted fw-bold text-uppercase mb-1">Or Enter Asset Code / S/N Manually</label>
                    <div class="input-group input-group-sm">
                        <input type="text" id="manualQrInput" class="form-control" placeholder="e.g. AST-2026-000001, Serial No..." onkeydown="if(event.key==='Enter') submitManualQrCode();">
                        <button type="button" class="btn btn-dark px-3" onclick="submitManualQrCode()">
                            <i class="bi bi-arrow-right-circle me-1"></i> Go
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/app.js"></script>
<script src="<?= BASE_URL ?>/assets/js/qr-scanner.js?v=<?= time() ?>"></script>
<script>
let qrScannerInstance = null;
let activeQrCallback = null;

function openQrScannerModal(customCallback = null) {
    activeQrCallback = typeof customCallback === 'function' ? customCallback : null;
    const modalEl = document.getElementById('qrScannerModal');
    if (!modalEl) return;
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
    
    // Clear manual input field
    const manualInput = document.getElementById('manualQrInput');
    if (manualInput) manualInput.value = '';

    if (!qrScannerInstance) {
        qrScannerInstance = new MachineQRScanner('qr-video', (cleanToken, raw) => {
            modal.hide();
            dispatchQrResult(cleanToken, raw);
        });
    }
    
    qrScannerInstance.start();

    modalEl.addEventListener('hidden.bs.modal', () => {
        if (qrScannerInstance) qrScannerInstance.stop();
        activeQrCallback = null;
    }, { once: true });
}

function submitManualQrCode() {
    const manualInput = document.getElementById('manualQrInput');
    const val = manualInput ? manualInput.value.trim() : '';
    if (!val) return;
    
    const modalEl = document.getElementById('qrScannerModal');
    if (modalEl) {
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
    }
    
    const cleanToken = MachineQRScanner.extractToken(val);
    dispatchQrResult(cleanToken, val);
}

function dispatchQrResult(cleanToken, raw) {
    const finalToken = MachineQRScanner.extractToken(cleanToken || raw);
    if (!finalToken) return;

    if (activeQrCallback) {
        activeQrCallback(finalToken, raw);
        return;
    }

    if (typeof window.onQrCodeScanned === 'function') {
        window.onQrCodeScanned(finalToken, raw);
        return;
    }

    // Default global redirect to asset QR view
    window.location.href = `${App.baseUrl}/machines/qr/${encodeURIComponent(finalToken)}`;
}
</script>
</body>
</html>
