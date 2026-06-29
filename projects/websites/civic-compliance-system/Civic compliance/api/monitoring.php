<?php
/**
 * api/monitoring.php
 * Handles submission of monitoring reports
 * PBO Compliance Hub | CRECO Kenya
 *
 * DB: if0_42280606_if0_42280606_
 * User: if0_42280606
 * Password: (Your vPanel Password)
 * Host: sql303.infinityfree.com
 */

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/auth.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

// CORS
if (defined('ALLOWED_ORIGIN')) {
    header('Access-Control-Allow-Origin: ' . ALLOWED_ORIGIN);
}

// Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// CSRF validation
if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid security token. Please refresh and try again.']);
    exit;
}

// Rate limiting
$clientIp = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
if (!checkRateLimit('monitoring_' . md5($clientIp), 5, 3600)) {
    http_response_code(429);
    echo json_encode(['success' => false, 'error' => 'Too many submissions. Please wait before submitting again.']);
    exit;
}

$db = Database::getInstance()->getConnection();

// Determine report type
$reportType = sanitizeInput($_POST['report_type'] ?? '');

$allowedTypes = ['compliance', 'barrier', 'incident', 'enabling'];
if (!in_array($reportType, $allowedTypes)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid report type.']);
    exit;
}

// Common fields
$orgName       = sanitizeInput($_POST['org_name'] ?? 'Anonymous');
$orgType       = sanitizeInput($_POST['org_type'] ?? '');
$county        = sanitizeInput($_POST['submitter_county'] ?? '');
$contactEmail  = filter_var($_POST['contact_email'] ?? '', FILTER_SANITIZE_EMAIL);
$consent       = isset($_POST['consent']);

// Validate consent
if (!$consent) {
    echo json_encode(['success' => false, 'error' => 'You must provide consent to submit this report.']);
    exit;
}

// Validate county
if (empty($county)) {
    echo json_encode(['success' => false, 'error' => 'Please select your county.']);
    exit;
}

// Build type-specific data
$reportData = [];
$mainDescription = '';
$severity = 'low';

switch ($reportType) {

    case 'compliance':
        $regStatus     = sanitizeInput($_POST['reg_status'] ?? '');
        $regDuration   = sanitizeInput($_POST['registration_duration'] ?? '');
        $regRating     = intval($_POST['registration_rating'] ?? 0);
        $regExperience = sanitizeInput($_POST['registration_experience'] ?? '');
        $compChallenges= $_POST['challenges'] ?? [];
        $compDetails   = sanitizeInput($_POST['compliance_details'] ?? '');
        $regCost       = intval($_POST['registration_cost'] ?? 0);
        $visitsCount   = intval($_POST['visits_count'] ?? 0);
        $docsCount     = intval($_POST['documents_count'] ?? 0);
        $infPayments   = sanitizeInput($_POST['informal_payments'] ?? 'prefer_not_say');
        $appDate       = $_POST['application_date'] ?? null;
        $regDate       = $_POST['registration_date'] ?? null;

        if (empty($regExperience)) {
            echo json_encode(['success' => false, 'error' => 'Please describe your registration experience.']);
            exit;
        }

        $mainDescription = $regExperience;
        $reportData = [
            'registration_status'   => $regStatus,
            'registration_duration' => $regDuration,
            'registration_rating'   => $regRating,
            'compliance_challenges' => implode(',', array_map('sanitizeInput', $compChallenges)),
            'compliance_details'    => $compDetails,
            'registration_cost_kes' => $regCost,
            'visits_count'          => $visitsCount,
            'documents_count'       => $docsCount,
            'informal_payments'     => $infPayments,
            'application_date'      => $appDate,
            'registration_date'     => $regDate,
        ];
        break;

    case 'barrier':
        $barrierType   = sanitizeInput($_POST['barrier_type'] ?? '');
        $authOffice    = sanitizeInput($_POST['authority_office'] ?? '');
        $barrierDate   = $_POST['barrier_date'] ?? null;
        $barrierDesc   = sanitizeInput($_POST['barrier_description'] ?? '');
        $impact        = $_POST['impact'] ?? [];
        $resolved      = sanitizeInput($_POST['resolved'] ?? 'no');
        $resolution    = sanitizeInput($_POST['resolution_details'] ?? '');

        if (empty($barrierDesc)) {
            echo json_encode(['success' => false, 'error' => 'Please describe the barrier.']);
            exit;
        }
        if (empty($barrierDate)) {
            echo json_encode(['success' => false, 'error' => 'Please provide the date the barrier was experienced.']);
            exit;
        }

        $mainDescription = $barrierDesc;
        $reportData = [
            'barrier_type'         => $barrierType,
            'authority_office'     => $authOffice,
            'barrier_date'         => $barrierDate,
            'impact'               => implode(',', array_map('sanitizeInput', $impact)),
            'resolved'             => $resolved,
            'resolution_details'   => $resolution,
        ];
        break;

    case 'incident':
        $reporterType     = sanitizeInput($_POST['reporter_type'] ?? 'anonymous');
        $violationType    = sanitizeInput($_POST['violation_type'] ?? '');
        $incidentSeverity = sanitizeInput($_POST['severity'] ?? 'medium');
        $incidentDate     = $_POST['incident_date'] ?? null;
        $perpetratorType  = sanitizeInput($_POST['perpetrator_type'] ?? '');
        $incidentDesc     = sanitizeInput($_POST['incident_description'] ?? '');
        $peopleAffected   = intval($_POST['people_affected'] ?? 0);
        $reportedAuth     = sanitizeInput($_POST['reported_to_authorities'] ?? 'no');
        $authReference    = sanitizeInput($_POST['authority_reference'] ?? '');
        $witnessInfo      = sanitizeInput($_POST['witness_info'] ?? '');

        if (empty($incidentDesc)) {
            echo json_encode(['success' => false, 'error' => 'Please describe the incident.']);
            exit;
        }
        if (empty($incidentDate)) {
            echo json_encode(['success' => false, 'error' => 'Please provide the incident date.']);
            exit;
        }
        if (empty($violationType)) {
            echo json_encode(['success' => false, 'error' => 'Please select the type of violation.']);
            exit;
        }

        $severity = $incidentSeverity;
        $mainDescription = $incidentDesc;
        $reportData = [
            'reporter_type'              => $reporterType,
            'violation_type'             => $violationType,
            'severity'                   => $incidentSeverity,
            'incident_date'              => $incidentDate,
            'perpetrator_type'           => $perpetratorType,
            'people_affected'            => $peopleAffected,
            'reported_to_authorities'    => $reportedAuth,
            'authority_reference'        => $authReference,
            'witness_info'               => $witnessInfo,
        ];
        break;

    case 'enabling':
        $practiceCategory = sanitizeInput($_POST['practice_category'] ?? '');
        $practiceDesc     = sanitizeInput($_POST['practice_description'] ?? '');
        $practiceRating   = intval($_POST['practice_rating'] ?? 0);
        $recommend        = sanitizeInput($_POST['recommend_replication'] ?? 'maybe');

        if (empty($practiceDesc)) {
            echo json_encode(['success' => false, 'error' => 'Please describe the enabling practice.']);
            exit;
        }

        $mainDescription = $practiceDesc;
        $reportData = [
            'practice_category'      => $practiceCategory,
            'practice_rating'        => $practiceRating,
            'recommend_replication'  => $recommend,
        ];
        break;
}

// Handle file uploads
$uploadedFiles = [];
if (!empty($_FILES['documents']['name'][0])) {
    $uploadDir = '../uploads/monitoring/' . date('Y/m/') . uniqid() . '/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $allowedMimes = [
        'application/pdf', 'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'image/jpeg', 'image/png', 'image/gif',
        'video/mp4', 'video/quicktime',
    ];

    $maxFileSize = 10 * 1024 * 1024; // 10MB

    foreach ($_FILES['documents']['tmp_name'] as $key => $tmpName) {
        if ($_FILES['documents']['error'][$key] !== UPLOAD_ERR_OK) continue;

        $originalName = basename($_FILES['documents']['name'][$key]);
        $fileSize     = $_FILES['documents']['size'][$key];
        $mimeType     = mime_content_type($tmpName);

        if (!in_array($mimeType, $allowedMimes)) {
            continue; // Skip disallowed file types
        }

        if ($fileSize > $maxFileSize) {
            continue; // Skip oversized files
        }

        // Sanitize filename
        $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
        $uniqueName = time() . '_' . $key . '_' . $safeName;
        $destination = $uploadDir . $uniqueName;

        if (move_uploaded_file($tmpName, $destination)) {
            $uploadedFiles[] = [
                'original_name' => $originalName,
                'stored_path'   => $destination,
                'mime_type'     => $mimeType,
                'file_size'     => $fileSize,
            ];
        }
    }
}

// Begin DB transaction
$db->beginTransaction();

try {
    // Insert main monitoring report
    $stmt = $db->prepare("
        INSERT INTO monitoring_reports (
            report_type,
            organization_name,
            organization_type,
            county,
            description,
            severity,
            report_data,
            ip_address,
            status,
            created_at
        ) VALUES (
            :report_type, :org_name, :org_type, :submitter_county,
            :description, :severity, :report_data,
            :ip_address, 'submitted', NOW()
        )
    ");

    $stmt->execute([
        ':report_type'      => $reportType,
        ':org_name'         => $orgName,
        ':org_type'         => $orgType,
        ':submitter_county' => $county,
        ':description'      => $mainDescription,
        ':severity'         => $severity,
        ':report_data'      => json_encode($reportData),
        ':ip_address'       => md5($clientIp),
    ]);

    $reportId = $db->lastInsertId();

    // Insert uploaded files
    if (!empty($uploadedFiles)) {
        $stmtFile = $db->prepare("
            INSERT INTO monitoring_attachments (report_id, original_name, stored_path, mime_type, file_size, created_at)
            VALUES (:report_id, :original_name, :stored_path, :mime_type, :file_size, NOW())
        ");

        foreach ($uploadedFiles as $file) {
            $stmtFile->execute([
                ':report_id'     => $reportId,
                ':original_name' => $file['original_name'],
                ':stored_path'   => $file['stored_path'],
                ':mime_type'     => $file['mime_type'],
                ':file_size'     => $file['file_size'],
            ]);
        }
    }

    $db->commit();

    // Send notification email to admin (non-blocking)
    $adminEmail = APP_EMAIL;
    $subject = '[PBO Hub] New ' . ucfirst($reportType) . ' Report — ' . $county;
    $body = "A new {$reportType} report has been submitted.\n\n"
          . "Report ID: {$reportId}\n"
          . "County: {$county}\n"
          . "Organization: {$orgName}\n"
          . "Severity: {$severity}\n\n"
          . "Review at: " . APP_URL . "/admin/monitoring/view.php?id={$reportId}";

    if (function_exists('mail')) {
        @mail($adminEmail, $subject, $body, 'From: ' . SMTP_USER);
    }

    echo json_encode([
        'success'   => true,
        'message'   => 'Thank you! Your report (ID: #' . str_pad($reportId, 6, '0', STR_PAD_LEFT) . ') has been received and will be reviewed by the CRECO Kenya team.',
        'report_id' => $reportId,
    ]);

} catch (Exception $e) {
    $db->rollBack();

    // Clean up uploaded files on error
    foreach ($uploadedFiles as $file) {
        if (file_exists($file['stored_path'])) {
            unlink($file['stored_path']);
        }
    }

    error_log('Monitoring API Error: ' . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'A server error occurred. Please try again. If the problem persists, contact support.',
    ]);
}