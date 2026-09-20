<?php
/**
 * Desktop Admin / Support / Call Center Main Layout
 */
$user = Auth::user();
$roleName = Auth::roleName();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Dashboard' ?> | <?= htmlspecialchars(Setting::get('company_name', 'AMC System')) ?></title>
    <meta name="base-url" content="<?= BASE_URL ?>">
    <meta name="csrf-token" content="<?= Request::csrfToken() ?>">
    <link rel="manifest" href="<?= BASE_URL ?>/manifest.json">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/custom.css">
</head>
<body>
<div class="app-wrapper">
    <!-- Sidebar Navigation -->
    <aside class="app-sidebar">
        <div class="sidebar-brand">
            <i class="bi bi-shield-check text-primary fs-3 me-2"></i>
            <div class="text-truncate" style="max-width: 190px;">
                <span class="fw-bold text-white fs-6 tracking-wide d-block text-truncate"><?= htmlspecialchars(Setting::get('company_name', 'AMC System')) ?></span>
            </div>
        </div>

        <div class="overflow-y-auto flex-grow-1 py-2">
            <div class="nav-group-title">Main Operations</div>
            <a href="<?= BASE_URL ?>/dashboard" class="nav-link <?= ($activeMenu ?? '') === 'dashboard' ? 'active' : '' ?>">
                <i class="bi bi-grid-1x2-fill"></i> Operations Dashboard
            </a>
            <a href="<?= BASE_URL ?>/calls" class="nav-link <?= ($activeMenu ?? '') === 'calls' ? 'active' : '' ?>">
                <i class="bi bi-headset"></i> Call Management
            </a>
            <a href="<?= BASE_URL ?>/pm" class="nav-link <?= ($activeMenu ?? '') === 'pm' ? 'active' : '' ?>">
                <i class="bi bi-calendar-check"></i> Preventive Maint. (PM)
            </a>

            <div class="nav-group-title">Asset & Client Hierarchy</div>
            <a href="<?= BASE_URL ?>/customers" class="nav-link <?= ($activeMenu ?? '') === 'customers' ? 'active' : '' ?>">
                <i class="bi bi-buildings"></i> Customers & Locations
            </a>
            <a href="<?= BASE_URL ?>/machines" class="nav-link <?= ($activeMenu ?? '') === 'machines' ? 'active' : '' ?>">
                <i class="bi bi-pc-display"></i> Machine / IT Assets
            </a>
            <a href="<?= BASE_URL ?>/contracts" class="nav-link <?= ($activeMenu ?? '') === 'contracts' ? 'active' : '' ?>">
                <i class="bi bi-file-earmark-text"></i> AMC Contracts
            </a>

            <div class="nav-group-title">Inventory & Logistics</div>
            <a href="<?= BASE_URL ?>/inventory" class="nav-link <?= ($activeMenu ?? '') === 'inventory' ? 'active' : '' ?>">
                <i class="bi bi-boxes"></i> Spare Parts Inventory
            </a>
            <a href="<?= BASE_URL ?>/inventory/requisitions" class="nav-link <?= ($activeMenu ?? '') === 'requisitions' ? 'active' : '' ?>">
                <i class="bi bi-patch-question"></i> Spare Approvals
            </a>
            <a href="<?= BASE_URL ?>/purchases" class="nav-link <?= ($activeMenu ?? '') === 'purchases' ? 'active' : '' ?>">
                <i class="bi bi-cart-check"></i> Purchases & Suppliers
            </a>

            <div class="nav-group-title">Finance & Analytics</div>
            <a href="<?= BASE_URL ?>/billing" class="nav-link <?= ($activeMenu ?? '') === 'billing' ? 'active' : '' ?>">
                <i class="bi bi-receipt"></i> Invoicing & GST
            </a>
            <a href="<?= BASE_URL ?>/reports" class="nav-link <?= ($activeMenu ?? '') === 'reports' ? 'active' : '' ?>">
                <i class="bi bi-bar-chart-line"></i> Analytics & Reports
            </a>

            <?php if (Auth::hasAnyRole([ROLE_SUPER_ADMIN, ROLE_ADMIN])): ?>
            <div class="nav-group-title">System & Security</div>
            <a href="<?= BASE_URL ?>/settings/masters" class="nav-link <?= ($activeMenu ?? '') === 'masters' ? 'active' : '' ?>">
                <i class="bi bi-sliders"></i> Master Tables
            </a>
            <a href="<?= BASE_URL ?>/settings" class="nav-link <?= ($activeMenu ?? '') === 'settings' ? 'active' : '' ?>">
                <i class="bi bi-gear"></i> System Settings
            </a>
            <a href="<?= BASE_URL ?>/settings/audit" class="nav-link <?= ($activeMenu ?? '') === 'audit' ? 'active' : '' ?>">
                <i class="bi bi-journal-text"></i> Audit Trail Log
            </a>
            <?php endif; ?>
        </div>

        <!-- Sidebar User Footer -->
        <div class="p-3 border-top border-secondary border-opacity-25 bg-black bg-opacity-25 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2 overflow-hidden">
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px; flex-shrink: 0;">
                    <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
                </div>
                <div class="text-truncate">
                    <div class="text-white small fw-bold text-truncate"><?= htmlspecialchars($user['name'] ?? '') ?></div>
                    <div class="text-muted text-xs text-truncate"><?= $roleName ?></div>
                </div>
            </div>
            <a href="<?= BASE_URL ?>/logout" class="text-danger fs-5" title="Logout"><i class="bi bi-box-arrow-right"></i></a>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="app-main">
        <!-- Header -->
        <header class="app-header">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm btn-outline-secondary d-lg-none" id="sidebar-toggle">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <button type="button" class="btn btn-light border d-flex align-items-center gap-2 text-muted px-3 py-1 rounded-pill" data-bs-toggle="modal" data-bs-target="#fastSearchModal">
                    <i class="bi bi-search text-primary"></i>
                    <span class="d-none d-md-inline small">Quick Universal Search...</span>
                    <kbd class="bg-white border text-dark ms-2 small d-none d-md-inline">Ctrl+K</kbd>
                </button>
            </div>

            <div class="d-flex align-items-center gap-3">
                <a href="<?= BASE_URL ?>/calls/create" class="btn btn-sm btn-primary d-flex align-items-center gap-1">
                    <i class="bi bi-plus-lg"></i> <span class="d-none d-sm-inline">New Call</span>
                </a>
                <div class="dropdown">
                    <button class="btn btn-light border position-relative rounded-circle p-2" data-bs-toggle="dropdown">
                        <i class="bi bi-bell"></i>
                        <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2" style="width: 280px;">
                        <li class="dropdown-header fw-bold">Live System Alerts</li>
                        <li><a class="dropdown-item small text-wrap py-2" href="<?= BASE_URL ?>/calls"><i class="bi bi-exclamation-circle text-danger me-1"></i> SLA approaching for CALL-2026-000001</a></li>
                        <li><a class="dropdown-item small text-wrap py-2" href="<?= BASE_URL ?>/inventory"><i class="bi bi-box-seam text-warning me-1"></i> Low stock warning for Crucial RAM</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-center small text-primary" href="#">View all notifications</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Dynamic Content Body -->
        <main class="app-content">
            <?php if (!empty($flashSuccess)): ?>
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
                    <i class="bi bi-check-circle-fill fs-5"></i>
                    <div><?= $flashSuccess ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if (!empty($flashError)): ?>
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                    <div><?= $flashError ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?= $content ?? '' ?>
        </main>
    </div>
</div>

<!-- Fast Universal Search Modal (Ctrl+K) -->
<div class="modal fade" id="fastSearchModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
            <div class="modal-header bg-light border-bottom p-3">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-primary fs-5"></i></span>
                    <input type="text" class="form-control border-start-0 fs-5 ps-0" id="fast-search-input" placeholder="Search Customer, Mobile, Serial Number, Asset Tag, Call No..." autocomplete="off">
                </div>
            </div>
            <div class="modal-body p-0 fast-search-results" id="fast-search-results">
                <div class="text-center text-muted p-4">
                    <i class="bi bi-search fs-3 mb-2 d-block text-primary opacity-50"></i>
                    Type at least 2 characters to search across Customers, Serials, Phones, Asset Tags, and Contracts...
                </div>
            </div>
            <div class="modal-footer bg-light p-2 justify-content-between text-muted small">
                <span><kbd>ESC</kbd> to exit</span>
                <span>Sub-second Call Center Universal Search</span>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/app.js"></script>
<script src="<?= BASE_URL ?>/assets/js/fast-search.js"></script>
</body>
</html>
