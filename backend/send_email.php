<?php
// Simple email sending function for job applications

function sendEmail($to, $subject, $htmlBody, $from = 'careers@sofindex.com') {
    // For development: Log email instead of sending
    $logFile = __DIR__ . '/email_log.txt';
    $logEntry = "=== EMAIL LOG ===\n";
    $logEntry .= "Date: " . date('Y-m-d H:i:s') . "\n";
    $logEntry .= "To: " . $to . "\n";
    $logEntry .= "From: " . $from . "\n";
    $logEntry .= "Subject: " . $subject . "\n";
    $logEntry .= "Body Length: " . strlen($htmlBody) . " characters\n";
    $logEntry .= "================\n\n";
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    
    // Try to send real email
    try {
        // Email headers
        $headers = array(
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: SOFINDEX<' . $from . '>',
            'Reply-To: ' . $from,
            'X-Mailer: PHP/' . phpversion()
        );
        
        // Convert headers array to string
        $headersString = implode("\r\n", $headers);
        
        // Send email
        $result = mail($to, $subject, $htmlBody, $headersString);
        
        // Log the result
        $resultLog = "Email sending result: " . ($result ? "SUCCESS" : "FAILED") . "\n";
        file_put_contents($logFile, $resultLog, FILE_APPEND | LOCK_EX);
        
        return $result;
    } catch (Exception $e) {
        // Log any errors
        $errorLog = "Email sending error: " . $e->getMessage() . "\n";
        file_put_contents($logFile, $errorLog, FILE_APPEND | LOCK_EX);
        return false;
    }
}

function sendApplicationEmails($application, $job) {
    require_once 'email_templates.php';
    
    // Send email to admin
    $adminEmail = 'careers@sofindex.com';
    $adminSubject = 'New Job Application: ' . $job['title'] . ' - ' . $application['full_name'];
    $adminBody = getAdminNotificationEmail($application, $job);
    
    $adminResult = sendEmail($adminEmail, $adminSubject, $adminBody);
    
    // Send confirmation email to applicant
    $applicantEmail = $application['email'];
    $applicantSubject = 'Application Received - ' . $job['title'] . ' | SOFINDEX';
    $applicantBody = getUserConfirmationEmail($application, $job);
    
    $applicantResult = sendEmail($applicantEmail, $applicantSubject, $applicantBody);
    
    return array(
        'admin_sent' => $adminResult,
        'applicant_sent' => $applicantResult
    );
}
?>
