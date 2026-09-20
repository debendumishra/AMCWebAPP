<?php
/**
 * Field Engineer All Tasks List View
 */
?>
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
        <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
            <i class="bi bi-list-task text-primary"></i> All Assigned Tasks
        </h4>
        <div class="text-xs text-muted">Complete roster of ongoing and past allocated service calls</div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="<?= BASE_URL ?>/engineer/calls/create" class="btn btn-sm btn-primary rounded-pill px-3 py-2 shadow-sm fw-bold d-inline-flex align-items-center gap-1">
            <i class="bi bi-plus-circle-fill"></i> <span>Log Ticket</span>
        </a>
        <span class="badge bg-secondary rounded-pill px-3 py-2 fs-7"><?= count($calls) ?> Total</span>
    </div>
</div>

<?php if (empty($calls)): ?>
    <div class="card p-5 text-center border-0 shadow-sm rounded-4 bg-white">
        <i class="bi bi-folder-x display-4 text-muted mb-2"></i>
        <h5 class="fw-bold text-dark">No Assigned Tasks Found</h5>
        <p class="text-muted small mb-3">You currently have no tasks assigned in your queue.</p>
        <div>
            <a href="<?= BASE_URL ?>/engineer/calls/create" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm">
                <i class="bi bi-plus-circle me-1"></i> Raise / Log New Complain
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="row g-3 g-md-4">
        <?php foreach ($calls as $c): ?>
            <div class="col-12 col-md-6 col-xxl-4 d-flex">
                <div class="card border-0 shadow-sm rounded-4 w-100 d-flex flex-column bg-white overflow-hidden">
                    <div class="card-body p-3 p-md-4 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold text-primary fs-6 text-nowrap"><?= $c['call_number'] ?></span>
                            <span class="badge-status badge-status-<?= strtolower($c['status']) ?>"><?= str_replace('_', ' ', $c['status']) ?></span>
                        </div>
                        <h6 class="fw-bold text-dark mb-1"><?= htmlspecialchars($c['company_name']) ?></h6>
                        <div class="text-xs text-muted mb-2"><i class="bi bi-geo-alt-fill text-danger me-1"></i> <?= htmlspecialchars($c['location_name']) ?></div>
                        
                        <div class="p-2 rounded-3 bg-light mb-3 text-xs border border-light-subtle flex-grow-1">
                            <?= htmlspecialchars(substr($c['reported_issue'] ?? '', 0, 100)) ?><?= strlen($c['reported_issue'] ?? '') > 100 ? '...' : '' ?>
                        </div>

                        <a href="<?= BASE_URL ?>/engineer/calls/<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill w-100 mt-auto py-2 fw-bold d-flex align-items-center justify-content-center gap-1">
                            <i class="bi bi-folder2-open"></i> <span>View Ticket & Journey</span>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
