<?php
/**
 * Field Engineer Spare Requisitions View
 */
?>
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
        <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
            <i class="bi bi-box-seam text-primary"></i> My Spare Part Requests
        </h4>
        <div class="text-xs text-muted">Status of requested replacement components and parts from inventory</div>
    </div>
    <span class="badge bg-warning text-dark rounded-pill px-3 py-2 fs-7 fw-bold"><?= count($requisitions) ?> Total Requisitions</span>
</div>

<?php if (empty($requisitions)): ?>
    <div class="card p-5 text-center border-0 shadow-sm rounded-4 bg-white">
        <i class="bi bi-box-seam text-muted display-4 mb-2"></i>
        <h5 class="fw-bold text-dark">No Spare Part Requisitions</h5>
        <p class="text-muted small mb-0">You have not submitted any spare part requests yet. You can request spares directly inside any active service ticket.</p>
    </div>
<?php else: ?>
    <div class="row g-3 g-md-4">
        <?php foreach ($requisitions as $r): ?>
            <div class="col-12 col-md-6 col-xxl-4 d-flex">
                <div class="card border-0 shadow-sm rounded-4 w-100 d-flex flex-column bg-white overflow-hidden">
                    <div class="card-body p-3 p-md-4 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold text-primary fs-6 text-nowrap"><?= $r['request_number'] ?></span>
                            <span class="badge <?= $r['status'] === 'APPROVED' ? 'bg-success' : ($r['status'] === 'REQUESTED' ? 'bg-warning text-dark' : 'bg-secondary') ?> rounded-pill px-2 py-1 text-xs">
                                <?= $r['status'] ?>
                            </span>
                        </div>
                        <div class="text-dark fw-bold mb-1">Call: <a href="<?= BASE_URL ?>/engineer/calls/<?= $r['call_id'] ?>" class="text-decoration-none"><?= $r['call_number'] ?></a></div>
                        <div class="text-xs text-muted mb-3"><?= htmlspecialchars($r['company_name']) ?></div>
                        
                        <?php if (!empty($r['approval_remarks'])): ?>
                            <div class="p-2 bg-light rounded-3 text-xs text-muted mt-auto border border-light-subtle">
                                <strong class="text-dark">Admin Note:</strong> <?= htmlspecialchars($r['approval_remarks']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
