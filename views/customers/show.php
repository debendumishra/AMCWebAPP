<?php
/**
 * Customer Profile & Multi-Location Detail View
 */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <div class="d-flex align-items-center gap-2">
            <h4 class="fw-bold mb-0"><?= htmlspecialchars($customer['company_name']) ?></h4>
            <span class="badge bg-primary"><?= $customer['customer_code'] ?></span>
            <span class="badge <?= $customer['customer_type'] === 'AMC' ? 'bg-success' : 'bg-info' ?>"><?= $customer['customer_type'] ?></span>
        </div>
        <p class="text-muted small mb-0 mt-1">
            <i class="bi bi-person"></i> <?= htmlspecialchars($customer['contact_person']) ?> (<?= htmlspecialchars($customer['designation'] ?? 'Manager') ?>) | 
            <i class="bi bi-telephone"></i> <a href="tel:<?= $customer['mobile'] ?>"><?= $customer['mobile'] ?></a> | 
            <i class="bi bi-envelope"></i> <?= htmlspecialchars($customer['email']) ?>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/calls/create?customer_id=<?= $customer['id'] ?>" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle me-1"></i> Log Ticket
        </a>
        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addLocationModal">
            <i class="bi bi-geo-alt-fill me-1"></i> Add Branch Location
        </button>
    </div>
</div>

<ul class="nav nav-tabs mb-4" id="customerTab" role="tablist">
    <li class="nav-item">
        <button class="nav-link active fw-bold" id="locations-tab" data-bs-toggle="tab" data-bs-target="#locations-pane">
            <i class="bi bi-geo-alt me-1"></i> Locations (<?= count($locations) ?>)
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link fw-bold" id="machines-tab" data-bs-toggle="tab" data-bs-target="#machines-pane">
            <i class="bi bi-pc-display me-1"></i> IT Assets (<?= count($machines) ?>)
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link fw-bold" id="calls-tab" data-bs-toggle="tab" data-bs-target="#calls-pane">
            <i class="bi bi-headset me-1"></i> Service Calls (<?= count($calls) ?>)
        </button>
    </li>
</ul>

<div class="tab-content" id="customerTabContent">
    <!-- Tab 1: Locations -->
    <div class="tab-pane fade show active" id="locations-pane">
        <div class="row g-3">
            <?php foreach ($locations as $loc): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 card-hover">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-dark"><i class="bi bi-pin-map text-primary me-2"></i><?= htmlspecialchars($loc['location_name']) ?></span>
                            <span class="badge bg-light text-dark border"><?= $loc['machines_count'] ?? 0 ?> Assets</span>
                        </div>
                        <div class="card-body">
                            <p class="small text-muted mb-2">
                                <i class="bi bi-building"></i> <?= htmlspecialchars($loc['address']) ?><br>
                                <?= htmlspecialchars($loc['city']) ?>, <?= htmlspecialchars($loc['state']) ?> - <?= htmlspecialchars($loc['pincode']) ?>
                            </p>
                            <div class="small mb-2">
                                <strong>Contact:</strong> <?= htmlspecialchars($loc['contact_person'] ?? $customer['contact_person']) ?> (<?= $loc['mobile'] ?? $customer['mobile'] ?>)
                            </div>
                            <?php if (!empty($loc['latitude']) && !empty($loc['longitude'])): ?>
                                <a href="https://maps.google.com/?q=<?= $loc['latitude'] ?>,<?= $loc['longitude'] ?>" target="_blank" class="btn btn-xs btn-outline-info">
                                    <i class="bi bi-map"></i> View on Google Maps
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Tab 2: Machines -->
    <div class="tab-pane fade" id="machines-pane">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <span class="fw-bold">All Registered Hardware & IT Assets</span>
                <a href="<?= BASE_URL ?>/machines/create?customer_id=<?= $customer['id'] ?>" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-plus-lg me-1"></i> Register Asset
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Asset Tag / Code</th>
                            <th>Device Details</th>
                            <th>Location / Dept</th>
                            <th>Assigned User</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($machines)): ?>
                            <tr><td colspan="6" class="text-center text-muted p-4">No machines registered under this customer yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($machines as $m): ?>
                                <tr>
                                    <td>
                                        <a href="<?= BASE_URL ?>/machines/<?= $m['id'] ?>" class="fw-bold text-primary">
                                            <?= $m['asset_code'] ?>
                                        </a>
                                        <?php if (!empty($m['asset_tag'])): ?>
                                            <div class="text-xs text-muted">Tag: <?= htmlspecialchars($m['asset_tag']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= htmlspecialchars($m['make'] . ' ' . $m['model']) ?></div>
                                        <div class="text-xs text-muted">S/N: <code><?= htmlspecialchars($m['serial_number']) ?></code></div>
                                    </td>
                                    <td>
                                        <div><?= htmlspecialchars($m['location_name']) ?></div>
                                        <div class="text-xs text-muted"><?= htmlspecialchars($m['department'] ?? 'General') ?></div>
                                    </td>
                                    <td><?= htmlspecialchars($m['assigned_employee'] ?? 'Shared / None') ?></td>
                                    <td>
                                        <span class="badge <?= $m['status'] === 'UNDER_AMC' ? 'bg-success' : 'bg-secondary' ?>">
                                            <?= $m['status'] ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= BASE_URL ?>/machines/<?= $m['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tab 3: Calls -->
    <div class="tab-pane fade" id="calls-pane">
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Call Number</th>
                            <th>Type / Priority</th>
                            <th>Reported Issue</th>
                            <th>Technician</th>
                            <th>Status</th>
                            <th>Logged At</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($calls)): ?>
                            <tr><td colspan="7" class="text-center text-muted p-4">No service calls recorded for this customer.</td></tr>
                        <?php else: ?>
                            <?php foreach ($calls as $c): ?>
                                <tr>
                                    <td><a href="<?= BASE_URL ?>/calls/<?= $c['id'] ?>" class="fw-bold"><?= $c['call_number'] ?></a></td>
                                    <td>
                                        <span class="badge bg-light text-dark border"><?= $c['call_type'] ?></span>
                                        <span class="badge <?= $c['priority'] === 'CRITICAL' ? 'bg-danger' : 'bg-info' ?>"><?= $c['priority'] ?></span>
                                    </td>
                                    <td><?= htmlspecialchars(substr($c['reported_issue'], 0, 50)) ?>...</td>
                                    <td><?= htmlspecialchars($c['engineer_name'] ?? 'Unassigned') ?></td>
                                    <td><span class="badge-status badge-status-<?= strtolower($c['status']) ?>"><?= $c['status'] ?></span></td>
                                    <td><span class="text-muted text-xs"><?= $c['created_at'] ?></span></td>
                                    <td class="text-end"><a href="<?= BASE_URL ?>/calls/<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-arrow-right"></i></a></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Branch Location Modal -->
<div class="modal fade" id="addLocationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <form method="POST" action="<?= BASE_URL ?>/customers/<?= $customer['id'] ?>/locations">
                <input type="hidden" name="csrf_token" value="<?= Request::csrfToken() ?>">
                <div class="modal-header bg-dark text-white p-3">
                    <h6 class="modal-title fw-bold"><i class="bi bi-geo-alt-fill text-primary me-2"></i>Add Branch Service Location</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Location / Branch Name *</label>
                        <input type="text" name="location_name" class="form-control" required placeholder="e.g. Andheri R&D Branch">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Address *</label>
                        <textarea name="address" class="form-control" rows="2" required placeholder="Floor, Wing, Street, Area"></textarea>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">City *</label>
                            <input type="text" name="city" class="form-control" value="Mumbai" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">PIN Code *</label>
                            <input type="text" name="pincode" class="form-control" value="400001" required>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Branch Contact Person</label>
                            <input type="text" name="contact_person" class="form-control" placeholder="Site Manager">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Mobile</label>
                            <input type="text" name="mobile" class="form-control" placeholder="98XXXXXXXX">
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label small fw-bold">GPS Latitude</label>
                            <input type="text" name="latitude" class="form-control" placeholder="19.1234">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">GPS Longitude</label>
                            <input type="text" name="longitude" class="form-control" placeholder="72.8456">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light p-3">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Save Location</button>
                </div>
            </form>
        </div>
    </div>
</div>
