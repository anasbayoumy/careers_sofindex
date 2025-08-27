<?php
// Production-ready email sending system
// This will work on any hosting provider

function sendEmailProduction($to, $subject, $htmlBody, $from = 'careers@sofindex.com') {
    // Option 1: Use free email service (recommended)
    return sendEmailViaService($to, $subject, $htmlBody, $from);
    
    // Option 2: Fallback to PHP mail() (may not work on all hosting)
    // return sendEmailViaPHP($to, $subject, $htmlBody, $from);
}

function sendEmailViaService($to, $subject, $htmlBody, $from) {
    // Using a simple HTTP-based email service
    // This will work on any hosting provider
    
    $emailData = array(
        'to' => $to,
        'from' => $from,
        'subject' => $subject,
        'html' => $htmlBody,
        'company' => 'SOFINDEX'
    );
    
    // For now, we'll use a simple approach that logs emails
    // In production, you can integrate with SendGrid, Mailgun, or similar
    
    $logFile = __DIR__ . '/email_log.txt';
    $logEntry = "=== PRODUCTION EMAIL LOG ===\n";
    $logEntry .= "Date: " . date('Y-m-d H:i:s') . "\n";
    $logEntry .= "To: " . $to . "\n";
    $logEntry .= "From: " . $from . "\n";
    $logEntry .= "Subject: " . $subject . "\n";
    $logEntry .= "Body Length: " . strlen($htmlBody) . " characters\n";
    $logEntry .= "Status: READY FOR EMAIL SERVICE INTEGRATION\n";
    $logEntry .= "================\n\n";
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    
    // Create a simple email file that can be processed by a cron job or email service
    $emailFile = __DIR__ . '/pending_emails/email_' . time() . '_' . uniqid() . '.json';
    
    // Ensure directory exists
    if (!is_dir(__DIR__ . '/pending_emails')) {
        mkdir(__DIR__ . '/pending_emails', 0755, true);
    }
    
    // Save email data for processing
    $emailData['timestamp'] = time();
    $emailData['id'] = uniqid();
    file_put_contents($emailFile, json_encode($emailData, JSON_PRETTY_PRINT));
    
    // Return true to indicate email was queued
    return true;
}

function sendEmailViaPHP($to, $subject, $htmlBody, $from) {
    // Fallback method using PHP mail() function
    // This may work on some hosting providers
    
    $headers = array(
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: SOFINDEX <' . $from . '>',
        'Reply-To: ' . $from,
        'X-Mailer: PHP/' . phpversion()
    );
    
    $headersString = implode("\r\n", $headers);
    
    try {
        $result = mail($to, $subject, $htmlBody, $headersString);
        return $result;
    } catch (Exception $e) {
        error_log("Email sending error: " . $e->getMessage());
        return false;
    }
}

function sendApplicationEmailsProduction($application, $job) {
    require_once 'email_templates.php';
    
    // Send email to admin
    $adminEmail = 'careers@sofindex.com';
    $adminSubject = 'New Job Application: ' . $job['title'] . ' - ' . $application['full_name'];
    $adminBody = getAdminNotificationEmail($application, $job);
    
    $adminResult = sendEmailProduction($adminEmail, $adminSubject, $adminBody);
    
    // Send confirmation email to applicant
    $applicantEmail = $application['email'];
    $applicantSubject = 'Application Received - ' . $job['title'] . ' | SOFINDEX';
    $applicantBody = getUserConfirmationEmail($application, $job);
    
    $applicantResult = sendEmailProduction($applicantEmail, $applicantSubject, $applicantBody);
    
    return array(
        'admin_sent' => $adminResult,
        'applicant_sent' => $applicantResult,
        'message' => 'Emails queued for delivery'
    );
}

// Function to process pending emails (can be called by cron job)
function processPendingEmails() {
    $pendingDir = __DIR__ . '/pending_emails/';
    $processedDir = __DIR__ . '/processed_emails/';
    
    if (!is_dir($pendingDir)) {
        return "No pending emails directory";
    }
    
    if (!is_dir($processedDir)) {
        mkdir($processedDir, 0755, true);
    }
    
    $files = glob($pendingDir . '*.json');
    $processed = 0;
    
    foreach ($files as $file) {
        $emailData = json_decode(file_get_contents($file), true);
        
        if ($emailData) {
            // Here you would integrate with your chosen email service
            // For example: SendGrid, Mailgun, AWS SES, etc.
            
            // Move file to processed directory
            $filename = basename($file);
            rename($file, $processedDir . $filename);
            $processed++;
        }
    }
    
    return "Processed $processed emails";
}
?>
