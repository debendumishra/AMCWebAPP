<?php
/**
 * 403 Forbidden Error View
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>403 Forbidden | <?= htmlspecialchars(Setting::get('company_name', 'AMC System')) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 bg-light text-center">
    <div class="card p-5 shadow border-0 rounded-4" style="max-width: 500px;">
        <i class="bi bi-shield-lock-fill text-danger display-1 mb-3"></i>
        <h2 class="fw-bold">403 - Access Forbidden</h2>
        <p class="text-muted">You do not have administrative permission to access this module. Please contact your system administrator.</p>
        <a href="<?= BASE_URL ?>/" class="btn btn-primary rounded-pill mt-3"><i class="bi bi-house-door me-1"></i> Return to Safety</a>
    </div>
</body>
</html>
