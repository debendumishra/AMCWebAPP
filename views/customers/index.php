<?php
/**
 * Customer Directory View
 */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Customer Enterprise Master</h4>
        <p class="text-muted small mb-0">Manage corporate clients, branch locations, and registered hardware assets.</p>
    </div>
    <div>
        <a href="<?= BASE_URL ?>/customers/create" class="btn btn-primary shadow-sm">
            <i class="bi bi-plus-circle me-1"></i> Add New Customer
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white py-3">
        <div class="row align-items-center">
            <div class="col-md-6">
                <span class="fw-bold text-dark"><i class="bi bi-buildings text-primary me-2"></i>All Registered Customers (<?= count($customers) ?>)</span>
            </div>
            <div class="col-md-6 text-md-end">
                <input type="text" id="filterCustomer" class="form-control form-control-sm d-inline-block w-auto" placeholder="Filter customers...">
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="customerTable">
            <thead>
                <tr>
                    <th>Customer Code & Company</th>
                    <th>Primary Contact</th>
                    <th>Phone / WhatsApp</th>
                    <th>City & State</th>
                    <th>Type</th>
                    <th>Locations</th>
                    <th>Assets</th>
                    <th>Open Calls</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($customers)): ?>
                    <tr><td colspan="9" class="text-center text-muted p-4">No customers registered yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($customers as $cust): ?>
                        <tr>
                            <td>
                                <a href="<?= BASE_URL ?>/customers/<?= $cust['id'] ?>" class="fw-bold text-primary text-decoration-none">
                                    <?= htmlspecialchars($cust['company_name']) ?>
                                </a>
                                <div class="text-xs text-muted"><code><?= $cust['customer_code'] ?></code></div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark"><?= htmlspecialchars($cust['contact_person']) ?></div>
                                <div class="text-xs text-muted"><?= htmlspecialchars($cust['designation'] ?? 'Manager') ?></div>
                            </td>
                            <td>
                                <div><a href="tel:<?= $cust['mobile'] ?>" class="text-decoration-none text-dark fw-semibold"><?= $cust['mobile'] ?></a></div>
                                <div class="text-xs">
                                    <a href="https://wa.me/91<?= $cust['mobile'] ?>" target="_blank" class="text-success text-decoration-none"><i class="bi bi-whatsapp"></i> Chat</a>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($cust['city'] ?? 'Mumbai') ?>, <?= htmlspecialchars($cust['state'] ?? 'MH') ?></td>
                            <td>
                                <span class="badge <?= $cust['customer_type'] === 'AMC' ? 'bg-success-subtle text-success' : 'bg-primary-subtle text-primary' ?>">
                                    <?= $cust['customer_type'] ?>
                                </span>
                            </td>
                            <td><span class="badge bg-light text-dark border"><?= $cust['locations_count'] ?> Locations</span></td>
                            <td><span class="badge bg-secondary-subtle text-secondary"><?= $cust['machines_count'] ?> Machines</span></td>
                            <td>
                                <?php if ($cust['open_calls_count'] > 0): ?>
                                    <span class="badge bg-danger"><?= $cust['open_calls_count'] ?> Active</span>
                                <?php else: ?>
                                    <span class="badge bg-light text-muted border">0</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="<?= BASE_URL ?>/customers/<?= $cust['id'] ?>" class="btn btn-sm btn-outline-primary" title="View Profile">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.getElementById('filterCustomer')?.addEventListener('input', function(e) {
    const q = e.target.value.toLowerCase();
    document.querySelectorAll('#customerTable tbody tr').forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>
