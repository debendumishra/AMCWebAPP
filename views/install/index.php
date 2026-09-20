<?php
/**
 * Installation Wizard View
 */
?>
<div class="card shadow-lg border-0 rounded-4 overflow-hidden">
    <div class="card-header bg-dark text-white p-4 text-center border-0">
        <i class="bi bi-shield-check text-primary display-4 d-block mb-2"></i>
        <h4 class="fw-bold mb-1"><?= htmlspecialchars(Setting::get('company_name', 'AMC System')) ?> Setup</h4>
        <p class="text-white-50 small mb-0">Database Schema & Environment Configuration</p>
    </div>
    <div class="card-body p-4">
        <h6 class="fw-bold text-uppercase fs-8 text-muted mb-3"><i class="bi bi-cpu me-1"></i> System Prerequisites</h6>
        <ul class="list-group list-group-flush border rounded-3 mb-4 small">
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <span>PHP Version (&ge; 8.0)</span>
                <span class="badge <?= version_compare($phpVersion, '8.0.0', '>=') ? 'bg-success' : 'bg-danger' ?>"><?= $phpVersion ?></span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <span>PDO MySQL Driver</span>
                <span class="badge <?= $extensions['pdo_mysql'] ? 'bg-success' : 'bg-danger' ?>"><?= $extensions['pdo_mysql'] ? 'Enabled' : 'Missing' ?></span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <span>JSON & Mbstring</span>
                <span class="badge <?= ($extensions['json'] && $extensions['mbstring']) ? 'bg-success' : 'bg-danger' ?>">Enabled</span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <span>Database Connection (<code><?= DB_NAME ?></code>)</span>
                <span class="badge <?= $dbStatus ? 'bg-success' : 'bg-warning text-dark' ?>"><?= $dbStatus ? 'Connected (' . $tablesCount . ' Tables)' : 'Not Created Yet' ?></span>
            </li>
        </ul>

        <?php if ($tablesCount === 0 || !$hasDemoData): ?>
            <div class="p-3 bg-light rounded-3 border mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="seed_demo" checked>
                    <label class="form-check-label small fw-bold" for="seed_demo">
                        Install Demo Enterprise Data
                    </label>
                </div>
                <div class="text-muted text-xs mt-1">Includes sample customers (TechCorp, Zenith), machines, AMC contracts, spare inventory, and active service calls.</div>
            </div>

            <button type="button" id="btn-install" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm" onclick="runInstallation()">
                <i class="bi bi-play-circle-fill me-2"></i> Initialize System Database
            </button>
        <?php else: ?>
            <div class="alert alert-success d-flex align-items-center gap-2 mb-3">
                <i class="bi bi-check-circle-fill fs-4"></i>
                <div class="small">
                    <strong>System Installed & Ready!</strong><br>
                    Database contains <?= $tablesCount ?> initialized tables with demo data.
                </div>
            </div>

            <div class="d-grid gap-2">
                <a href="<?= BASE_URL ?>/login" class="btn btn-primary btn-lg fw-bold">
                    <i class="bi bi-box-arrow-in-right me-2"></i> Go to Login Screen
                </a>
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="purgeDemoData()">
                    <i class="bi bi-trash3 me-1"></i> Remove Demo Data (Keep Masters)
                </button>
            </div>
        <?php endif; ?>

        <div class="mt-4 pt-3 border-top text-center text-muted small">
            <strong>Default Credentials:</strong><br>
            Admin: <code>admin@amc.local</code> | Password: <code>password123</code><br>
            Engineer: <code>rahul.engineer@amc.local</code> | Customer: <code>ithead@techcorp.com</code>
        </div>
    </div>
</div>

<script>
async function runInstallation() {
    const btn = document.getElementById('btn-install');
    const seedDemo = document.getElementById('seed_demo')?.checked ? 1 : 0;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating tables & seeding data...';

    const formData = new FormData();
    formData.append('seed_demo', seedDemo);
    formData.append('csrf_token', App.csrfToken);

    const res = await App.fetch('install/migrate', {
        method: 'POST',
        body: formData
    });

    if (res.success) {
        App.toast(res.message, 'success');
        setTimeout(() => window.location.reload(), 1500);
    } else {
        App.toast(res.message || 'Installation error', 'danger');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-play-circle-fill me-2"></i> Retry Installation';
    }
}

async function purgeDemoData() {
    if (!confirm('Are you sure you want to remove all demo customers, calls, contracts, and inventory transactions?')) return;

    const formData = new FormData();
    formData.append('csrf_token', App.csrfToken);

    const res = await App.fetch('install/purge', {
        method: 'POST',
        body: formData
    });

    if (res.success) {
        App.toast(res.message, 'success');
        setTimeout(() => window.location.reload(), 1500);
    } else {
        App.toast(res.message || 'Error purging data', 'danger');
    }
}
</script>
