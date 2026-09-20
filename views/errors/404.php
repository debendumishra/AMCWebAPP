<?php
/**
 * 404 Not Found Error View
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>404 Page Not Found | <?= htmlspecialchars(Setting::get('company_name', 'AMC System')) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 bg-light text-center">
    <div class="card p-5 shadow border-0 rounded-4" style="max-width: 500px;">
        <i class="bi bi-compass-fill text-primary display-1 mb-3"></i>
        <h2 class="fw-bold">404 - Page Not Found</h2>
        <p class="text-muted">The requested page or ticket resource could not be found or has been moved.</p>
        <a href="<?= BASE_URL ?>/" class="btn btn-primary rounded-pill mt-3"><i class="bi bi-arrow-left me-1"></i> Return to Dashboard</a>
    </div>
</body>
</html>
