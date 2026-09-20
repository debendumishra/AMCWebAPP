<?php
/**
 * Field Engineer - Mobile-Optimized Complaint / Service Ticket Creation
 */
$machinesList = $machines ?? [];
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

.mode-card {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    border: 2px solid #e2e8f0;
    border-radius: 14px;
    background: #ffffff;
    padding: 14px;
    position: relative;
}
.mode-card:hover {
    border-color: #94a3b8;
    background: #f8fafc;
}
.mode-card.selected {
    border-color: #2563eb;
    background: #eff6ff;
    box-shadow: 0 4px 14px rgba(37, 99, 235, 0.15);
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
.asset-select-card.selected {
    border-color: #2563eb;
    background: #f0f7ff;
    box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
}

.btn-gradient-primary {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    border: none;
    color: #ffffff;
    font-weight: 600;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    transition: all 0.2s ease;
}
.btn-gradient-primary:hover {
    background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
    color: #ffffff;
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(37, 99, 235, 0.4);
}

.btn-gradient-dark {
    background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
    border: 1px solid #38bdf8;
    color: #ffffff;
    font-weight: 600;
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.25);
    transition: all 0.2s ease;
}
.btn-gradient-dark:hover {
    background: linear-gradient(135deg, #334155 0%, #1e293b 100%);
    color: #38bdf8;
    transform: translateY(-1px);
}
</style>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="fw-bold mb-0 text-dark">Raise Service Complaint</h5>
        <div class="text-xs text-muted">Log ticket on-site or report customer hardware defect</div>
    </div>
    <a href="<?= BASE_URL ?>/engineer" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-medium">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-body p-3 p-md-4">
        <form method="POST" action="<?= BASE_URL ?>/engineer/calls/create" id="engineerRaiseCallForm">
            <input type="hidden" name="csrf_token" value="<?= Request::csrfToken() ?>">
            <input type="hidden" name="machine_id" id="selectedMachineId" value="<?= htmlspecialchars($selectedMachineId ?? '') ?>">

            <!-- 1. Customer Selection -->
            <div class="mb-3">
                <label class="form-label small fw-bold text-dark d-flex justify-content-between align-items-center mb-1">
                    <span><i class="bi bi-building text-primary me-1"></i> Select Customer / Client *</span>
                    <span class="text-xs text-muted">Corporate & AMC Clients</span>
                </label>
                <select name="customer_id" id="engineerCustomerSelect" class="form-select form-select-lg fs-6 rounded-3 shadow-sm border-primary border-opacity-25" required onchange="onEngineerCustomerChange(this.value)">
                    <option value="">-- Choose Customer (or pick asset below) --</option>
                    <?php foreach ($customers as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ($selectedCustomerId == $c['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['company_name']) ?> (<?= $c['customer_code'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Service Location / Branch -->
            <div class="mb-3" id="locationSelectContainer" style="<?= empty($customerLocations) ? 'display:none;' : '' ?>">
                <label class="form-label small fw-bold text-dark mb-1">
                    <i class="bi bi-geo-alt-fill text-danger me-1"></i> Service Location / Branch *
                </label>
                <select name="location_id" id="engineerLocationSelect" class="form-select rounded-3 shadow-sm">
                    <option value="">-- Select Location --</option>
                    <?php foreach ($customerLocations as $loc): ?>
                        <option value="<?= $loc['id'] ?>"><?= htmlspecialchars($loc['location_name']) ?> (<?= htmlspecialchars($loc['city'] ?? '') ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- 2. Hardware Asset Selection Section -->
            <div class="mb-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                    <label class="form-label small fw-bold text-dark mb-0">
                        <i class="bi bi-pc-display text-primary me-1"></i> Select Hardware Asset / Machine
                    </label>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-gradient-dark rounded-pill px-3" onclick="openQrScannerModal()">
                            <i class="bi bi-qr-code-scan me-1 text-info"></i> Scan QR
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold shadow-sm" onclick="openAssetBrowserModal()">
                            <i class="bi bi-grid-3x3-gap me-1"></i> Browse (5000+)
                        </button>
                    </div>
                </div>

                <!-- Selected Asset Highlight Box (When chosen) -->
                <div id="selectedAssetCard" class="card border-primary border-2 bg-primary bg-opacity-10 rounded-4 p-3 mb-3 shadow-sm" style="<?= empty($selectedMachine) ? 'display: none;' : '' ?>">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 44px; height: 44px; font-size: 20px;">
                                <i class="bi bi-pc-display" id="assetIcon"></i>
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-2">
                                    <h6 class="fw-bold text-dark mb-0" id="assetTitle"><?= htmlspecialchars(($selectedMachine['make'] ?? '') . ' ' . ($selectedMachine['model'] ?? '')) ?></h6>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle text-xs">AMC Active</span>
                                </div>
                                <div class="text-xs text-muted mt-1">
                                    <span>Code: <strong class="text-primary font-monospace" id="assetCode"><?= htmlspecialchars($selectedMachine['asset_code'] ?? '') ?></strong></span> | 
                                    <span>S/N: <code id="assetSerial"><?= htmlspecialchars($selectedMachine['serial_number'] ?? '') ?></code></span>
                                </div>
                                <div class="text-xs text-secondary mt-1">
                                    <i class="bi bi-person"></i> <span id="assetUser" class="fw-semibold text-dark"><?= htmlspecialchars($selectedMachine['assigned_employee'] ?? 'Shared / Common') ?></span>
                                    (<span id="assetDept"><?= htmlspecialchars($selectedMachine['department'] ?? 'General') ?></span>)
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 text-xs fw-semibold flex-shrink-0" onclick="clearSelectedAsset()">
                            <i class="bi bi-x-circle me-1"></i> Change Asset
                        </button>
                    </div>
                </div>

                <!-- Asset Search & Visible Cards Area -->
                <div id="assetSelectorSection" style="<?= !empty($selectedMachine) ? 'display: none;' : '' ?>">
                    <!-- Live Search Input -->
                    <div class="position-relative mb-2">
                        <div class="input-group shadow-sm rounded-3 overflow-hidden">
                            <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-search text-primary"></i></span>
                            <input type="text" 
                                   id="assetSearchInput" 
                                   class="form-control border-start-0 ps-0" 
                                   placeholder="Filter by S/N, Model, Asset Code, Client, or Staff..."
                                   autocomplete="off">
                            <button class="btn btn-outline-secondary" type="button" id="clearAssetSearchBtn" style="display:none;" onclick="resetSearchInput()">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Instant Visible Cards Grid for Assets (Loaded Directly by PHP) -->
                    <div id="assetCardsContainer" class="d-flex flex-column gap-2 mb-2" style="max-height: 280px; overflow-y: auto; padding-right: 2px;">
                        <?php if (empty($machinesList)): ?>
                            <div class="p-3 text-center text-muted bg-light rounded-3 small">
                                <i class="bi bi-inbox fs-4 d-block mb-1"></i> No hardware assets registered.
                            </div>
                        <?php else: ?>
                            <?php foreach ($machinesList as $m): ?>
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
                                     onclick="selectAsset(<?= $jsonMach ?>)">
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

                    <!-- General / Non-Asset Option -->
                    <div class="card bg-light border-dashed rounded-3 p-2 text-center" style="border: 1px dashed #cbd5e1;">
                        <button type="button" class="btn btn-link btn-sm text-decoration-none text-secondary p-0 fw-medium" onclick="selectNoAsset()">
                            <i class="bi bi-slash-circle me-1"></i> No Specific Asset / General Facility or Network Issue
                        </button>
                    </div>
                </div>
            </div>

            <!-- 3. Call Type & Priority -->
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label class="form-label small fw-bold text-dark">Call Type</label>
                    <select name="call_type" class="form-select rounded-3 shadow-sm">
                        <option value="BREAKDOWN" selected>Breakdown / Repair</option>
                        <option value="PM">Preventive Maintenance (PM)</option>
                        <option value="INSTALLATION">Installation / Setup</option>
                        <option value="OS_SOFTWARE">OS / Software Issue</option>
                        <option value="NETWORK">Network / Internet</option>
                        <option value="SPARE_REPLACEMENT">Spare Replacement</option>
                        <option value="INSPECTION">Inspection / Audit</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label small fw-bold text-dark">Priority</label>
                    <select name="priority" class="form-select rounded-3 shadow-sm">
                        <option value="LOW">🟢 Low (Routine)</option>
                        <option value="MEDIUM" selected>🟡 Medium (Standard)</option>
                        <option value="HIGH">🟠 High (Urgent)</option>
                        <option value="CRITICAL">🔴 Critical (Total Down)</option>
                    </select>
                </div>
            </div>

            <!-- 4. Contact Person at Site -->
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label class="form-label small fw-bold text-dark">Site Contact Name</label>
                    <input type="text" name="caller_name" class="form-control rounded-3 shadow-sm" placeholder="e.g. IT Staff / User Name">
                </div>
                <div class="col-6">
                    <label class="form-label small fw-bold text-dark">Contact Phone</label>
                    <input type="text" name="caller_mobile" class="form-control rounded-3 shadow-sm" placeholder="e.g. 9876543210">
                </div>
            </div>

            <!-- 5. Defect Description & Quick Symptom Pills -->
            <div class="mb-4">
                <label class="form-label small fw-bold text-dark mb-1">
                    <i class="bi bi-exclamation-octagon-fill text-danger me-1"></i> Problem / Defect Description *
                </label>
                
                <!-- Quick Symptom Tags -->
                <div class="d-flex flex-wrap gap-1 mb-2">
                    <span class="symptom-chip" onclick="addSymptom(this, 'No Power / Complete Dead')">⚡ No Power</span>
                    <span class="symptom-chip" onclick="addSymptom(this, 'Blue Screen / OS Boot Crash')">💻 OS / BSOD</span>
                    <span class="symptom-chip" onclick="addSymptom(this, 'Display Lines / Blank Screen')">🖥️ Screen Defect</span>
                    <span class="symptom-chip" onclick="addSymptom(this, 'HDD / SSD Not Detected')">💾 Disk Failure</span>
                    <span class="symptom-chip" onclick="addSymptom(this, 'Printer Paper Jam / Toner Smudge')">🖨️ Printer Jam</span>
                    <span class="symptom-chip" onclick="addSymptom(this, 'LAN / Wi-Fi Disconnected')">🌐 Network Down</span>
                    <span class="symptom-chip" onclick="addSymptom(this, 'Overheating / Fan Abnormal Noise')">🔥 Overheating</span>
                    <span class="symptom-chip" onclick="addSymptom(this, 'Keyboard / Mouse Not Responding')">⌨️ Peripheral</span>
                </div>

                <textarea name="reported_issue" id="reportedIssueText" rows="3" class="form-control rounded-3 shadow-sm" placeholder="Detail the exact issue, error code, or defect observed on site..." required></textarea>
            </div>

            <!-- 6. Ticket Assignment & Execution Mode (Interactive Visual Cards) -->
            <div class="mb-4">
                <label class="form-label small fw-bold text-dark mb-2">
                    <i class="bi bi-lightning-charge-fill text-warning me-1"></i> Execution & Assignment Mode
                </label>
                <input type="hidden" name="assign_mode" id="assignModeInput" value="self_onsite">

                <div class="d-flex flex-column gap-2">
                    <!-- Mode 1: On Site Diagnosis -->
                    <div class="mode-card selected" id="card_mode_onsite" onclick="setExecutionMode('self_onsite')">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 38px; height: 38px;">
                                    <i class="bi bi-tools fs-5"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">⚡ I am on site — Start Diagnosis & Job Card Now</div>
                                    <div class="text-xs text-muted">Directly self-assigns to you with status <strong>DIAGNOSIS</strong> to immediately open digital Job Card.</div>
                                </div>
                            </div>
                            <i class="bi bi-check-circle-fill text-primary fs-5 mode-check" id="check_mode_onsite"></i>
                        </div>
                    </div>

                    <!-- Mode 2: Self-Assign -->
                    <div class="mode-card" id="card_mode_assigned" onclick="setExecutionMode('self_assigned')">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 38px; height: 38px;">
                                    <i class="bi bi-calendar-check fs-5"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">🚗 Self-Assign (Will visit later / In schedule)</div>
                                    <div class="text-xs text-muted">Adds call to your schedule as <strong>ASSIGNED</strong> for upcoming site travel.</div>
                                </div>
                            </div>
                            <i class="bi bi-circle text-muted fs-5 mode-check" id="check_mode_assigned"></i>
                        </div>
                    </div>

                    <!-- Mode 3: Dispatch Queue -->
                    <div class="mode-card" id="card_mode_queue" onclick="setExecutionMode('queue')">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 38px; height: 38px;">
                                    <i class="bi bi-inbox fs-5"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">📋 Send to Central Dispatch Queue</div>
                                    <div class="text-xs text-muted">Leaves ticket unassigned with status <strong>NEW</strong> for coordinator allocation.</div>
                                </div>
                            </div>
                            <i class="bi bi-circle text-muted fs-5 mode-check" id="check_mode_queue"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-gradient-primary btn-touch-action py-3 fw-bold rounded-pill">
                    <i class="bi bi-check2-circle fs-5 me-1"></i> Log Complaint & Proceed
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Browse All Assets for Selected Customer -->
<div class="modal fade" id="assetBrowserModal" tabindex="-1" aria-labelledby="assetBrowserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-dark text-white p-3 border-0">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-grid-3x3-gap text-info fs-5"></i>
                    <div>
                        <h6 class="modal-title fw-bold mb-0" id="assetBrowserModalLabel">Asset Directory Browser (5,000+ Assets)</h6>
                        <span class="text-xs text-info" id="modalAssetCount">Loading directory...</span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <div class="input-group mb-3 shadow-sm rounded-pill overflow-hidden border">
                    <span class="input-group-text bg-white border-0 text-primary ps-3"><i class="bi bi-search"></i></span>
                    <input type="text" id="modalAssetSearch" class="form-control border-0 ps-2" placeholder="Search by S/N, Make/Model, Asset Code, Tag, Staff...">
                    <button class="btn btn-outline-secondary border-0 pe-3" type="button" onclick="document.getElementById('modalAssetSearch').value=''; renderModalList(currentModalAssets);"><i class="bi bi-x-circle"></i></button>
                </div>

                <div id="modalAssetList" class="d-flex flex-column gap-2" style="min-height: 250px; max-height: 60vh; overflow-y: auto;">
                    <div class="text-center py-5 text-muted">
                        <div class="spinner-border text-primary mb-2"></div>
                        <div>Loading assets...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let assetSearchDebounce = null;
let currentCustomerAssets = <?= json_encode($machinesList) ?>;
let currentModalAssets = [];

function onEngineerCustomerChange(customerId) {
    clearSelectedAsset();

    if (!customerId) {
        document.getElementById('locationSelectContainer').style.display = 'none';
        renderInlineAssetCards(currentCustomerAssets);
        return;
    }

    // 1. Fetch customer locations dynamically
    fetch(`${App.baseUrl}/ajax/customer-locations?customer_id=${customerId}`)
        .then(r => r.json())
        .then(data => {
            const locSelect = document.getElementById('engineerLocationSelect');
            locSelect.innerHTML = '<option value="">-- All Locations --</option>';
            if (data.locations && data.locations.length > 0) {
                data.locations.forEach(loc => {
                    const opt = document.createElement('option');
                    opt.value = loc.id;
                    opt.textContent = `${loc.location_name} (${loc.city || ''})`;
                    locSelect.appendChild(opt);
                });
                document.getElementById('locationSelectContainer').style.display = 'block';
                if (data.locations.length === 1) {
                    locSelect.value = data.locations[0].id;
                }
            } else {
                document.getElementById('locationSelectContainer').style.display = 'none';
            }
        })
        .catch(err => console.error('Error fetching locations:', err));

    // 2. Filter local assets first or fetch from server
    const filtered = currentCustomerAssets.filter(m => m.customer_id == customerId);
    if (filtered.length > 0) {
        renderInlineAssetCards(filtered);
    } else {
        loadCustomerAssets(customerId);
    }
}

function loadCustomerAssets(customerId, locationId = '') {
    const container = document.getElementById('assetCardsContainer');
    container.innerHTML = '<div class="text-center py-3 text-muted"><div class="spinner-border spinner-border-sm text-primary me-2"></div>Loading customer hardware assets...</div>';

    fetch(`${App.baseUrl}/ajax/search-machines?customer_id=${encodeURIComponent(customerId)}&location_id=${encodeURIComponent(locationId)}&limit=100`)
        .then(r => r.json())
        .then(data => {
            const list = data.machines || [];
            renderInlineAssetCards(list);
        })
        .catch(err => {
            container.innerHTML = '<div class="alert alert-danger p-2 small">Error loading assets.</div>';
        });
}

function renderInlineAssetCards(machines) {
    const container = document.getElementById('assetCardsContainer');
    if (!machines || machines.length === 0) {
        container.innerHTML = '<div class="card p-3 text-center text-muted small bg-light border-0"><i class="bi bi-inbox fs-4 mb-1"></i>No hardware assets found.</div>';
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
        const company = m.company_name ? `<span class="badge bg-secondary-subtle text-secondary me-1">${m.company_name}</span>` : '';

        html += `
            <div class="asset-select-card p-2 p-md-3" onclick='selectAsset(${JSON.stringify(m).replace(/'/g, "&#39;")})'>
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2 overflow-hidden">
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px; font-size: 19px;">
                            <i class="bi ${iconClass}"></i>
                        </div>
                        <div class="text-truncate">
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-bold text-dark fs-7 text-truncate">${title}</span>
                                <span class="badge bg-success-subtle text-success text-xxs">AMC Active</span>
                            </div>
                            <div class="text-xs text-muted">
                                <span class="text-primary font-monospace fw-bold">${code}</span> | 
                                <span>S/N: <code>${sn}</code></span>
                            </div>
                            <div class="text-xs text-secondary text-truncate">
                                ${company}
                                <i class="bi bi-person"></i> ${user} (${dept})
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-1 text-xs fw-bold flex-shrink-0 ms-2 shadow-sm">
                        Select
                    </button>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}

function addSymptom(el, text) {
    const area = document.getElementById('reportedIssueText');
    if (area.value.trim() === '') {
        area.value = text;
    } else if (!area.value.includes(text)) {
        area.value += '; ' + text;
    }
    el.classList.add('active');
    area.focus();
}

function setExecutionMode(mode) {
    document.getElementById('assignModeInput').value = mode;

    ['onsite', 'assigned', 'queue'].forEach(m => {
        const card = document.getElementById(`card_mode_${m}`);
        const check = document.getElementById(`check_mode_${m}`);
        if (m === mode || (mode === 'self_onsite' && m === 'onsite') || (mode === 'self_assigned' && m === 'assigned')) {
            card.classList.add('selected');
            check.className = 'bi bi-check-circle-fill text-primary fs-5 mode-check';
        } else {
            card.classList.remove('selected');
            check.className = 'bi bi-circle text-muted fs-5 mode-check';
        }
    });
}

function selectAsset(machine) {
    document.getElementById('selectedMachineId').value = machine.id;
    document.getElementById('assetTitle').textContent = `${machine.make} ${machine.model}`;
    document.getElementById('assetCode').textContent = machine.asset_code;
    document.getElementById('assetSerial').textContent = machine.serial_number;
    document.getElementById('assetUser').textContent = machine.assigned_employee || 'Shared / Common';
    document.getElementById('assetDept').textContent = machine.department || 'General';

    // Auto select customer if not selected
    if (machine.customer_id) {
        const custSelect = document.getElementById('engineerCustomerSelect');
        if (custSelect && custSelect.value !== String(machine.customer_id)) {
            custSelect.value = machine.customer_id;
            
            // Load locations for this customer
            fetch(`${App.baseUrl}/ajax/customer-locations?customer_id=${machine.customer_id}`)
                .then(r => r.json())
                .then(data => {
                    const locSelect = document.getElementById('engineerLocationSelect');
                    locSelect.innerHTML = '<option value="">-- Select Location --</option>';
                    if (data.locations && data.locations.length > 0) {
                        data.locations.forEach(loc => {
                            const opt = document.createElement('option');
                            opt.value = loc.id;
                            opt.textContent = `${loc.location_name} (${loc.city || ''})`;
                            locSelect.appendChild(opt);
                        });
                        document.getElementById('locationSelectContainer').style.display = 'block';
                        if (machine.location_id) {
                            locSelect.value = machine.location_id;
                        } else if (data.locations.length === 1) {
                            locSelect.value = data.locations[0].id;
                        }
                    }
                });
        }
    }

    if (machine.location_id) {
        setTimeout(() => {
            const locSelect = document.getElementById('engineerLocationSelect');
            if (locSelect) locSelect.value = machine.location_id;
        }, 300);
    }

    document.getElementById('selectedAssetCard').style.display = 'block';
    document.getElementById('assetSelectorSection').style.display = 'none';

    // Dismiss modal if open
    const modalEl = document.getElementById('assetBrowserModal');
    const modalInstance = bootstrap.Modal.getInstance(modalEl);
    if (modalInstance) modalInstance.hide();
}

function clearSelectedAsset() {
    document.getElementById('selectedMachineId').value = '';
    document.getElementById('selectedAssetCard').style.display = 'none';
    document.getElementById('assetSelectorSection').style.display = 'block';
    const input = document.getElementById('assetSearchInput');
    if (input) {
        input.value = '';
    }
}

function selectNoAsset() {
    document.getElementById('selectedMachineId').value = '';
    document.getElementById('assetTitle').textContent = 'General / Facility / Network Issue';
    document.getElementById('assetCode').textContent = 'NON-ASSET';
    document.getElementById('assetSerial').textContent = 'N/A';
    document.getElementById('assetUser').textContent = 'Site Wide';
    document.getElementById('assetDept').textContent = 'Entire Branch';

    document.getElementById('selectedAssetCard').style.display = 'block';
    document.getElementById('assetSelectorSection').style.display = 'none';
}

function resetSearchInput() {
    const input = document.getElementById('assetSearchInput');
    input.value = '';
    document.getElementById('clearAssetSearchBtn').style.display = 'none';
    const custId = document.getElementById('engineerCustomerSelect').value;
    if (custId) {
        const filtered = currentCustomerAssets.filter(m => m.customer_id == custId);
        renderInlineAssetCards(filtered.length > 0 ? filtered : currentCustomerAssets);
    } else {
        renderInlineAssetCards(currentCustomerAssets);
    }
    input.focus();
}

let modalSearchDebounce = null;
let assetModalInstance = null;

function openAssetBrowserModal() {
    const customerId = document.getElementById('engineerCustomerSelect').value || '';
    const locationId = document.getElementById('engineerLocationSelect').value || '';
    const modalEl = document.getElementById('assetBrowserModal');
    
    if (!assetModalInstance) {
        assetModalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    }
    assetModalInstance.show();

    const listContainer = document.getElementById('modalAssetList');
    const countLabel = document.getElementById('modalAssetCount');
    const searchInput = document.getElementById('modalAssetSearch');
    if (searchInput) searchInput.value = '';

    listContainer.innerHTML = '<div class="text-center py-5 text-muted"><div class="spinner-border text-primary mb-2"></div><div>Loading assets directory...</div></div>';
    if (countLabel) countLabel.innerText = 'Searching directory...';

    fetch(`${App.baseUrl}/ajax/search-machines?customer_id=${encodeURIComponent(customerId)}&location_id=${encodeURIComponent(locationId)}&limit=500`)
        .then(r => r.json())
        .then(data => {
            currentModalAssets = data.machines || data.results || [];
            renderModalList(currentModalAssets);
        })
        .catch(err => {
            listContainer.innerHTML = '<div class="alert alert-danger p-3 rounded-4 small"><i class="bi bi-exclamation-circle me-1"></i> Failed to connect to asset database. Please try again.</div>';
            if (countLabel) countLabel.innerText = 'Error loading assets';
        });
}

function renderModalList(machines) {
    const listContainer = document.getElementById('modalAssetList');
    const countLabel = document.getElementById('modalAssetCount');
    
    if (countLabel) {
        countLabel.innerText = `Showing ${machines.length} asset(s)`;
    }

    if (!machines || machines.length === 0) {
        listContainer.innerHTML = `
            <div class="card p-5 text-center border-0 bg-light rounded-4 text-muted">
                <i class="bi bi-inbox fs-1 text-secondary mb-2"></i>
                <h6 class="fw-bold text-dark">No Hardware Assets Found</h6>
                <p class="text-xs text-muted mb-0">Try changing your search query or choosing another customer branch.</p>
            </div>
        `;
        return;
    }

    let html = '';
    machines.forEach(m => {
        const title = `${m.make || ''} ${m.model || ''}`;
        const sn = m.serial_number || 'N/A';
        const code = m.asset_code || 'AST-N/A';
        const user = m.assigned_employee || 'Shared / Pool';
        const dept = m.department || 'General';
        const loc = m.location_name ? `${m.location_name}` : '';
        const client = m.company_name ? `<span class="badge bg-dark-subtle text-dark border me-1">${m.company_name}</span>` : '';
        const safeJson = JSON.stringify(m).replace(/'/g, "&#39;");

        html += `
            <div class="card border rounded-4 p-3 hover-shadow transition-all bg-white mb-1">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px;">
                            <i class="bi bi-pc-display fs-5"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <h6 class="fw-bold text-dark mb-0">${title}</h6>
                                <span class="badge bg-primary font-monospace text-xxs">${code}</span>
                                ${client}
                            </div>
                            <div class="text-xs text-muted mt-1">
                                <span>S/N: <code class="text-dark fw-bold">${sn}</code></span>
                                <span class="mx-1">&bull;</span>
                                <span><i class="bi bi-person me-1"></i>${user} (${dept})</span>
                                ${loc ? `<span class="mx-1">&bull;</span><span><i class="bi bi-geo-alt me-1 text-danger"></i>${loc}</span>` : ''}
                            </div>
                        </div>
                    </div>
                    <div>
                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm d-flex align-items-center gap-1" onclick='selectAsset(${safeJson})'>
                            <i class="bi bi-check2-circle"></i> <span>Select</span>
                        </button>
                    </div>
                </div>
            </div>
        `;
    });
    listContainer.innerHTML = html;
}

// Live search listener
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('assetSearchInput');
    const clearBtn = document.getElementById('clearAssetSearchBtn');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const q = this.value.trim().toLowerCase();
            clearBtn.style.display = q ? 'block' : 'none';

            if (!q) {
                const custId = document.getElementById('engineerCustomerSelect').value;
                const filtered = custId ? currentCustomerAssets.filter(m => m.customer_id == custId) : currentCustomerAssets;
                renderInlineAssetCards(filtered);
                return;
            }

            // Real-time filter
            const filtered = currentCustomerAssets.filter(m => {
                const searchStr = `${m.asset_code} ${m.serial_number} ${m.asset_tag || ''} ${m.make} ${m.model} ${m.assigned_employee || ''} ${m.department || ''} ${m.company_name || ''}`.toLowerCase();
                return searchStr.includes(q);
            });

            if (filtered.length > 0) {
                renderInlineAssetCards(filtered);
            } else {
                // Perform backend debounced AJAX search
                clearTimeout(assetSearchDebounce);
                assetSearchDebounce = setTimeout(() => {
                    const customerId = document.getElementById('engineerCustomerSelect').value || '';
                    const locationId = document.getElementById('engineerLocationSelect').value || '';

                    fetch(`${App.baseUrl}/ajax/search-machines?q=${encodeURIComponent(q)}&customer_id=${encodeURIComponent(customerId)}&location_id=${encodeURIComponent(locationId)}&limit=100`)
                        .then(res => res.json())
                        .then(data => {
                            renderInlineAssetCards(data.machines || data.results || []);
                        });
                }, 250);
            }
        });
    }

    // Modal Live Filter & Backend Search
    const modalFilterInput = document.getElementById('modalAssetSearch');
    if (modalFilterInput) {
        modalFilterInput.addEventListener('input', function() {
            const filter = this.value.toLowerCase().trim();
            if (!filter) {
                renderModalList(currentModalAssets);
                return;
            }

            // 1. Instant client-side search across loaded assets
            const filtered = currentModalAssets.filter(m => {
                const searchStr = `${m.asset_code} ${m.serial_number} ${m.asset_tag || ''} ${m.make} ${m.model} ${m.assigned_employee || ''} ${m.department || ''} ${m.company_name || ''}`.toLowerCase();
                return searchStr.includes(filter);
            });
            renderModalList(filtered);

            // 2. Debounced deep search in backend database
            clearTimeout(modalSearchDebounce);
            modalSearchDebounce = setTimeout(() => {
                const customerId = document.getElementById('engineerCustomerSelect').value || '';
                const locationId = document.getElementById('engineerLocationSelect').value || '';

                fetch(`${App.baseUrl}/ajax/search-machines?q=${encodeURIComponent(filter)}&customer_id=${encodeURIComponent(customerId)}&location_id=${encodeURIComponent(locationId)}&limit=500`)
                    .then(res => res.json())
                    .then(data => {
                        const dbResults = data.machines || data.results || [];
                        if (dbResults.length > 0) {
                            renderModalList(dbResults);
                        }
                    });
            }, 300);
        });
    }
});
</script>
