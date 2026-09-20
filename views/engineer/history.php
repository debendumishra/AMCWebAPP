<?php
/**
 * Field Engineer Service History View with Date Range Filter, Direct PDF Download & WhatsApp Sharing
 */
$cleanPreset = $preset ?? '';
?>
<style>
.history-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
    transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
}
.history-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
    border-color: #cbd5e1;
}
.btn-whatsapp-action {
    background-color: #25D366;
    color: #ffffff !important;
    border: none;
    transition: background-color 0.15s ease;
}
.btn-whatsapp-action:hover {
    background-color: #1eb956;
    color: #ffffff !important;
}
.btn-pdf-action {
    background-color: #0f172a;
    color: #ffffff !important;
    border: none;
    transition: background-color 0.15s ease;
}
.btn-pdf-action:hover {
    background-color: #1e293b;
    color: #ffffff !important;
}
.filter-preset-pill {
    font-size: 0.8rem;
    font-weight: 600;
    padding: 0.4rem 0.9rem;
    border-radius: 50rem;
    white-space: nowrap;
    text-decoration: none;
    transition: all 0.15s ease;
}
.filter-preset-pill:hover {
    background-color: #e2e8f0;
    color: #0f172a;
}
.spec-chip {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 3px 8px;
    font-size: 0.75rem;
}
</style>

<!-- Header Bar -->
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
        <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
            <i class="bi bi-clock-history text-primary"></i> Service History
        </h4>
        <div class="text-muted text-xs">Completed breakdown tickets, digital job cards, and instant PDF sharing</div>
    </div>
    <div>
        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2 fw-semibold fs-7 shadow-sm">
            <i class="bi bi-check2-all me-1"></i> <?= count($completedCalls) ?> <?= !empty($fromDate) || !empty($toDate) || $cleanPreset === 'all' ? 'Found' : 'Records Shown' ?>
        </span>
    </div>
</div>

<!-- Modern Filter Toolbar Section -->
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
    <div class="card-body p-3 p-md-4">
        <!-- Preset Quick Filter Pills -->
        <div class="d-flex align-items-center gap-2 overflow-x-auto pb-2 mb-3 no-scrollbar">
            <a href="<?= BASE_URL ?>/engineer/history" 
               class="filter-preset-pill <?= empty($cleanPreset) && empty($fromDate) && empty($toDate) ? 'bg-primary text-white shadow-sm' : 'bg-light text-secondary border' ?>">
                <i class="bi bi-clock me-1"></i> Last 10 (Default)
            </a>
            <a href="<?= BASE_URL ?>/engineer/history?preset=today" 
               class="filter-preset-pill <?= $cleanPreset === 'today' ? 'bg-primary text-white shadow-sm' : 'bg-light text-secondary border' ?>">
                Today
            </a>
            <a href="<?= BASE_URL ?>/engineer/history?preset=7days" 
               class="filter-preset-pill <?= $cleanPreset === '7days' ? 'bg-primary text-white shadow-sm' : 'bg-light text-secondary border' ?>">
                Last 7 Days
            </a>
            <a href="<?= BASE_URL ?>/engineer/history?preset=this_month" 
               class="filter-preset-pill <?= $cleanPreset === 'this_month' ? 'bg-primary text-white shadow-sm' : 'bg-light text-secondary border' ?>">
                This Month
            </a>
            <a href="<?= BASE_URL ?>/engineer/history?preset=30days" 
               class="filter-preset-pill <?= $cleanPreset === '30days' ? 'bg-primary text-white shadow-sm' : 'bg-light text-secondary border' ?>">
                Last 30 Days
            </a>
            <a href="<?= BASE_URL ?>/engineer/history?preset=all" 
               class="filter-preset-pill <?= $cleanPreset === 'all' ? 'bg-primary text-white shadow-sm' : 'bg-light text-secondary border' ?>">
                All Time (<?= $totalAllTime ?? count($completedCalls) ?>)
            </a>
        </div>

        <!-- Date Range Filter Form -->
        <div class="pt-3 border-top">
            <form method="GET" action="<?= BASE_URL ?>/engineer/history" class="row g-2 align-items-end">
                <div class="col-12 col-sm-4 col-md-3">
                    <label class="form-label text-xxs fw-bold text-muted text-uppercase mb-1">From Date</label>
                    <input type="date" name="from_date" class="form-control form-control-sm rounded-3" 
                           value="<?= htmlspecialchars($fromDate ?? '') ?>" max="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-12 col-sm-4 col-md-3">
                    <label class="form-label text-xxs fw-bold text-muted text-uppercase mb-1">To Date</label>
                    <input type="date" name="to_date" class="form-control form-control-sm rounded-3" 
                           value="<?= htmlspecialchars($toDate ?? '') ?>" max="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-12 col-sm-4 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill w-100 fw-bold px-3 py-2 shadow-sm d-flex align-items-center justify-content-center gap-1">
                        <i class="bi bi-funnel-fill"></i> <span>Filter Range</span>
                    </button>
                    <?php if (!empty($fromDate) || !empty($toDate) || !empty($cleanPreset)): ?>
                        <a href="<?= BASE_URL ?>/engineer/history" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-2 d-flex align-items-center" title="Reset Filters">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Active Filter Status Indicator -->
<div class="d-flex justify-content-between align-items-center mb-3 px-1 text-xs text-muted">
    <div>
        <?php if (!empty($fromDate) || !empty($toDate)): ?>
            <span class="text-dark fw-bold"><i class="bi bi-calendar-check me-1 text-primary"></i> Date Range:</span> 
            <?= !empty($fromDate) ? date('d M Y', strtotime($fromDate)) : 'Start' ?> &rarr; <?= !empty($toDate) ? date('d M Y', strtotime($toDate)) : 'Today' ?>
        <?php elseif ($cleanPreset === 'all'): ?>
            <span class="text-dark fw-bold"><i class="bi bi-infinity me-1 text-primary"></i> All Time History:</span> <?= count($completedCalls) ?> total records
        <?php elseif (!empty($cleanPreset)): ?>
            <span class="text-dark fw-bold"><i class="bi bi-funnel me-1 text-primary"></i> Filter:</span> <?= ucfirst(str_replace('_', ' ', $cleanPreset)) ?>
        <?php else: ?>
            <span class="text-dark fw-bold"><i class="bi bi-clock me-1 text-primary"></i> Default:</span> Displaying last 10 completed cases
        <?php endif; ?>
    </div>
    <span class="fw-semibold text-secondary"><?= count($completedCalls) ?> Records</span>
</div>

<!-- Completed Service Records Grid -->
<?php if (empty($completedCalls)): ?>
    <div class="card p-5 text-center border-0 shadow-sm rounded-4 bg-white">
        <div class="text-muted mb-3">
            <i class="bi bi-clipboard2-x display-4 text-secondary opacity-50"></i>
        </div>
        <h5 class="fw-bold text-dark mb-1">No Completed Records Found</h5>
        <p class="text-muted small mb-3">No resolved cases matched the selected date range or preset.</p>
        <div>
            <a href="<?= BASE_URL ?>/engineer/history" class="btn btn-sm btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm">
                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset to Default (Last 10)
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="row g-3 g-md-4">
        <?php foreach ($completedCalls as $c): ?>
            <?php
            $cardJobCardUrl = BASE_URL . "/engineer/calls/" . $c['id'] . "/print-jobcard";
            $cardDownloadUrl = BASE_URL . "/engineer/calls/" . $c['id'] . "/print-jobcard?action=download";
            $cardShareUrl = BASE_URL . "/engineer/calls/" . $c['id'] . "/print-jobcard?action=share";
            ?>
            <div class="col-12 col-md-6 col-xxl-4 d-flex">
                <div class="history-card w-100 d-flex flex-column overflow-hidden">
                    <div class="p-3 p-md-4 d-flex flex-column h-100">
                        <!-- Top Row: Ticket Number & Status Badge -->
                        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-1">
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-bold text-primary fs-6 text-nowrap"><?= htmlspecialchars($c['call_number']) ?></span>
                                <?php if (!empty($c['report_number'])): ?>
                                    <span class="badge bg-light text-secondary border rounded-pill text-xxs">
                                        <i class="bi bi-file-earmark-medical me-1 text-info"></i><?= htmlspecialchars($c['report_number']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 text-xs fw-semibold">
                                <i class="bi bi-check2-circle me-1"></i><?= htmlspecialchars($c['status']) ?>
                            </span>
                        </div>

                        <!-- Customer & Site -->
                        <h6 class="fw-bold text-dark mb-1 fs-6"><?= htmlspecialchars($c['company_name'] ?? 'Client') ?></h6>
                        <div class="text-xs text-muted mb-3 d-flex align-items-center">
                            <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                            <span><?= htmlspecialchars($c['location_name'] ?? '') ?><?= !empty($c['city']) ? ' &bull; ' . htmlspecialchars($c['city']) : '' ?></span>
                        </div>

                        <!-- Machine & Hardware Specs Chips -->
                        <?php if (!empty($c['asset_code']) || !empty($c['make']) || !empty($c['serial_number'])): ?>
                            <div class="d-flex flex-wrap gap-1 mb-3">
                                <?php if (!empty($c['make']) || !empty($c['model'])): ?>
                                    <span class="spec-chip text-dark fw-semibold">
                                        <i class="bi bi-pc-display me-1 text-primary"></i><?= htmlspecialchars(trim(($c['make'] ?? '') . ' ' . ($c['model'] ?? ''))) ?>
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($c['asset_code'])): ?>
                                    <span class="spec-chip text-muted">
                                        Asset: <strong class="text-dark"><?= htmlspecialchars($c['asset_code']) ?></strong>
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($c['serial_number'])): ?>
                                    <span class="spec-chip text-muted">
                                        S/N: <code class="text-dark"><?= htmlspecialchars($c['serial_number']) ?></code>
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Issue Description & Diagnosis Excerpt -->
                        <div class="p-3 rounded-3 bg-light mb-3 text-xs border border-light-subtle flex-grow-1">
                            <div class="text-muted text-xxs fw-bold text-uppercase mb-1">Reported Issue & Resolution:</div>
                            <div class="text-dark fw-semibold mb-2"><?= htmlspecialchars($c['reported_issue'] ?? 'General Fault') ?></div>
                            <?php if (!empty($c['action_taken'])): ?>
                                <div class="text-secondary text-xxs">
                                    <i class="bi bi-wrench me-1 text-success"></i><?= htmlspecialchars(mb_strimwidth($c['action_taken'], 0, 120, '...')) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Resolved Timestamp & Sign-off Details -->
                        <div class="d-flex flex-wrap justify-content-between align-items-center text-xs text-muted mb-3 pt-1">
                            <div>
                                <i class="bi bi-clock-history me-1 text-primary"></i>
                                Resolved: <strong><?= date('d M Y, h:i A', strtotime($c['resolved_at'] ?? $c['updated_at'])) ?></strong>
                            </div>
                            <?php if (!empty($c['customer_signed_name'])): ?>
                                <div class="text-success fw-semibold text-xxs">
                                    <i class="bi bi-pen-fill me-1"></i> Signed by <?= htmlspecialchars($c['customer_signed_name']) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Action Buttons Toolbar -->
                        <div class="d-flex align-items-center gap-2 pt-3 border-top mt-auto">
                            <a href="<?= $cardDownloadUrl ?>" 
                               target="_blank" 
                               class="btn btn-pdf-action btn-sm rounded-pill flex-grow-1 fw-bold shadow-sm d-inline-flex align-items-center justify-content-center py-2 text-xs"
                               title="Download PDF Service Report">
                                <i class="bi bi-file-earmark-pdf-fill text-danger me-1 fs-6"></i> <span>Download PDF</span>
                            </a>

                            <a href="<?= $cardShareUrl ?>" 
                               target="_blank" 
                               class="btn btn-whatsapp-action btn-sm rounded-pill flex-grow-1 fw-bold shadow-sm d-inline-flex align-items-center justify-content-center py-2 text-xs"
                               title="Share PDF Report on WhatsApp">
                                <i class="bi bi-whatsapp me-1 fs-6"></i> <span>WhatsApp PDF</span>
                            </a>

                            <a href="<?= BASE_URL ?>/engineer/calls/<?= $c['id'] ?>" 
                               class="btn btn-outline-primary btn-sm rounded-pill px-3 py-2 fw-bold d-inline-flex align-items-center justify-content-center"
                               title="View Full Call Details">
                                <i class="bi bi-eye"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
