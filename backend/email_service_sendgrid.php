<?php
// SendGrid Email Service Integration
// This will work on any hosting provider

// To use this:
// 1. Sign up for free SendGrid account (100 emails/day free)
// 2. Get your API key from SendGrid dashboard
// 3. Uncomment the code below and add your API key

function sendEmailViaSendGrid($to, $subject, $htmlBody, $from = 'careers@sofindex.com') {
    // YOUR SENDGRID API KEY - Get this from SendGrid dashboard
    $apiKey = 'YOUR_SENDGRID_API_KEY_HERE'; // Replace with your actual API key
    
    // If no API key is set, fall back to logging
    if ($apiKey === 'YOUR_SENDGRID_API_KEY_HERE') {
        return sendEmailViaService($to, $subject, $htmlBody, $from);
    }
    
    // SendGrid API endpoint
    $url = 'https://api.sendgrid.com/v3/mail/send';
    
    // Email data
    $data = array(
        'personalizations' => array(
            array(
                'to' => array(
                    array('email' => $to)
                )
            )
        ),
        'from' => array(
            'email' => $from,
            'name' => 'SOFINDEX Careers'
        ),
        'subject' => $subject,
        'content' => array(
            array(
                'type' => 'text/html',
                'value' => $htmlBody
            )
        )
    );
    
    // Send via cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json'
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Log the result
    $logFile = __DIR__ . '/email_log.txt';
    $logEntry = "=== SENDGRID EMAIL LOG ===\n";
    $logEntry .= "Date: " . date('Y-m-d H:i:s') . "\n";
    $logEntry .= "To: " . $to . "\n";
    $logEntry .= "Subject: " . $subject . "\n";
    $logEntry .= "HTTP Code: " . $httpCode . "\n";
    $logEntry .= "Response: " . $response . "\n";
    $logEntry .= "================\n\n";
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    
    // Return true if email was sent successfully
    return $httpCode === 202; // SendGrid returns 202 for success
}

// Alternative: Mailgun (5000 emails/month free)
function sendEmailViaMailgun($to, $subject, $htmlBody, $from = 'careers@sofindex.com') {
    // YOUR MAILGUN API KEY - Get this from Mailgun dashboard
    $apiKey = 'YOUR_MAILGUN_API_KEY_HERE'; // Replace with your actual API key
    $domain = 'YOUR_MAILGUN_DOMAIN_HERE'; // Replace with your Mailgun domain
    
    // If no API key is set, fall back to logging
    if ($apiKey === 'YOUR_MAILGUN_API_KEY_HERE') {
        return sendEmailViaService($to, $subject, $htmlBody, $from);
    }
    
    // Mailgun API endpoint
    $url = "https://api.mailgun.net/v3/{$domain}/messages";
    
    // Email data
    $data = array(
        'from' => 'SOFINDEX Careers <' . $from . '>',
        'to' => $to,
        'subject' => $subject,
        'html' => $htmlBody
    );
    
    // Send via cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_USERPWD, "api:{$apiKey}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Log the result
    $logFile = __DIR__ . '/email_log.txt';
    $logEntry = "=== MAILGUN EMAIL LOG ===\n";
    $logEntry .= "Date: " . date('Y-m-d H:i:s') . "\n";
    $logEntry .= "To: " . $to . "\n";
    $logEntry .= "Subject: " . $subject . "\n";
    $logEntry .= "HTTP Code: " . $httpCode . "\n";
    $logEntry .= "Response: " . $response . "\n";
    $logEntry .= "================\n\n";
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    
    // Return true if email was sent successfully
    return $httpCode === 200; // Mailgun returns 200 for success
}

// Function to send application emails using SendGrid
function sendApplicationEmailsSendGrid($application, $job) {
    require_once 'email_templates.php';
    
    // Send email to admin
    $adminEmail = 'careers@sofindex.com';
    $adminSubject = 'New Job Application: ' . $job['title'] . ' - ' . $application['full_name'];
    $adminBody = getAdminNotificationEmail($application, $job);
    
    $adminResult = sendEmailViaSendGrid($adminEmail, $adminSubject, $adminBody);
    
    // Send confirmation email to applicant
    $applicantEmail = $application['email'];
    $applicantSubject = 'Application Received - ' . $job['title'] . ' | SOFINDEX Careers';
    $applicantBody = getUserConfirmationEmail($application, $job);
    
    $applicantResult = sendEmailViaSendGrid($applicantEmail, $applicantSubject, $applicantBody);
    
    return array(
        'admin_sent' => $adminResult,
        'applicant_sent' => $applicantResult,
        'service' => 'SendGrid'
    );
}

// Function to send application emails using Mailgun
function sendApplicationEmailsMailgun($application, $job) {
    require_once 'email_templates.php';
    
    // Send email to admin
    $adminEmail = 'careers@sofindex.com';
    $adminSubject = 'New Job Application: ' . $job['title'] . ' - ' . $application['full_name'];
    $adminBody = getAdminNotificationEmail($application, $job);
    
    $adminResult = sendEmailViaMailgun($adminEmail, $adminSubject, $adminBody);
    
    // Send confirmation email to applicant
    $applicantEmail = $application['email'];
    $applicantSubject = 'Application Received - ' . $job['title'] . ' | SOFINDEX Careers';
    $applicantBody = getUserConfirmationEmail($application, $job);
    
    $applicantResult = sendEmailViaMailgun($applicantEmail, $applicantSubject, $applicantBody);
    
    return array(
        'admin_sent' => $adminResult,
        'applicant_sent' => $applicantResult,
        'service' => 'Mailgun'
    );
}
?>
