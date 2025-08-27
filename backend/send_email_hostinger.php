<?php
// Hostinger-compatible email sending system
// Based on working sample from Hostinger hosting

function sendEmailHostinger($to, $subject, $htmlBody, $from = 'careers@sofindex.com') {
    // Email headers for HTML emails - using more robust format for Hostinger
    $headers = array();
    $headers[] = "MIME-Version: 1.0";
    $headers[] = "Content-Type: text/html; charset=UTF-8";
    $headers[] = "From: SOFINDEX<$from>";
    $headers[] = "Reply-To: $from";
    $headers[] = "X-Mailer: PHP/" . phpversion();
    
    // Convert headers array to string
    $headersString = implode("\r\n", $headers);
    
    // Log email attempt
    $logFile = __DIR__ . '/email_log.txt';
    $logEntry = "=== HOSTINGER EMAIL LOG ===\n";
    $logEntry .= "Date: " . date('Y-m-d H:i:s') . "\n";
    $logEntry .= "To: " . $to . "\n";
    $logEntry .= "From: " . $from . "\n";
    $logEntry .= "Subject: " . $subject . "\n";
    $logEntry .= "Body Length: " . strlen($htmlBody) . " characters\n";
    $logEntry .= "Headers: " . $headersString . "\n";
    $logEntry .= "================\n\n";
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    
    // Send email using PHP mail() function (works on Hostinger)
    $result = mail($to, $subject, $htmlBody, $headersString);
    
    // Log the result
    $resultLog = "Email sending result: " . ($result ? "SUCCESS" : "FAILED") . "\n";
    file_put_contents($logFile, $resultLog, FILE_APPEND | LOCK_EX);
    
    return $result;
}

function sendApplicationEmailsHostinger($application, $job) {
    require_once 'email_templates.php';
    
    // Log the start of email sending process
    $logFile = __DIR__ . '/email_log.txt';
    $startLog = "=== STARTING EMAIL SENDING PROCESS ===\n";
    $startLog .= "Date: " . date('Y-m-d H:i:s') . "\n";
    $startLog .= "Application ID: " . $application['id'] . "\n";
    $startLog .= "Job Title: " . $job['title'] . "\n";
    $startLog .= "Applicant: " . $application['full_name'] . "\n";
    $startLog .= "================\n\n";
    file_put_contents($logFile, $startLog, FILE_APPEND | LOCK_EX);
    
    // Send email to admin FIRST
    $adminEmail = 'careers@sofindex.com';
    $adminSubject = 'New Job Application: ' . $job['title'] . ' - ' . $application['full_name'];
    $adminBody = getAdminNotificationEmail($application, $job);
    
    $adminResult = sendEmailHostinger($adminEmail, $adminSubject, $adminBody);
    
    // Log admin email result
    $adminLog = "Admin email result: " . ($adminResult ? "SUCCESS" : "FAILED") . "\n";
    file_put_contents($logFile, $adminLog, FILE_APPEND | LOCK_EX);
    
    // Send confirmation email to applicant
    $applicantEmail = $application['email'];
    $applicantSubject = 'Application Received - ' . $job['title'] . ' | SOFCAREERS';
    $applicantBody = getUserConfirmationEmail($application, $job);
    
    $applicantResult = sendEmailHostinger($applicantEmail, $applicantSubject, $applicantBody);
    
    // Log applicant email result
    $applicantLog = "Applicant email result: " . ($applicantResult ? "SUCCESS" : "FAILED") . "\n";
    file_put_contents($logFile, $applicantLog, FILE_APPEND | LOCK_EX);
    
    // Log completion
    $completionLog = "=== EMAIL SENDING PROCESS COMPLETED ===\n";
    $completionLog .= "Admin email: " . ($adminResult ? "SENT" : "FAILED") . "\n";
    $completionLog .= "Applicant email: " . ($applicantResult ? "SENT" : "FAILED") . "\n";
    $completionLog .= "================\n\n";
    file_put_contents($logFile, $completionLog, FILE_APPEND | LOCK_EX);
    
    return array(
        'admin_sent' => $adminResult,
        'applicant_sent' => $applicantResult,
        'service' => 'Hostinger PHP mail()',
        'admin_email' => $adminEmail,
        'applicant_email' => $applicantEmail
    );
}

// Alternative simple email function (if you prefer inline HTML)
function sendSimpleEmail($to, $subject, $htmlContent, $from = 'careers@sofindex.com') {
    $headers = array();
    $headers[] = "MIME-Version: 1.0";
    $headers[] = "Content-Type: text/html; charset=UTF-8";
    $headers[] = "From: SOFINDEX<$from>";
    $headers[] = "Reply-To: $from";
    
    $headersString = implode("\r\n", $headers);
    
    return mail($to, $subject, $htmlContent, $headersString);
}
?>
