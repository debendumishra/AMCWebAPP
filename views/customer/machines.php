<?php
/**
 * Customer Asset List View
 */
?>
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1">My Registered Hardware & IT Assets</h4>
        <p class="text-muted small mb-0">List of computers, servers, laptops, and printers covered under maintenance.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/customer/calls/create" class="btn btn-primary btn-sm shadow-sm">
            <i class="bi bi-plus-circle me-1"></i> Raise Issue on Asset
        </a>
    </div>
</div>

<!-- Search & Quick Filter Bar for large asset fleets -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <div class="input-group">
            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-primary"></i></span>
            <input type="text" id="machineSearchFilter" class="form-control border-start-0 ps-0" placeholder="Quick search by Serial Number, Asset Tag, Make/Model, Assigned User, or Department..." autocomplete="off">
            <button class="btn btn-outline-secondary" type="button" id="clearSearchBtn" style="display:none;">Clear</button>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-2 px-1">
            <small class="text-muted" id="machineCountSummary">Showing <?= count($machines) ?> assets</small>
            <small class="text-primary fw-medium"><i class="bi bi-lightning-charge me-1"></i>Click "Report Issue" on any machine to prefill complaint form</small>
        </div>
    </div>
</div>

<div class="row g-3" id="machinesGrid">
    <?php if (empty($machines)): ?>
        <div class="col-12"><div class="card p-4 text-center text-muted">No machines registered under your account.</div></div>
    <?php else: ?>
        <?php foreach ($machines as $m): ?>
            <div class="col-md-6 col-lg-4 machine-card-col" 
                 data-keywords="<?= strtolower(htmlspecialchars(($m['asset_code'] ?? '') . ' ' . ($m['serial_number'] ?? '') . ' ' . ($m['asset_tag'] ?? '') . ' ' . ($m['make'] ?? '') . ' ' . ($m['model'] ?? '') . ' ' . ($m['assigned_employee'] ?? '') . ' ' . ($m['department'] ?? '') . ' ' . ($m['location_name'] ?? ''))) ?>">
                <div class="card h-100 card-hover shadow-sm border">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                        <strong class="text-primary font-monospace"><?= htmlspecialchars($m['asset_code']) ?></strong>
                        <span class="badge bg-success-subtle text-success border border-success-subtle">AMC Active</span>
                    </div>
                    <div class="card-body">
                        <h6 class="fw-bold text-dark mb-1"><?= htmlspecialchars($m['make'] . ' ' . $m['model']) ?></h6>
                        <div class="text-xs text-muted mb-2">S/N: <code><?= htmlspecialchars($m['serial_number']) ?></code> <?= !empty($m['asset_tag']) ? '| Tag: <span class="fw-semibold">' . htmlspecialchars($m['asset_tag']) . '</span>' : '' ?></div>
                        <div class="small text-muted mb-2">
                            <i class="bi bi-geo-alt text-secondary"></i> <?= htmlspecialchars($m['location_name']) ?> (<?= htmlspecialchars($m['department'] ?? 'General') ?>)<br>
                            <i class="bi bi-person text-secondary"></i> User: <span class="fw-medium text-dark"><?= htmlspecialchars($m['assigned_employee'] ?? 'Shared / Common') ?></span>
                        </div>
                        <?php if (!empty($m['processor']) || !empty($m['ram'])): ?>
                        <div class="p-2 bg-light rounded text-xs text-muted">
                            CPU: <?= htmlspecialchars($m['processor'] ?? 'Standard') ?> | RAM: <?= htmlspecialchars($m['ram'] ?? '8GB') ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-white border-top-0 pt-0 pb-3">
                        <a href="<?= BASE_URL ?>/customer/calls/create?machine_id=<?= $m['id'] ?>" class="btn btn-sm btn-outline-primary w-100 fw-semibold">
                            <i class="bi bi-headset me-1"></i> Report Issue on this Asset
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div id="noResultsAlert" class="card p-4 text-center text-muted mt-3" style="display: none;">
    <i class="bi bi-search fs-1 mb-2 text-secondary opacity-50"></i>
    <div class="fw-bold">No assets match your search keyword.</div>
    <div class="small">Try searching by Serial Number, Model, Asset Code, or Staff Name.</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('machineSearchFilter');
    const clearBtn = document.getElementById('clearSearchBtn');
    const cards = document.querySelectorAll('.machine-card-col');
    const countSummary = document.getElementById('machineCountSummary');
    const noResults = document.getElementById('noResultsAlert');
    const totalCount = cards.length;

    if (!searchInput) return;

    searchInput.addEventListener('input', function() {
        const q = this.value.trim().toLowerCase();
        clearBtn.style.display = q ? 'inline-block' : 'none';
        let visibleCount = 0;

        cards.forEach(card => {
            const kw = card.getAttribute('data-keywords') || '';
            if (!q || kw.includes(q)) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        countSummary.textContent = q ? `Found ${visibleCount} matching assets of ${totalCount}` : `Showing ${totalCount} assets`;
        noResults.style.display = (visibleCount === 0 && totalCount > 0) ? 'block' : 'none';
    });

    clearBtn.addEventListener('click', function() {
        searchInput.value = '';
        searchInput.dispatchEvent(new Event('input'));
        searchInput.focus();
    });
});
</script>
