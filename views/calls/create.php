<?php
/**
 * Create Service Ticket / Call View with 5,000+ Asset Fast Search Engine
 */
?>
<style>
.symptom-chip {
    transition: all 0.2s ease;
    cursor: pointer;
    font-size: 0.82rem;
    font-weight: 500;
    padding: 6px 14px;
    border-radius: 50px;
    background: #f1f5f9;
    color: #334155;
    border: 1px solid #e2e8f0;
    user-select: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.symptom-chip:hover {
    background: #e2e8f0;
    color: #0f172a;
    transform: translateY(-1px);
}
.symptom-chip.active {
    background: #0284c7;
    color: #ffffff;
    border-color: #0284c7;
    box-shadow: 0 2px 6px rgba(2, 132, 199, 0.35);
}

.asset-select-card {
    transition: all 0.2s ease;
    cursor: pointer;
    border: 1.5px solid #e2e8f0;
    border-radius: 12px;
    background: #ffffff;
}
.asset-select-card:hover {
    border-color: #2563eb;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
}
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Log Service Call / Breakdown Complaint</h4>
        <p class="text-muted small mb-0">Book ticket for AMC asset or registered client with instant technician assignment.</p>
    </div>
    <a href="<?= BASE_URL ?>/calls" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
        <i class="bi bi-arrow-left me-1"></i> Back to Queue
    </a>
</div>

<div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
    <div class="card-body p-4">
        <form method="POST" action="<?= BASE_URL ?>/calls/create" id="createCallForm">
            <input type="hidden" name="csrf_token" value="<?= Request::csrfToken() ?>">
            <input type="hidden" name="machine_id" id="selectedAdminMachineId" value="<?= htmlspecialchars($selectedMachineId ?? '') ?>">

            <!-- Client Selection Type -->
            <div class="mb-4">
                <div class="btn-group" role="group">
                    <input type="radio" class="btn-check" name="caller_mode" id="mode_registered" value="registered" checked onclick="toggleCallerMode('registered')">
                    <label class="btn btn-outline-primary fw-bold rounded-start-pill px-4" for="mode_registered"><i class="bi bi-buildings me-1"></i> Registered AMC / Corporate Client</label>

                    <input type="radio" class="btn-check" name="caller_mode" id="mode_unregistered" value="unregistered" onclick="toggleCallerMode('unregistered')">
                    <label class="btn btn-outline-primary fw-bold rounded-end-pill px-4" for="mode_unregistered"><i class="bi bi-person-plus me-1"></i> New / Walk-in Caller (Non-AMC Paid)</label>
                </div>
            </div>

            <!-- 1. Registered Client Block -->
            <div id="registered_client_block">
                <h6 class="fw-bold text-uppercase fs-8 text-primary mb-3"><i class="bi bi-building me-1"></i> Client & Asset Identification</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Select Customer *</label>
                        <select name="customer_id" id="callCustomerSelect" class="form-select shadow-sm rounded-3" onchange="onCallCustomerChange(this.value)">
                            <option value="">-- Choose Customer --</option>
                            <?php foreach ($customers as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= ($selectedCustomerId == $c['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['company_name']) ?> (<?= $c['customer_code'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Service Location / Branch *</label>
                        <select name="location_id" id="callLocationSelect" class="form-select shadow-sm rounded-3" onchange="onAdminLocationChange(this.value)" required>
                            <option value="">-- Select Location --</option>
                            <?php foreach ($customerLocations as $loc): ?>
                                <option value="<?= $loc['id'] ?>"><?= htmlspecialchars($loc['location_name']) ?> (<?= $loc['city'] ?? '' ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Instant Search & Asset Selection Box (5,000+ Assets) -->
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label small fw-bold mb-0">
                                <i class="bi bi-pc-display text-primary me-1"></i> Machine / Equipment (5,000+ Assets)
                            </label>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm fw-semibold" onclick="openAdminAssetBrowserModal()">
                                <i class="bi bi-grid-3x3-gap me-1"></i> Browse Assets (5000+)
                            </button>
                        </div>

                        <!-- Selected Asset Highlight Box -->
                        <div id="adminSelectedAssetCard" class="card border-primary border-2 bg-primary bg-opacity-10 rounded-4 p-3 mb-3 shadow-sm" style="display: none;">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 44px; height: 44px; font-size: 20px;">
                                        <i class="bi bi-pc-display" id="adminAssetIcon"></i>
                                    </div>
                                    <div>
                                        <div class="d-flex align-items-center gap-2">
                                            <h6 class="fw-bold text-dark mb-0" id="adminAssetTitle">Dell OptiPlex 7090</h6>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle text-xs">AMC Active</span>
                                        </div>
                                        <div class="text-xs text-muted mt-1">
                                            <span>Code: <strong class="text-primary font-monospace" id="adminAssetCode">AST-2026-000001</strong></span> | 
                                            <span>S/N: <code id="adminAssetSerial">DELL-7090-001</code></span> | 
                                            <span id="adminAssetDept">Finance</span>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 text-xs fw-semibold" onclick="clearAdminSelectedAsset()">
                                    <i class="bi bi-x-circle me-1"></i> Change Asset
                                </button>
                            </div>
                        </div>

                        <!-- Fast Autocomplete Search Box -->
                        <div id="adminAssetSearchBox" class="position-relative">
                            <div class="input-group shadow-sm rounded-3 overflow-hidden mb-2">
                                <span class="input-group-text bg-white text-primary border-end-0"><i class="bi bi-search"></i></span>
                                <input type="text" 
                                       id="adminAssetSearchInput" 
                                       class="form-control border-start-0 ps-0" 
                                       placeholder="Type to search asset by Code, Serial Number, Make/Model, Dept or Employee..."
                                       autocomplete="off">
                                <button class="btn btn-outline-secondary" type="button" id="adminClearSearchBtn" onclick="clearAdminSearchInput()" style="display: none;">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>

                            <!-- Instant Cards Grid for Selected Customer's Assets -->
                            <div id="adminAssetCardsContainer" class="d-flex flex-column gap-2 mb-2" style="max-height: 280px; overflow-y: auto; padding-right: 2px;">
                                <?php if (empty($customerMachines)): ?>
                                    <div class="p-3 text-center text-muted bg-light rounded-3 small">
                                        <i class="bi bi-inbox fs-4 d-block mb-1"></i> No hardware assets found.
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($customerMachines as $m): ?>
                                        <?php
                                            $iconClass = $m['asset_type_icon'] ?? (stripos($m['make'] ?? '', 'printer') !== false ? 'bi-printer' : 'bi-pc-display');
                                            $user = $m['assigned_employee'] ?? 'Shared Team';
                                            $dept = $m['department'] ?? 'General';
                                            $sn = $m['serial_number'] ?? 'N/A';
                                            $code = $m['asset_code'] ?? '';
                                            $title = ($m['make'] ?? '') . ' ' . ($m['model'] ?? '');
                                            $company = $m['company_name'] ?? '';
                                            $jsonMach = htmlspecialchars(json_encode($m), ENT_QUOTES, 'UTF-8');
                                        ?>
                                        <div class="asset-select-card p-2 p-md-3 machine-row-card" 
                                             data-customer-id="<?= $m['customer_id'] ?>" 
                                             data-keywords="<?= strtolower(htmlspecialchars($code . ' ' . $sn . ' ' . ($m['asset_tag'] ?? '') . ' ' . $title . ' ' . $user . ' ' . $dept . ' ' . $company . ' ' . ($m['location_name'] ?? ''))) ?>"
                                             onclick="selectAdminAsset(<?= $jsonMach ?>)">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="d-flex align-items-center gap-2 overflow-hidden">
                                                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px; font-size: 19px;">
                                                        <i class="bi <?= $iconClass ?>"></i>
                                                    </div>
                                                    <div class="text-truncate">
                                                        <div class="d-flex align-items-center gap-2">
                                                            <span class="fw-bold text-dark fs-7 text-truncate"><?= htmlspecialchars($title) ?></span>
                                                            <span class="badge bg-success-subtle text-success text-xxs">AMC Active</span>
                                                        </div>
                                                        <div class="text-xs text-muted">
                                                            <span class="text-primary font-monospace fw-bold"><?= htmlspecialchars($code) ?></span> | 
                                                            <span>S/N: <code><?= htmlspecialchars($sn) ?></code></span>
                                                        </div>
                                                        <div class="text-xs text-secondary text-truncate">
                                                            <span class="badge bg-secondary-subtle text-secondary me-1"><?= htmlspecialchars($company) ?></span>
                                                            <i class="bi bi-person"></i> <?= htmlspecialchars($user) ?> (<?= htmlspecialchars($dept) ?>)
                                                        </div>
                                                    </div>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-1 text-xs fw-bold flex-shrink-0 ms-2 shadow-sm">
                                                    Select
                                                </button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Unregistered Client Block -->
            <div id="unregistered_client_block" style="display: none;">
                <h6 class="fw-bold text-uppercase fs-8 text-warning mb-3"><i class="bi bi-person-plus me-1"></i> New Customer Information</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Company / Caller Organization *</label>
                        <input type="text" name="unregistered_company" class="form-control rounded-3" placeholder="Company Name or Individual">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Service Address *</label>
                        <input type="text" name="unregistered_address" class="form-control rounded-3" placeholder="Floor, Building, Area">
                    </div>
                </div>
            </div>

            <!-- Caller Contact Details -->
            <h6 class="fw-bold text-uppercase fs-8 text-primary mb-3"><i class="bi bi-person-lines-fill me-1"></i> Caller Contact</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Caller Contact Person *</label>
                    <input type="text" name="caller_name" class="form-control rounded-3 shadow-sm" required placeholder="Name of Person Reporting">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Caller Mobile Number *</label>
                    <input type="text" name="caller_mobile" class="form-control rounded-3 shadow-sm" required placeholder="98XXXXXXXX">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Caller Email</label>
                    <input type="email" name="caller_email" class="form-control rounded-3 shadow-sm" placeholder="caller@company.com">
                </div>
            </div>

            <!-- Problem & Ticket Configuration -->
            <h6 class="fw-bold text-uppercase fs-8 text-primary mb-3"><i class="bi bi-tools me-1"></i> Problem Categorization & Priority</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Problem Category *</label>
                    <select name="problem_category_id" class="form-select rounded-3 shadow-sm" required>
                        <option value="">-- Choose Category --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Priority / Urgency Level *</label>
                    <select name="priority" class="form-select rounded-3 shadow-sm" required>
                        <option value="CRITICAL">🔴 Critical (Total Work Stoppage / Server Down - 2h SLA)</option>
                        <option value="HIGH" selected>🟠 High (Major Device Down / Standstill - 4h SLA)</option>
                        <option value="MEDIUM">🟡 Medium (Workstation Glitch / Degraded - 8h SLA)</option>
                        <option value="LOW">🟢 Low (Minor Setup / Routine Check - 24h SLA)</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Assign Engineer (Optional)</label>
                    <select name="assigned_engineer_id" class="form-select rounded-3 shadow-sm">
                        <option value="">-- Auto-Queue Dispatch --</option>
                        <?php foreach ($engineers as $eng): ?>
                            <option value="<?= $eng['id'] ?>">
                                <?= htmlspecialchars($eng['name']) ?> (<?= $eng['active_calls_count'] ?> active jobs)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Quick Symptom Tags -->
                <div class="col-12">
                    <label class="form-label small fw-bold mb-1">Quick Symptoms</label>
                    <div class="d-flex flex-wrap gap-1 mb-2">
                        <span class="symptom-chip" onclick="addAdminSymptom(this, 'No Power / Complete Dead')">⚡ No Power</span>
                        <span class="symptom-chip" onclick="addAdminSymptom(this, 'Blue Screen / OS Boot Crash')">💻 OS / BSOD</span>
                        <span class="symptom-chip" onclick="addAdminSymptom(this, 'Display Lines / Blank Screen')">🖥️ Screen Defect</span>
                        <span class="symptom-chip" onclick="addAdminSymptom(this, 'HDD / SSD Not Detected')">💾 Disk Failure</span>
                        <span class="symptom-chip" onclick="addAdminSymptom(this, 'Printer Paper Jam / Toner Smudge')">🖨️ Printer Jam</span>
                        <span class="symptom-chip" onclick="addAdminSymptom(this, 'LAN / Wi-Fi Disconnected')">🌐 Network Down</span>
                        <span class="symptom-chip" onclick="addAdminSymptom(this, 'Overheating / Fan Abnormal Noise')">🔥 Overheating</span>
                    </div>
                    <label class="form-label small fw-bold">Reported Issue Description *</label>
                    <textarea name="reported_issue" id="adminReportedIssue" class="form-control rounded-3 shadow-sm" rows="3" required placeholder="Describe the symptom, error code, noises, or failure observations..."></textarea>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 border-top pt-3">
                <a href="<?= BASE_URL ?>/calls" class="btn btn-light border rounded-pill px-4">Cancel</a>
                <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow">
                    <i class="bi bi-headset me-1"></i> Register Service Call & Start SLA
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Admin Asset Browser -->
<div class="modal fade" id="adminAssetBrowserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-dark text-white p-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-pc-display-horizontal text-primary fs-5"></i>
                    <div>
                        <h6 class="modal-title fw-bold mb-0 text-white">Asset Directory Browser (5,000+ Assets)</h6>
                        <span class="text-xs text-white-50">Search across client inventory</span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3 bg-light">
                <div class="input-group mb-3 shadow-sm rounded-3 overflow-hidden">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" id="modalAdminAssetSearchInput" class="form-control border-start-0" placeholder="Type Asset Code, S/N, Make, Dept...">
                </div>
                <div id="modalAdminAssetList" class="row g-2"></div>
            </div>
            <div class="modal-footer bg-white p-2 d-flex justify-content-between">
                <span class="text-xs text-muted" id="modalAdminAssetCount">Showing assets</span>
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentAdminCustomerAssets = <?= json_encode($customerMachines ?? []) ?>;
let currentAdminModalAssets = [];

function toggleCallerMode(mode) {
    if (mode === 'registered') {
        document.getElementById('registered_client_block').style.display = 'block';
        document.getElementById('unregistered_client_block').style.display = 'none';
        document.getElementById('callCustomerSelect').required = true;
    } else {
        document.getElementById('registered_client_block').style.display = 'none';
        document.getElementById('unregistered_client_block').style.display = 'block';
        document.getElementById('callCustomerSelect').required = false;
    }
}

function onCallCustomerChange(cid) {
    clearAdminSelectedAsset();
    if (!cid) {
        document.getElementById('adminAssetCardsContainer').innerHTML = '<div class="p-3 text-center text-muted bg-light rounded-3 small"><i class="bi bi-arrow-up-circle me-1 text-primary"></i> Select a customer to view their registered hardware assets.</div>';
        return;
    }

    // 1. Fetch locations dynamically
    fetch(`${App.baseUrl}/ajax/customer-locations?customer_id=${cid}`)
        .then(r => r.json())
        .then(data => {
            const locSelect = document.getElementById('callLocationSelect');
            locSelect.innerHTML = '<option value="">-- Select Location --</option>';
            if (data.locations && data.locations.length > 0) {
                data.locations.forEach(loc => {
                    const opt = document.createElement('option');
                    opt.value = loc.id;
                    opt.textContent = `${loc.location_name} (${loc.city || ''})`;
                    locSelect.appendChild(opt);
                });
                if (data.locations.length === 1) {
                    locSelect.value = data.locations[0].id;
                }
            }
        });

    // 2. Fetch customer assets immediately
    loadAdminCustomerAssets(cid);
}

function onAdminLocationChange(locId) {
    const custId = document.getElementById('callCustomerSelect').value;
    if (custId) {
        loadAdminCustomerAssets(custId, locId);
    }
}

function loadAdminCustomerAssets(custId, locId = '') {
    const container = document.getElementById('adminAssetCardsContainer');
    container.innerHTML = '<div class="text-center py-3 text-muted"><div class="spinner-border spinner-border-sm text-primary me-2"></div>Loading assets...</div>';

    fetch(`${App.baseUrl}/ajax/search-machines?customer_id=${encodeURIComponent(custId)}&location_id=${encodeURIComponent(locId)}&limit=100`)
        .then(r => r.json())
        .then(data => {
            currentAdminCustomerAssets = data.machines || data.results || [];
            renderAdminInlineCards(currentAdminCustomerAssets);
        })
        .catch(err => {
            container.innerHTML = '<div class="alert alert-danger p-2 small">Error loading assets.</div>';
        });
}

function renderAdminInlineCards(machines) {
    const container = document.getElementById('adminAssetCardsContainer');
    if (!machines || machines.length === 0) {
        container.innerHTML = '<div class="card p-3 text-center text-muted small bg-light border-0"><i class="bi bi-inbox fs-4 mb-1"></i>No hardware assets registered for this client.</div>';
        return;
    }

    let html = '';
    machines.forEach(m => {
        const iconClass = m.asset_type_icon || (m.make && m.make.toLowerCase().includes('printer') ? 'bi-printer' : 'bi-pc-display');
        const user = m.assigned_employee || 'Shared Team';
        const dept = m.department || 'General';
        const sn = m.serial_number || 'N/A';
        const code = m.asset_code || '';
        const title = `${m.make} ${m.model}`;

        html += `
            <div class="asset-select-card p-2 p-md-3" onclick='selectAdminAsset(${JSON.stringify(m).replace(/'/g, "&#39;")})'>
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2 overflow-hidden">
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px; font-size: 18px;">
                            <i class="bi ${iconClass}"></i>
                        </div>
                        <div class="text-truncate">
                            <div class="fw-bold text-dark fs-7 text-truncate">${title}</div>
                            <div class="text-xs text-muted">
                                <span class="text-primary font-monospace fw-bold">${code}</span> | 
                                <span>S/N: <code>${sn}</code></span>
                            </div>
                            <div class="text-xs text-secondary">
                                <i class="bi bi-person"></i> ${user} (${dept})
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 text-xs fw-bold flex-shrink-0 ms-2">
                        Select
                    </button>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}

function addAdminSymptom(el, text) {
    const area = document.getElementById('adminReportedIssue');
    if (area.value.trim() === '') {
        area.value = text;
    } else if (!area.value.includes(text)) {
        area.value += '; ' + text;
    }
    el.classList.add('active');
    area.focus();
}

document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('adminAssetSearchInput');
    const modalSearchInput = document.getElementById('modalAdminAssetSearchInput');

    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const q = e.target.value.trim().toLowerCase();
            document.getElementById('adminClearSearchBtn').style.display = q ? 'block' : 'none';

            if (!q) {
                renderAdminInlineCards(currentAdminCustomerAssets);
                return;
            }

            const filtered = currentAdminCustomerAssets.filter(m => {
                const searchStr = `${m.asset_code} ${m.serial_number} ${m.asset_tag || ''} ${m.make} ${m.model} ${m.assigned_employee || ''} ${m.department || ''}`.toLowerCase();
                return searchStr.includes(q);
            });

            if (filtered.length > 0) {
                renderAdminInlineCards(filtered);
            } else {
                clearTimeout(adminSearchDebounceTimer);
                adminSearchDebounceTimer = setTimeout(() => {
                    const custId = document.getElementById('callCustomerSelect').value || '';
                    const locId = document.getElementById('callLocationSelect').value || '';
                    fetch(`${App.baseUrl}/ajax/search-machines?q=${encodeURIComponent(q)}&customer_id=${custId}&location_id=${locId}`)
                        .then(r => r.json())
                        .then(data => {
                            renderAdminInlineCards(data.machines || data.results || []);
                        });
                }, 250);
            }
        });
    }

    if (modalSearchInput) {
        modalSearchInput.addEventListener('input', (e) => {
            const filter = e.target.value.toLowerCase().trim();
            const filtered = currentAdminModalAssets.filter(m => {
                const searchStr = `${m.asset_code} ${m.serial_number} ${m.asset_tag || ''} ${m.make} ${m.model} ${m.assigned_employee || ''} ${m.department || ''}`.toLowerCase();
                return searchStr.includes(filter);
            });
            renderAdminModalList(filtered);
        });
    }

    const preSelectedCust = document.getElementById('callCustomerSelect').value;
    if (preSelectedCust) {
        loadAdminCustomerAssets(preSelectedCust);
    }
});

function openAdminAssetBrowserModal() {
    const modalEl = document.getElementById('adminAssetBrowserModal');
    if (!adminAssetModalInstance) {
        adminAssetModalInstance = new bootstrap.Modal(modalEl);
    }
    adminAssetModalInstance.show();

    const custId = document.getElementById('callCustomerSelect').value || '';
    const locId = document.getElementById('callLocationSelect').value || '';

    fetch(`${App.baseUrl}/ajax/search-machines?customer_id=${custId}&location_id=${locId}&limit=100`)
        .then(r => r.json())
        .then(data => {
            currentAdminModalAssets = data.machines || data.results || [];
            renderAdminModalList(currentAdminModalAssets);
        });
}

function renderAdminModalList(assets) {
    const list = document.getElementById('modalAdminAssetList');
    const countLabel = document.getElementById('modalAdminAssetCount');
    list.innerHTML = '';
    countLabel.innerText = `Found ${assets.length} asset(s)`;

    if (assets.length === 0) {
        list.innerHTML = `<div class="col-12 text-center p-4 text-muted small">No assets found.</div>`;
        return;
    }

    assets.forEach(m => {
        const col = document.createElement('div');
        col.className = 'col-md-6';
        col.innerHTML = `
            <div class="card border-0 shadow-sm rounded-3 p-3 h-100 bg-white">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="badge bg-primary-subtle text-primary fw-bold text-xs">${escapeHtml(m.asset_code)}</span>
                    <span class="badge ${m.active_contract_number ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary'} text-xxs">
                        ${m.active_contract_number ? 'AMC Covered' : 'Active'}
                    </span>
                </div>
                <h6 class="fw-bold text-dark mb-1 text-xs">${escapeHtml(m.make)} ${escapeHtml(m.model)}</h6>
                <div class="text-muted text-xxs mb-2">
                    <div>S/N: <code>${escapeHtml(m.serial_number)}</code></div>
                    <div>Location: ${escapeHtml(m.location_name || '')}</div>
                </div>
                <button type="button" class="btn btn-sm btn-primary rounded-pill w-100 fw-bold py-1 text-xs mt-auto">
                    <i class="bi bi-check2-circle me-1"></i> Select Asset
                </button>
            </div>
        `;
        col.querySelector('button').onclick = () => {
            selectAdminAsset(m);
            if (adminAssetModalInstance) adminAssetModalInstance.hide();
        };
        list.appendChild(col);
    });
}

function selectAdminAsset(m) {
    document.getElementById('selectedAdminMachineId').value = m.id;
    document.getElementById('adminAssetTitle').innerText = `${m.make || ''} ${m.model || ''}`;
    document.getElementById('adminAssetCode').innerText = m.asset_code || 'N/A';
    document.getElementById('adminAssetSerial').innerText = m.serial_number || 'N/A';
    document.getElementById('adminAssetDept').innerText = m.department ? `Dept: ${m.department}` : '';

    if (m.location_id) {
        const locSelect = document.getElementById('callLocationSelect');
        if (locSelect) locSelect.value = m.location_id;
    }

    document.getElementById('adminSelectedAssetCard').style.display = 'block';
    document.getElementById('adminAssetSearchBox').style.display = 'none';
}

function clearAdminSelectedAsset() {
    document.getElementById('selectedAdminMachineId').value = '';
    document.getElementById('adminSelectedAssetCard').style.display = 'none';
    document.getElementById('adminAssetSearchBox').style.display = 'block';
    document.getElementById('adminAssetSearchInput').value = '';
}

function clearAdminSearchInput() {
    document.getElementById('adminAssetSearchInput').value = '';
    document.getElementById('adminClearSearchBtn').style.display = 'none';
    renderAdminInlineCards(currentAdminCustomerAssets);
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
</script>
