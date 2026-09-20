<?php
/**
 * Clean Auth & Installer Layout
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Authentication' ?> | <?= htmlspecialchars(Setting::get('company_name', 'AMC Portal')) ?></title>
    <meta name="base-url" content="<?= BASE_URL ?>">
    <meta name="csrf-token" content="<?= Request::csrfToken() ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/custom.css">
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 py-5" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <?php if (!empty($flashSuccess)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $flashSuccess ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if (!empty($flashError)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $flashError ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?= $content ?? '' ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/app.js"></script>
</body>
</html>
