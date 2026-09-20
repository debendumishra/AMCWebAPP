<?php
/**
 * PM Checklists Master View
 */
?>
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
            <i class="bi bi-card-checklist text-primary"></i> Preventive Maintenance (PM) Checklists
        </h4>
        <p class="text-muted small mb-0">Define mandatory diagnostic inspection points per asset type (Desktop, Laptop, Server, Switch, Printer).</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="<?= BASE_URL ?>/pm" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Back to PM Schedules
        </a>
        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#addChecklistModal">
            <i class="bi bi-plus-circle me-1"></i> New PM Checklist
        </button>
    </div>
</div>

<div class="row g-4">
    <?php foreach ($checklists as $cl): ?>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <strong class="text-dark"><?= htmlspecialchars($cl['title']) ?></strong>
                        <div class="text-xs text-primary"><?= htmlspecialchars($cl['asset_type_name']) ?></div>
                    </div>
                    <span class="badge bg-light text-dark border"><?= $cl['items_count'] ?> Tasks</span>
                </div>
                <div class="card-body">
                    <?php
                    $db = Database::getInstance();
                    $items = $db->prepare("SELECT * FROM pm_checklist_items WHERE checklist_id = :cid ORDER BY sort_order ASC");
                    $items->execute(['cid' => $cl['id']]);
                    $tasks = $items->fetchAll();
                    ?>
                    <ul class="list-group list-group-flush small">
                        <?php foreach ($tasks as $t): ?>
                            <li class="list-group-item d-flex align-items-center gap-2 px-0">
                                <i class="bi bi-check-square-fill text-success"></i>
                                <span><?= htmlspecialchars($t['task_description']) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Add Checklist Modal -->
<div class="modal fade" id="addChecklistModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <form method="POST" action="<?= BASE_URL ?>/pm/checklists">
                <input type="hidden" name="csrf_token" value="<?= Request::csrfToken() ?>">
                <div class="modal-header bg-dark text-white p-3">
                    <h6 class="modal-title fw-bold"><i class="bi bi-card-checklist text-primary me-2"></i>Create PM Checklist</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Checklist Title *</label>
                        <input type="text" name="title" class="form-control" required placeholder="e.g. Server & Storage 12-Point PM Checklist">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Target Asset Type *</label>
                        <select name="asset_type_id" class="form-select" required>
                            <?php foreach ($assetTypes as $at): ?>
                                <option value="<?= $at['id'] ?>"><?= htmlspecialchars($at['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Initial Inspection Task *</label>
                        <textarea name="task_description" class="form-control" rows="2" required placeholder="e.g. Inspect RAID array health status & fan speed RPM"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light p-3">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Save Checklist</button>
                </div>
            </form>
        </div>
    </div>
</div>
