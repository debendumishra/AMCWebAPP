<?php
/**
 * Ultra-Clean Corporate Digital Field Service Job Card & Printable PDF
 */
$isCompleted = in_array($call['status'] ?? '', ['RESOLVED', 'CLOSED']) 
    || !empty($serviceReport['signed_at']) 
    || !empty($serviceReport['customer_signature_data'])
    || !empty($call['resolved_at'])
    || !empty($call['closed_at']);

$isPreview = !$isCompleted && !empty($_GET['preview']);
$pdfFileName = 'JobCard_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $call['call_number']) . '.pdf';

$cleanMobile = preg_replace('/[^0-9]/', '', $call['caller_mobile'] ?? $call['customer_mobile'] ?? '');
if (strlen($cleanMobile) === 10) {
    $cleanMobile = '91' . $cleanMobile;
}

$waFriendlyText = "Hello, please find attached the Field Service Job Card for Ticket #" . $call['call_number'] . " (" . ($call['company_name'] ?? 'Client') . ").";
$waDirectUrl = "https://api.whatsapp.com/send?text=" . urlencode($waFriendlyText) . (!empty($cleanMobile) ? "&phone=" . $cleanMobile : "");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Card - <?= htmlspecialchars($call['call_number']) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- html2pdf.js for Client-Side Direct Physical PDF File Generation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        :root {
            --primary-navy: #0f172a;
            --accent-blue: #2563eb;
            --border-color: #cbd5e1;
            --bg-light: #f8fafc;
        }
        body {
            background-color: #f1f5f9;
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 12.5px;
            margin: 0;
            padding: 0;
        }
        .report-page-wrapper {
            max-width: 860px;
            margin: 20px auto;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 36px 40px;
            box-shadow: 0 4px 24px rgba(15, 23, 42, 0.08);
            position: relative;
        }
        .report-header {
            border-bottom: 2px solid var(--primary-navy);
            padding-bottom: 16px;
            margin-bottom: 16px;
        }
        .company-logo-badge {
            width: 46px;
            height: 46px;
            background: var(--accent-blue);
            color: #ffffff;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }
        .info-card {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            background: #ffffff;
            height: 100%;
            overflow: hidden;
        }
        .info-card-header {
            background: var(--bg-light);
            border-bottom: 1px solid var(--border-color);
            padding: 6px 12px;
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--primary-navy);
        }
        .info-card-body {
            padding: 10px 12px;
        }
        .meta-strip {
            background: var(--bg-light);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 8px 14px;
            margin-bottom: 16px;
        }
        .table-custom {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 0;
        }
        .table-custom th {
            background: var(--bg-light);
            color: var(--primary-navy);
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
            border-bottom: 1px solid var(--border-color);
            padding: 8px 10px;
        }
        .table-custom td {
            padding: 8px 10px;
            font-size: 12px;
            border-bottom: 1px solid #e2e8f0;
        }
        .signature-box {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 12px;
            background: #ffffff;
            height: 100%;
        }
        .signature-preview-img {
            max-height: 70px;
            max-width: 190px;
            object-fit: contain;
            display: block;
            margin: 6px 0;
        }
        .preview-watermark {
            position: absolute;
            top: 45%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 58px;
            font-weight: 900;
            color: rgba(220, 38, 38, 0.12);
            pointer-events: none;
            text-transform: uppercase;
            border: 4px dashed rgba(220, 38, 38, 0.25);
            padding: 8px 36px;
            border-radius: 16px;
            letter-spacing: 4px;
            white-space: nowrap;
            z-index: 10;
        }
        .btn-whatsapp-action {
            background-color: #25D366;
            color: #ffffff !important;
            border: none;
            font-weight: 600;
        }
        .btn-whatsapp-action:hover {
            background-color: #1eb956;
        }
        @media print {
            body {
                background: #ffffff !important;
                font-size: 11.5px;
            }
            .report-page-wrapper {
                box-shadow: none !important;
                padding: 0 !important;
                margin: 0 !important;
                max-width: 100% !important;
                border: none !important;
                border-radius: 0 !important;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<!-- Floating / Top Action Toolbar -->
<div class="container my-3 no-print">
    <div class="card border-0 shadow-sm rounded-4 p-2 bg-white">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex align-items-center gap-2">
                <span class="badge <?= $isPreview ? 'bg-warning text-dark' : 'bg-success' ?> rounded-pill px-3 py-2 fw-semibold">
                    <i class="bi <?= $isPreview ? 'bi-eye-fill' : 'bi-patch-check-fill' ?> me-1"></i>
                    <?= $isPreview ? 'DRAFT PREVIEW (Awaiting Signature)' : 'SIGNED & VERIFIED' ?>
                </span>
                <span class="text-muted small">Ticket: <strong><?= htmlspecialchars($call['call_number']) ?></strong></span>
            </div>

            <div class="d-flex align-items-center gap-2">
                <!-- 1. Download Physical PDF File Directly -->
                <button type="button" class="btn btn-dark btn-sm px-3 rounded-pill fw-bold shadow-sm" onclick="downloadPhysicalPDF()" id="btnDownload">
                    <i class="bi bi-file-earmark-pdf-fill text-danger me-1"></i> Download PDF
                </button>

                <!-- 2. Share Physical PDF File Directly -->
                <button type="button" class="btn btn-whatsapp-action btn-sm px-3 rounded-pill shadow-sm" onclick="sharePhysicalPDF()" id="btnShare">
                    <i class="bi bi-whatsapp me-1"></i> Share PDF
                </button>

                <!-- 3. Print Option -->
                <button onclick="window.print()" class="btn btn-outline-primary btn-sm px-3 rounded-pill fw-bold">
                    <i class="bi bi-printer-fill me-1"></i> Print
                </button>

                <!-- 4. Close Window -->
                <button onclick="window.close()" class="btn btn-outline-secondary btn-sm px-2 rounded-circle" title="Close">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Main Printable Document Container -->
<div class="report-page-wrapper" id="jobCardDocument">
    <?php if ($isPreview): ?>
        <div class="preview-watermark">PREVIEW DRAFT</div>
    <?php endif; ?>

    <!-- 1. Company Brand Header -->
    <div class="report-header">
        <div class="row align-items-center">
            <div class="col-8 d-flex align-items-center gap-3">
                <div class="company-logo-badge">
                    <i class="bi bi-shield-check"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($settings['company_name'] ?? 'Apex IT Solutions & AMC Services') ?></h5>
                    <div class="text-muted text-xs"><?= htmlspecialchars($settings['company_address'] ?? 'Tower B, 4th Floor, Tech Park, Mumbai, MH') ?></div>
                    <div class="text-muted text-xxs mt-1">
                        <span>GSTIN: <strong><?= htmlspecialchars($settings['company_gstin'] ?? '27AAACA1234A1Z5') ?></strong></span> | 
                        <span>Phone: <?= htmlspecialchars($settings['company_phone'] ?? '+91 98765 43210') ?></span> | 
                        <span>Email: <?= htmlspecialchars($settings['company_email'] ?? 'support@apexit-amc.com') ?></span>
                    </div>
                </div>
            </div>
            <div class="col-4 text-end">
                <h6 class="fw-bold text-primary mb-1">FIELD SERVICE REPORT</h6>
                <div class="text-muted text-xxs">DIGITAL JOB CARD & SIGN-OFF</div>
                <span class="badge bg-primary rounded-pill px-2 py-1 text-xxs mt-1"><?= htmlspecialchars($call['call_type'] ?? 'AMC') ?> CALL</span>
            </div>
        </div>
    </div>

    <!-- 2. Metadata Quick Strip -->
    <div class="meta-strip">
        <div class="row g-2 text-xs">
            <div class="col-3">
                <span class="text-muted d-block text-xxs text-uppercase fw-bold">Report Number</span>
                <strong class="text-dark"><?= htmlspecialchars($serviceReport['report_number'] ?? 'DRAFT-PENDING') ?></strong>
            </div>
            <div class="col-3">
                <span class="text-muted d-block text-xxs text-uppercase fw-bold">Call Ticket #</span>
                <strong class="text-primary"><?= htmlspecialchars($call['call_number']) ?></strong>
            </div>
            <div class="col-3">
                <span class="text-muted d-block text-xxs text-uppercase fw-bold">Call Logged Date</span>
                <span><?= date('d M Y, h:i A', strtotime($call['created_at'])) ?></span>
            </div>
            <div class="col-3 text-end">
                <span class="text-muted d-block text-xxs text-uppercase fw-bold">Service Completed</span>
                <strong class="text-success"><?= !empty($serviceReport['signed_at']) ? date('d M Y, h:i A', strtotime($serviceReport['signed_at'])) : date('d M Y, h:i A') ?></strong>
            </div>
        </div>
    </div>

    <!-- 3. Customer & Asset Two-Column Grid -->
    <div class="row g-3 mb-3">
        <!-- Customer Info Card -->
        <div class="col-6">
            <div class="info-card">
                <div class="info-card-header d-flex justify-content-between">
                    <span><i class="bi bi-building me-1"></i> Customer & Site Details</span>
                </div>
                <div class="info-card-body text-xs">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted p-1" style="width: 38%;">Company:</td>
                            <td class="p-1 fw-bold text-dark"><?= htmlspecialchars($call['company_name']) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted p-1">Contact Person:</td>
                            <td class="p-1 fw-semibold"><?= htmlspecialchars($call['caller_name']) ?> <?= !empty($call['caller_mobile']) ? '(' . htmlspecialchars($call['caller_mobile']) . ')' : '' ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted p-1">Site Location:</td>
                            <td class="p-1"><?= htmlspecialchars($call['location_name']) ?> - <?= htmlspecialchars($call['location_address'] ?? '') ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted p-1">Contract #:</td>
                            <td class="p-1"><?= !empty($call['contract_number']) ? htmlspecialchars($call['contract_number']) . ' (' . ($call['contract_type_name'] ?? 'AMC') . ')' : '<span class="text-muted">Standard / Paid Service</span>' ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Asset Info Card -->
        <div class="col-6">
            <div class="info-card">
                <div class="info-card-header d-flex justify-content-between">
                    <span><i class="bi bi-pc-display me-1"></i> Machine / Equipment Details</span>
                </div>
                <div class="info-card-body text-xs">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted p-1" style="width: 38%;">Asset Code:</td>
                            <td class="p-1 fw-bold text-primary"><?= htmlspecialchars($call['asset_code'] ?? 'N/A') ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted p-1">Make & Model:</td>
                            <td class="p-1 fw-semibold"><?= htmlspecialchars(($call['make'] ?? '') . ' ' . ($call['model'] ?? 'IT Equipment')) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted p-1">Serial Number:</td>
                            <td class="p-1"><code><?= htmlspecialchars($call['serial_number'] ?? 'N/A') ?></code></td>
                        </tr>
                        <tr>
                            <td class="text-muted p-1">Specifications:</td>
                            <td class="p-1 text-muted"><?= htmlspecialchars($call['processor'] ?? 'Standard') ?> | <?= htmlspecialchars($call['ram'] ?? '8GB') ?> | <?= htmlspecialchars($call['storage'] ?? '256GB') ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Problem & Technical Work Summary -->
    <div class="info-card mb-3">
        <div class="info-card-header">
            <i class="bi bi-journal-medical me-1"></i> Problem Diagnosis & Technical Work Summary
        </div>
        <div class="info-card-body text-xs">
            <div class="mb-2">
                <span class="text-muted text-xxs text-uppercase fw-bold d-block">Reported Complaint:</span>
                <div class="p-2 bg-light rounded text-dark"><?= nl2br(htmlspecialchars($call['problem_description'] ?? $call['reported_issue'] ?? 'Technical service inspection')) ?></div>
            </div>
            <div class="row g-2">
                <div class="col-6">
                    <span class="text-muted text-xxs text-uppercase fw-bold d-block">Technical Diagnosis / Findings:</span>
                    <div class="p-2 border rounded bg-white" style="min-height: 52px;"><?= nl2br(htmlspecialchars($serviceReport['diagnosis'] ?? $call['resolution_notes'] ?? 'System inspected and diagnosed.')) ?></div>
                </div>
                <div class="col-6">
                    <span class="text-muted text-xxs text-uppercase fw-bold d-block">Action Taken & Resolution:</span>
                    <div class="p-2 border rounded bg-white" style="min-height: 52px;"><?= nl2br(htmlspecialchars($serviceReport['action_taken'] ?? 'Serviced, tested and certified operational.')) ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. Spare Parts & Components (If any) -->
    <div class="info-card mb-3">
        <div class="info-card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-box-seam me-1"></i> Spare Parts & Components Replaced</span>
            <span class="text-muted text-xxs"><?= count($spares) ?> item(s)</span>
        </div>
        <div class="info-card-body p-0">
            <?php if (empty($spares)): ?>
                <div class="p-2 text-center text-muted text-xs">
                    <i class="bi bi-check-circle me-1 text-success"></i> No spare parts consumed. (Labour / General Maintenance Only)
                </div>
            <?php else: ?>
                <table class="table table-sm table-custom mb-0">
                    <thead>
                        <tr>
                            <th style="width: 5%;">#</th>
                            <th style="width: 45%;">Spare Description</th>
                            <th style="width: 20%;">Part Code / SKU</th>
                            <th style="width: 15%;" class="text-center">Qty</th>
                            <th style="width: 15%;" class="text-end">Coverage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($spares as $i => $sp): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($sp['spare_name']) ?></strong>
                                    <?php if (!empty($sp['capacity_spec'])): ?>
                                        <span class="text-muted text-xxs">(<?= htmlspecialchars($sp['capacity_spec']) ?>)</span>
                                    <?php endif; ?>
                                </td>
                                <td><code><?= htmlspecialchars($sp['sku']) ?></code></td>
                                <td class="text-center fw-bold"><?= (int)$sp['requested_qty'] ?> <?= htmlspecialchars($sp['unit'] ?? 'PCS') ?></td>
                                <td class="text-end">
                                    <span class="badge <?= empty($sp['is_chargeable']) ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-dark' ?> text-xxs">
                                        <?= empty($sp['is_chargeable']) ? 'Under AMC' : 'Chargeable' ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- 6. Authorization & Dual Signature Section -->
    <div class="row g-3 pt-2">
        <!-- Service Engineer Signature -->
        <div class="col-6">
            <div class="signature-box">
                <span class="text-muted text-xxs text-uppercase fw-bold d-block border-bottom pb-1 mb-2">Service Engineer Verification</span>
                <div class="fw-bold text-dark fs-7"><?= htmlspecialchars($serviceReport['engineer_name'] ?? $call['engineer_name'] ?? 'Rahul Sharma') ?></div>
                <div class="text-muted text-xs">Emp Code: <?= htmlspecialchars($serviceReport['engineer_code'] ?? 'ENG-101') ?></div>
                <div class="text-muted text-xs">Contact: <?= htmlspecialchars($serviceReport['engineer_mobile'] ?? '+91 98200 11223') ?></div>
                <div class="mt-3 pt-2 border-top text-success text-xxs d-flex align-items-center">
                    <i class="bi bi-patch-check-fill me-1"></i> Work Completed & Certified by Technician
                </div>
            </div>
        </div>

        <!-- Customer Authorized Signatory -->
        <div class="col-6">
            <div class="signature-box">
                <span class="text-muted text-xxs text-uppercase fw-bold d-block border-bottom pb-1 mb-1">Customer Sign-off & Acceptance</span>
                <?php if (!empty($serviceReport['customer_signature_data'])): ?>
                    <img src="<?= $serviceReport['customer_signature_data'] ?>" alt="Customer Touch Signature" class="signature-preview-img">
                <?php else: ?>
                    <div class="p-2 border rounded text-muted text-xxs bg-light my-2 text-center">
                        <em><?= $isPreview ? 'Pending Customer E-Signature' : 'Electronic Signature on File' ?></em>
                    </div>
                <?php endif; ?>
                <div class="fw-bold text-dark fs-7"><?= htmlspecialchars($serviceReport['customer_signed_name'] ?? $call['caller_name'] ?? 'Authorized Signatory') ?></div>
                <div class="text-muted text-xxs">
                    Date & Time: <?= !empty($serviceReport['signed_at']) ? date('d M Y, h:i A', strtotime($serviceReport['signed_at'])) : ($isPreview ? 'Pending Sign-off' : date('d M Y, h:i A')) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- 7. Bottom Disclaimer -->
    <div class="text-center text-muted text-xxs border-top pt-2 mt-3">
        <span>This is a computer-generated digital field service job card. <?= htmlspecialchars($settings['company_name'] ?? Setting::get('company_name', 'AMC System')) ?>.</span>
    </div>
</div>

<script>
const PDF_FILENAME = <?= json_encode($pdfFileName) ?>;
const WA_DIRECT_URL = <?= json_encode($waDirectUrl) ?>;

function getPdfConfig() {
    return {
        margin:       [8, 8, 8, 8],
        filename:     PDF_FILENAME,
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { scale: 2, useCORS: true, logging: false },
        jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
    };
}

/**
 * 1. Download Physical PDF File Directly
 */
function downloadPhysicalPDF() {
    const btn = document.getElementById('btnDownload');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';
    btn.disabled = true;

    const element = document.getElementById('jobCardDocument');
    html2pdf().set(getPdfConfig()).from(element).save().then(() => {
        btn.innerHTML = '<i class="bi bi-check-circle-fill text-success me-1"></i> Saved!';
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }, 2000);
    }).catch(err => {
        console.error('PDF error:', err);
        btn.innerHTML = originalText;
        btn.disabled = false;
        window.print();
    });
}

/**
 * 2. Share Physical PDF File Directly (Without URL replacing the file)
 */
async function sharePhysicalPDF() {
    const btn = document.getElementById('btnShare');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Preparing PDF...';
    btn.disabled = true;

    const element = document.getElementById('jobCardDocument');

    try {
        const pdfWorker = html2pdf().set(getPdfConfig()).from(element);
        const pdfBlob = await pdfWorker.outputPdf('blob');
        const pdfFile = new File([pdfBlob], PDF_FILENAME, { type: 'application/pdf' });

        // If Web Share API Level 2 file sharing is supported on this mobile device:
        // IMPORTANT: We pass ONLY files (no url) so WhatsApp attaches the real PDF file!
        if (navigator.canShare && navigator.canShare({ files: [pdfFile] })) {
            btn.innerHTML = originalText;
            btn.disabled = false;
            await navigator.share({
                files: [pdfFile],
                title: 'Service Job Card - <?= htmlspecialchars($call['call_number']) ?>'
            });
        } else {
            // Desktop or browser without direct file sharing API:
            // 1. Instantly download the PDF file to user's computer
            await html2pdf().set(getPdfConfig()).from(element).save();
            btn.innerHTML = originalText;
            btn.disabled = false;

            // 2. Open WhatsApp so user can drop the downloaded PDF file into chat
            alert('Job Card PDF (' + PDF_FILENAME + ') has been downloaded to your device!\n\nOpening WhatsApp now so you can attach it.');
            window.open(WA_DIRECT_URL, '_blank');
        }
    } catch (err) {
        console.error('Share error:', err);
        btn.innerHTML = originalText;
        btn.disabled = false;
        downloadPhysicalPDF();
    }
}

// Auto-trigger if requested via query parameter
document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('action') === 'download') {
        setTimeout(downloadPhysicalPDF, 600);
    } else if (urlParams.get('action') === 'share') {
        setTimeout(sharePhysicalPDF, 600);
    }
});
</script>

</body>
</html>
