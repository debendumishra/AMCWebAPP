<?php
/**
 * Customer Self-Service Raise Complaint View with Instant 5,000+ Asset Search
 */
$selectedMachineId = Request::get('machine_id');
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Raise Service Request / Complaint</h4>
        <p class="text-muted small mb-0">Search your machine or asset effortlessly from thousands of registered devices.</p>
    </div>
    <a href="<?= BASE_URL ?>/customer/calls" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
        <i class="bi bi-arrow-left me-1"></i> Back to Tickets
    </a>
</div>

<div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
    <div class="card-header bg-dark text-white p-3 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-headset text-primary fs-5"></i>
            <span class="fw-bold">New Complaint Registration</span>
        </div>
        <span class="badge bg-primary rounded-pill">Fast Track Service</span>
    </div>
    <div class="card-body p-4">
        <form method="POST" action="<?= BASE_URL ?>/customer/calls/create" id="raiseComplaintForm">
            <input type="hidden" name="csrf_token" value="<?= Request::csrfToken() ?>">
            <input type="hidden" name="machine_id" id="selectedMachineId" value="<?= htmlspecialchars($selectedMachineId ?? '') ?>">

            <!-- 1. ASSET IDENTIFICATION SECTION (Search among 5,000+ Assets) -->
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label fw-bold text-dark mb-0">
                        <i class="bi bi-pc-display text-primary me-1"></i> Problematic Machine / IT Asset
                    </label>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-xs btn-dark rounded-pill px-3 shadow-sm fw-medium" onclick="openQrScannerModal()">
                            <i class="bi bi-qr-code-scan text-info me-1"></i> Scan QR
                        </button>
                        <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-3" onclick="openAssetBrowserModal()">
                            <i class="bi bi-grid-3x3-gap me-1"></i> Browse Assets
                        </button>
                    </div>
                </div>

                <!-- Selected Machine Preview Card (Hidden when none selected) -->
                <div id="selectedAssetCard" class="card border-primary border-2 bg-primary bg-opacity-10 rounded-3 p-3 mb-3" style="display: none;">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; font-size: 20px;">
                                <i class="bi bi-pc-display" id="selectedAssetIcon"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-0" id="selectedAssetTitle">Dell OptiPlex 7090</h6>
                                <div class="text-xs text-muted">
                                    <span>Code: <strong class="text-primary" id="selectedAssetCode">AST-2026-000001</strong></span> | 
                                    <span>S/N: <code id="selectedAssetSerial">DELL-7090-001</code></span> | 
                                    <span id="selectedAssetDept">Finance</span>
                                </div>
                                <div class="text-xxs text-muted mt-1" id="selectedAssetSpecs">Core i7 | 16GB RAM | 512GB SSD</div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1 text-xs" onclick="clearSelectedAsset()">
                            <i class="bi bi-x-circle me-1"></i> Change Asset
                        </button>
                    </div>
                </div>

                <!-- Fast Multi-Field Search Input Box -->
                <div id="assetSearchBox" class="position-relative">
                    <div class="input-group input-group-lg shadow-sm rounded-4 overflow-hidden border">
                        <span class="input-group-text bg-white border-0 text-primary pe-1"><i class="bi bi-search fs-5"></i></span>
                        <input type="text" 
                               id="assetSearchInput" 
                               class="form-control border-0 ps-2 fs-6" 
                               placeholder="Search across 5,000+ assets by Code, S/N, Make/Model, Dept or Employee..."
                               autocomplete="off">
                        <button class="btn btn-light border-0 text-muted px-3" type="button" id="clearSearchBtn" onclick="clearSearchInput()" style="display: none;">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="form-text text-xxs text-muted mt-1 px-2">
                        <i class="bi bi-info-circle me-1 text-primary"></i> Type any keyword (e.g. <code>AST-001</code>, <code>DELL-7090</code>, <code>Accounts</code>, <code>ThinkPad</code>) for instant suggestions.
                    </div>

                    <!-- Instant Live Results Floating Dropdown -->
                    <div id="assetSearchResults" class="card position-absolute w-100 shadow-lg border-0 rounded-3 mt-1 overflow-hidden" style="z-index: 1050; display: none; max-height: 380px; overflow-y: auto;">
                        <div class="list-group list-group-flush" id="assetResultsList">
                            <!-- Injected dynamically via JS -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. LOCATION & PRIORITY SECTION -->
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Service Location / Office Branch *</label>
                    <select name="location_id" id="locationSelect" class="form-select" required>
                        <?php foreach ($locations as $loc): ?>
                            <option value="<?= $loc['id'] ?>"><?= htmlspecialchars($loc['location_name']) ?> (<?= htmlspecialchars($loc['address']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold">Urgency / Severity Level *</label>
                    <select name="priority" class="form-select">
                        <option value="HIGH" selected>High (Workstation down / Urgent breakdown)</option>
                        <option value="CRITICAL">Critical (Total stoppage / Server offline)</option>
                        <option value="MEDIUM">Medium (Intermittent fault / Slow performance)</option>
                        <option value="LOW">Low (Routine check / Software setup)</option>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label small fw-bold">Problem Symptoms & Description *</label>
                    <textarea name="reported_issue" class="form-control rounded-3" rows="4" required placeholder="Please describe the fault symptoms (e.g. PC not powering on, blue screen error, display flickering, printer offline, OS corrupted)..."></textarea>
                </div>
            </div>

            <!-- Action Bar -->
            <div class="d-flex justify-content-between align-items-center border-top pt-3">
                <a href="<?= BASE_URL ?>/customer/calls" class="btn btn-light border rounded-pill px-4">Cancel</a>
                <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                    <i class="bi bi-send-fill me-1"></i> Register Complaint
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Full Asset Browser for 5,000+ Assets -->
<div class="modal fade" id="assetBrowserModal" tabindex="-1" aria-labelledby="assetBrowserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-dark text-white p-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-pc-display-horizontal text-primary fs-5"></i>
                    <div>
                        <h6 class="modal-title fw-bold mb-0 text-white" id="assetBrowserModalLabel">Asset Directory Browser</h6>
                        <span class="text-xs text-white-50">Filter and choose from all registered hardware assets</span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3 bg-light">
                <!-- Modal Search Filter Bar -->
                <div class="input-group mb-3 shadow-sm rounded-3 overflow-hidden">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" id="modalAssetSearchInput" class="form-control border-start-0" placeholder="Type to filter assets by code, make, serial, department...">
                </div>

                <div id="modalAssetList" class="row g-2">
                    <!-- Populated via AJAX -->
                    <div class="col-12 text-center p-4">
                        <div class="spinner-border spinner-border-sm text-primary"></div>
                        <span class="ms-2 small text-muted">Loading assets...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-white p-2 d-flex justify-content-between">
                <span class="text-xs text-muted" id="modalAssetCount">Showing assets</span>
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
let searchDebounceTimer = null;
let assetModalInstance = null;

document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('assetSearchInput');
    const modalSearchInput = document.getElementById('modalAssetSearchInput');

    // Live search input listener with debounce
    searchInput.addEventListener('input', (e) => {
        clearTimeout(searchDebounceTimer);
        const q = e.target.value.trim();
        document.getElementById('clearSearchBtn').style.display = q ? 'block' : 'none';

        if (q.length < 1) {
            document.getElementById('assetSearchResults').style.display = 'none';
            return;
        }

        searchDebounceTimer = setTimeout(() => {
            fetchAssets(q, false);
        }, 250);
    });

    // Close live search dropdown when clicking outside
    document.addEventListener('click', (e) => {
        const box = document.getElementById('assetSearchBox');
        if (box && !box.contains(e.target)) {
            document.getElementById('assetSearchResults').style.display = 'none';
        }
    });

    // Modal Search listener
    modalSearchInput.addEventListener('input', (e) => {
        clearTimeout(searchDebounceTimer);
        const q = e.target.value.trim();
        searchDebounceTimer = setTimeout(() => {
            fetchAssets(q, true);
        }, 250);
    });

    // If pre-selected machine_id is given in URL
    const preSelectedId = document.getElementById('selectedMachineId').value;
    if (preSelectedId) {
        fetch(`${App.baseUrl}/ajax/search-machines?q=&id=${preSelectedId}`)
            .then(res => res.json())
            .then(data => {
                if (data.results && data.results.length > 0) {
                    const match = data.results.find(m => m.id == preSelectedId) || data.results[0];
                    selectAsset(match);
                }
            });
    }
});

function fetchAssets(query, isModal = false) {
    const url = `${App.baseUrl}/ajax/search-machines?q=${encodeURIComponent(query)}`;
    
    fetch(url)
        .then(res => res.json())
        .then(data => {
            if (isModal) {
                renderModalResults(data.results || []);
            } else {
                renderDropdownResults(data.results || []);
            }
        })
        .catch(err => {
            console.error('Asset search error:', err);
        });
}

function renderDropdownResults(assets) {
    const container = document.getElementById('assetResultsList');
    const wrapper = document.getElementById('assetSearchResults');
    container.innerHTML = '';

    if (assets.length === 0) {
        container.innerHTML = `
            <div class="p-3 text-center text-muted small">
                <i class="bi bi-exclamation-circle d-block fs-5 text-secondary mb-1"></i>
                No matching assets found. You can still submit a general request.
            </div>
        `;
        wrapper.style.display = 'block';
        return;
    }

    assets.forEach(m => {
        const item = document.createElement('a');
        item.href = 'javascript:void(0)';
        item.className = 'list-group-item list-group-item-action p-2 d-flex justify-content-between align-items-center';
        item.onclick = () => selectAsset(m);

        item.innerHTML = `
            <div class="d-flex align-items-center gap-2 overflow-hidden">
                <div class="rounded bg-light border p-2 text-primary">
                    <i class="bi ${m.asset_type_icon || 'bi-pc-display'} fs-5"></i>
                </div>
                <div class="text-truncate">
                    <div class="fw-bold text-dark text-xs">${escapeHtml(m.make || '')} ${escapeHtml(m.model || '')}</div>
                    <div class="text-muted text-xxs">
                        <span class="badge bg-primary-subtle text-primary">${escapeHtml(m.asset_code || '')}</span>
                        <span>S/N: <code>${escapeHtml(m.serial_number || 'N/A')}</code></span>
                        ${m.department ? `&bull; ${escapeHtml(m.department)}` : ''}
                    </div>
                </div>
            </div>
            <div class="text-end ps-2">
                <span class="badge ${m.active_contract_number ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary'} text-xxs">
                    ${m.active_contract_number ? 'Under AMC' : 'Standard'}
                </span>
                <div class="text-xxs text-muted mt-1">${escapeHtml(m.location_name || '')}</div>
            </div>
        `;
        container.appendChild(item);
    });

    wrapper.style.display = 'block';
}

function renderModalResults(assets) {
    const list = document.getElementById('modalAssetList');
    const countLabel = document.getElementById('modalAssetCount');
    list.innerHTML = '';
    countLabel.innerText = `Found ${assets.length} asset(s)`;

    if (assets.length === 0) {
        list.innerHTML = `
            <div class="col-12 text-center p-4 text-muted small">
                <i class="bi bi-search display-6 text-secondary mb-2 d-block"></i>
                No hardware assets matched your search criteria.
            </div>
        `;
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
                    <div>Location: ${escapeHtml(m.location_name || '')} ${m.department ? `(${escapeHtml(m.department)})` : ''}</div>
                </div>
                <button type="button" class="btn btn-sm btn-primary rounded-pill w-100 fw-bold py-1 text-xs mt-auto">
                    <i class="bi bi-check2-circle me-1"></i> Select Asset
                </button>
            </div>
        `;
        col.querySelector('button').onclick = () => {
            selectAsset(m);
            if (assetModalInstance) assetModalInstance.hide();
        };
        list.appendChild(col);
    });
}

function selectAsset(m) {
    document.getElementById('selectedMachineId').value = m.id;
    document.getElementById('selectedAssetTitle').innerText = `${m.make || ''} ${m.model || ''}`;
    document.getElementById('selectedAssetCode').innerText = m.asset_code || 'N/A';
    document.getElementById('selectedAssetSerial').innerText = m.serial_number || 'N/A';
    document.getElementById('selectedAssetDept').innerText = m.department ? `Dept: ${m.department}` : (m.assigned_employee ? `User: ${m.assigned_employee}` : '');
    document.getElementById('selectedAssetSpecs').innerText = `${m.processor || 'Std'} | ${m.ram || '8GB'} | ${m.storage || 'SSD'}`;

    if (m.location_id) {
        document.getElementById('locationSelect').value = m.location_id;
    }

    document.getElementById('selectedAssetCard').style.display = 'block';
    document.getElementById('assetSearchBox').style.display = 'none';
    document.getElementById('assetSearchResults').style.display = 'none';
}

function clearSelectedAsset() {
    document.getElementById('selectedMachineId').value = '';
    document.getElementById('selectedAssetCard').style.display = 'none';
    document.getElementById('assetSearchBox').style.display = 'block';
    document.getElementById('assetSearchInput').value = '';
    document.getElementById('assetSearchInput').focus();
}

function clearSearchInput() {
    document.getElementById('assetSearchInput').value = '';
    document.getElementById('clearSearchBtn').style.display = 'none';
    document.getElementById('assetSearchResults').style.display = 'none';
}

function openAssetBrowserModal() {
    const modalEl = document.getElementById('assetBrowserModal');
    if (!assetModalInstance) {
        assetModalInstance = new bootstrap.Modal(modalEl);
    }
    assetModalInstance.show();
    fetchAssets('', true);
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

// Hook QR scanner to auto-populate customer complaint form
window.onQrCodeScanned = function(cleanToken, raw) {
    if (!cleanToken) return;

    fetch(`${App.baseUrl}/ajax/search-machines?qr=${encodeURIComponent(cleanToken)}`)
        .then(res => res.json())
        .then(data => {
            if (data.success && data.machine) {
                selectAsset(data.machine);
                if (typeof App !== 'undefined' && App.toast) {
                    App.toast(`Asset Identified: ${data.machine.make} ${data.machine.model} (${data.machine.asset_code})`, 'success');
                }
            } else {
                alert(`Asset not found for scanned code: "${cleanToken}". Please check if this asset belongs to your account.`);
            }
        })
        .catch(err => {
            console.error('Error scanning asset:', err);
            alert('Could not look up asset. Please check network connection.');
        });
};
</script>
