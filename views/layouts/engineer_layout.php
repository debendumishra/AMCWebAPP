<?php
/**
 * Field Technician Layout (Desktop Top Navbar & Mobile App Shell)
 */
$user = Auth::user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= $pageTitle ?? 'Field Engineer' ?> | <?= htmlspecialchars(Setting::get('company_name', 'Field Service')) ?></title>
    <meta name="base-url" content="<?= BASE_URL ?>">
    <meta name="csrf-token" content="<?= Request::csrfToken() ?>">
    <link rel="manifest" href="<?= BASE_URL ?>/manifest.json">
    <meta name="theme-color" content="#0f172a">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/custom.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/mobile-engineer.css?v=<?= time() ?>">
    <style>
        /* Base Resets & Layout */
        body {
            background-color: #f1f5f9;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            color: #1e293b;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
        }

        /* 1. Desktop Navbar Styling (>= 768px) */
        .engineer-desktop-nav {
            background: #0f172a;
            border-bottom: 1px solid #1e293b;
            padding: 0.65rem 0;
            z-index: 1030;
        }
        .engineer-nav-link {
            color: #94a3b8;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            padding: 0.45rem 0.85rem;
            border-radius: 0.5rem;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            transition: all 0.15s ease-in-out;
            white-space: nowrap;
        }
        .engineer-nav-link:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.08);
        }
        .engineer-nav-link.active {
            color: #ffffff;
            background: #2563eb;
            font-weight: 600;
            box-shadow: 0 2px 6px rgba(37, 99, 235, 0.35);
        }

        /* 2. Responsive Main Content Area */
        .engineer-main-wrapper {
            flex: 1 0 auto;
            width: 100%;
        }

        @media (min-width: 768px) {
            .engineer-content-container {
                max-width: 1320px;
                width: 100%;
                margin: 0 auto;
                padding: 1.75rem 1.5rem 3rem 1.5rem;
            }
            .mobile-app-container {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                background: transparent !important;
            }
            .mobile-top-bar,
            .bottom-nav {
                display: none !important;
            }
        }

        @media (max-width: 767.98px) {
            body {
                background-color: #f8fafc;
            }
            .engineer-content-container {
                max-width: 600px;
                width: 100%;
                margin: 0 auto;
                padding: 0.85rem 0.75rem 85px 0.75rem;
            }
            .mobile-app-container {
                max-width: 600px;
                margin: 0 auto;
                padding-bottom: 75px;
            }
        }

        /* Mobile Header */
        .mobile-top-bar {
            background: #0f172a;
            color: #ffffff;
            padding: 0.75rem 1rem;
            position: sticky;
            top: 0;
            z-index: 1020;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        /* Mobile Bottom Nav */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 64px;
            background: #ffffff;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-around;
            align-items: center;
            z-index: 1030;
            box-shadow: 0 -2px 12px rgba(0, 0, 0, 0.06);
            max-width: 600px;
            margin: 0 auto;
        }
        .bottom-nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #64748b;
            text-decoration: none;
            font-size: 0.7rem;
            font-weight: 600;
            flex: 1;
            height: 100%;
            transition: color 0.15s ease;
        }
        .bottom-nav-item i {
            font-size: 1.25rem;
            margin-bottom: 2px;
        }
        .bottom-nav-item.active {
            color: #2563eb;
        }
        .bottom-nav-raised-btn {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: #2563eb;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
            margin-top: -16px;
            transition: transform 0.15s ease;
        }
        .bottom-nav-raised-btn:active {
            transform: scale(0.92);
        }
    </style>
</head>
<body>

<!-- 1. DESKTOP NAVBAR (Visible on Screen >= 768px) -->
<header class="engineer-desktop-nav sticky-top shadow-sm d-none d-md-block">
    <div class="container-fluid px-3 px-xl-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <!-- Brand & Portal Badge -->
            <div class="d-flex align-items-center gap-2 text-nowrap">
                <a href="<?= BASE_URL ?>/engineer" class="text-decoration-none d-flex align-items-center gap-2">
                    <i class="bi bi-shield-check text-primary fs-4"></i>
                    <span class="fw-bold tracking-wide text-white fs-6"><?= htmlspecialchars(Setting::get('company_name', 'Field Service')) ?></span>
                </a>
                <span class="badge bg-primary-subtle text-info border border-info-subtle rounded-pill text-xxs px-2 py-1">Technician Portal</span>
            </div>
            
            <!-- Navigation Links -->
            <nav class="d-flex align-items-center gap-1">
                <a class="engineer-nav-link <?= ($activeMenu ?? '') === 'dashboard' ? 'active' : '' ?>" href="<?= BASE_URL ?>/engineer">
                    <i class="bi bi-calendar2-check"></i> <span>Today's Schedule</span>
                </a>
                <a class="engineer-nav-link <?= ($activeMenu ?? '') === 'calls' ? 'active' : '' ?>" href="<?= BASE_URL ?>/engineer/calls">
                    <i class="bi bi-list-task"></i> <span>All Tasks</span>
                </a>
                <a class="engineer-nav-link <?= ($activeMenu ?? '') === 'spares' ? 'active' : '' ?>" href="<?= BASE_URL ?>/engineer/spares">
                    <i class="bi bi-box-seam"></i> <span>Spare Parts</span>
                </a>
                <a class="engineer-nav-link <?= ($activeMenu ?? '') === 'history' ? 'active' : '' ?>" href="<?= BASE_URL ?>/engineer/history">
                    <i class="bi bi-clock-history"></i> <span>Service History</span>
                </a>
            </nav>

            <!-- Quick Action Buttons & Profile -->
            <div class="d-flex align-items-center gap-2 text-nowrap">
                <a href="<?= BASE_URL ?>/engineer/calls/create" class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-bold shadow-sm d-inline-flex align-items-center gap-1">
                    <i class="bi bi-plus-circle-fill"></i> <span>Log Complaint</span>
                </a>
                <button type="button" class="btn btn-sm btn-dark border border-secondary rounded-pill px-3 py-1 text-white fw-medium d-inline-flex align-items-center gap-1" onclick="openQrScannerModal()">
                    <i class="bi bi-qr-code-scan text-info"></i> <span>Scan QR</span>
                </button>
                
                <div class="vr bg-secondary mx-1 opacity-50" style="height: 24px;"></div>
                
                <div class="d-flex align-items-center gap-2 ps-1">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 32px; height: 32px; font-size: 0.85rem;">
                        <?= strtoupper(substr($user['name'] ?? 'E', 0, 1)) ?>
                    </div>
                    <div class="lh-1 d-none d-lg-block">
                        <div class="fw-bold text-white text-xs"><?= htmlspecialchars($user['name'] ?? 'Engineer') ?></div>
                        <div class="text-info text-xxs mt-1">Field Tech</div>
                    </div>
                    <a href="<?= BASE_URL ?>/logout" class="btn btn-sm btn-outline-light border-0 text-white-50 p-1 ms-1" title="Logout">
                        <i class="bi bi-box-arrow-right fs-5"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- 2. MOBILE TOP STATUS BAR (Visible only on Mobile < 768px) -->
<div class="mobile-top-bar d-md-none">
    <div class="d-flex align-items-center gap-2">
        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.85rem;">
            <?= strtoupper(substr($user['name'] ?? 'E', 0, 1)) ?>
        </div>
        <div>
            <div class="fw-bold text-white fs-7 text-truncate" style="max-width: 140px;"><?= htmlspecialchars($user['name'] ?? 'Engineer') ?></div>
            <div class="text-xxs text-info"><i class="bi bi-geo-alt-fill me-1"></i>Field Tech Active</div>
        </div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="<?= BASE_URL ?>/engineer/calls/create" class="btn btn-sm btn-primary rounded-pill px-2 py-1 text-xs fw-bold">
            <i class="bi bi-plus-lg me-1"></i> New
        </a>
        <button class="btn btn-sm btn-dark border border-secondary text-white rounded-pill px-2 py-1 text-xs" onclick="openQrScannerModal()">
            <i class="bi bi-qr-code-scan text-info"></i> QR
        </button>
        <a href="<?= BASE_URL ?>/logout" class="text-white-50 fs-5 ps-1" title="Logout"><i class="bi bi-box-arrow-right"></i></a>
    </div>
</div>

<!-- 3. MAIN CONTENT CONTAINER (Responsive for Desktop & Mobile) -->
<main class="engineer-main-wrapper">
    <div class="engineer-content-container">
        <?php if (!empty($flashSuccess)): ?>
            <div class="alert alert-success alert-dismissible fade show small shadow-sm rounded-4 mb-3 border-0 bg-success-subtle text-success-emphasis" role="alert">
                <i class="bi bi-check-circle-fill me-1"></i> <?= $flashSuccess ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (!empty($flashError)): ?>
            <div class="alert alert-danger alert-dismissible fade show small shadow-sm rounded-4 mb-3 border-0 bg-danger-subtle text-danger-emphasis" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= $flashError ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?= $content ?? '' ?>
    </div>
</main>

<!-- 4. MOBILE BOTTOM NAVIGATION (Visible only on Mobile < 768px) -->
<nav class="bottom-nav d-md-none">
    <a href="<?= BASE_URL ?>/engineer" class="bottom-nav-item <?= ($activeMenu ?? '') === 'dashboard' ? 'active' : '' ?>">
        <i class="bi bi-calendar2-check"></i>
        <span>Today</span>
    </a>
    <a href="<?= BASE_URL ?>/engineer/calls" class="bottom-nav-item <?= ($activeMenu ?? '') === 'calls' ? 'active' : '' ?>">
        <i class="bi bi-list-task"></i>
        <span>Tasks</span>
    </a>
    <a href="<?= BASE_URL ?>/engineer/calls/create" class="bottom-nav-item <?= ($activeMenu ?? '') === 'create_call' ? 'active' : '' ?>">
        <div class="bottom-nav-raised-btn">
            <i class="bi bi-plus-lg fs-5"></i>
        </div>
        <span class="mt-1">Log Call</span>
    </a>
    <a href="<?= BASE_URL ?>/engineer/spares" class="bottom-nav-item <?= ($activeMenu ?? '') === 'spares' ? 'active' : '' ?>">
        <i class="bi bi-box-seam"></i>
        <span>Spares</span>
    </a>
    <a href="<?= BASE_URL ?>/engineer/history" class="bottom-nav-item <?= ($activeMenu ?? '') === 'history' ? 'active' : '' ?>">
        <i class="bi bi-clock-history"></i>
        <span>History</span>
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
<script src="<?= BASE_URL ?>/assets/js/signature-pad.js"></script>
<script src="<?= BASE_URL ?>/assets/js/offline-sync.js"></script>
<script src="<?= BASE_URL ?>/assets/js/qr-scanner.js"></script>
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
    if (!cleanToken) return;

    if (activeQrCallback) {
        activeQrCallback(cleanToken, raw);
        return;
    }

    if (typeof window.onQrCodeScanned === 'function') {
        window.onQrCodeScanned(cleanToken, raw);
        return;
    }

    // Default global redirect to asset QR view
    window.location.href = `${App.baseUrl}/machines/qr/${encodeURIComponent(cleanToken)}`;
}
</script>
</body>
</html>
