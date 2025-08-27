<?php
// Email templates for job applications

function getAdminNotificationEmail($application, $job) {
    $html = '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>New Job Application Received</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4; }
            .email-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; }
            .email-header { background: linear-gradient(135deg, #000000 0%, #333 100%); color: white; padding: 30px; text-align: center; }
            .email-header h1 { margin: 0; font-size: 28px; font-weight: 700; }
            .email-header p { margin: 10px 0 0 0; font-size: 16px; opacity: 0.9; }
            .email-body { padding: 40px 30px; }
            .section { margin-bottom: 30px; }
            .section h2 { color: #000000; border-bottom: 2px solid #000000; padding-bottom: 10px; margin-bottom: 20px; font-size: 20px; }
            .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
            .info-item { background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #000000; }
            .info-item strong { color: #000000; display: block; margin-bottom: 5px; }
            .cv-section { background: #e8f5e8; padding: 20px; border-radius: 8px; border: 1px solid #d4edda; }
            .cv-section a { color: #155724; text-decoration: none; font-weight: 600; }
            .cv-section a:hover { text-decoration: underline; }
            .email-footer { background: #f8f9fa; padding: 30px; text-align: center; border-top: 1px solid #dee2e6; }
            .email-footer p { margin: 0; color: #6c757d; font-size: 14px; }
            .logo { font-size: 24px; font-weight: 700; margin-bottom: 10px; }
            .status-badge { display: inline-block; background: #28a745; color: white; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; }
        </style>
    </head>
    <body>
        <div class="email-container">
            <div class="email-header">
                <div class="logo">SOFINDEX</div>
                <h1>New Job Application Received</h1>
                <p>You have received a new application for review</p>
            </div>
            
            <div class="email-body">
                <div class="section">
                    <h2>📋 Application Summary</h2>
                    <div class="info-grid">
                        <div class="info-item">
                            <strong>Position Applied For:</strong>
                            ' . htmlspecialchars($job['title']) . '
                        </div>
                        <div class="info-item">
                            <strong>Department:</strong>
                            ' . htmlspecialchars($job['department']) . '
                        </div>
                        <div class="info-item">
                            <strong>Application Status:</strong>
                            <span class="status-badge">New</span>
                        </div>
                        <div class="info-item">
                            <strong>Application Date:</strong>
                            ' . date('F j, Y \a\t g:i A', strtotime($application['submitted_at'])) . '
                        </div>
                    </div>
                </div>
                
                <div class="section">
                    <h2>👤 Applicant Information</h2>
                    <div class="info-grid">
                        <div class="info-item">
                            <strong>Full Name:</strong>
                            ' . htmlspecialchars($application['full_name']) . '
                        </div>
                        <div class="info-item">
                            <strong>Email Address:</strong>
                            <a href="mailto:' . htmlspecialchars($application['email']) . '">' . htmlspecialchars($application['email']) . '</a>
                        </div>
                        <div class="info-item">
                            <strong>Phone Number:</strong>
                            <a href="tel:' . htmlspecialchars($application['phone']) . '">' . htmlspecialchars($application['phone']) . '</a>
                        </div>
                        <div class="info-item">
                            <strong>LinkedIn Profile:</strong>
                            <a href="' . htmlspecialchars($application['linkedin_url']) . '" target="_blank">View Profile</a>
                        </div>
                    </div>
                </div>
                
                <div class="section">
                    <h2>📄 Resume/CV</h2>
                    <div class="cv-section">';
    
    if (strpos($application['resume_url'], 'uploads/') === 0) {
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'your-domain.com';
        $html .= '<strong>CV Uploaded:</strong> <a href="' . $host . '/backend/' . $application['resume_url'] . '" target="_blank">Download CV File</a>';
    } else {
        $html .= '<strong>CV Link:</strong> <a href="' . htmlspecialchars($application['resume_url']) . '" target="_blank">View CV Online</a>';
    }
    
    $html .= '
                    </div>
                </div>';
    
    if (!empty($application['portfolio_url'])) {
        $html .= '
                <div class="section">
                    <h2>🎨 Portfolio</h2>
                    <div class="cv-section">
                        <strong>Portfolio Link:</strong> <a href="' . htmlspecialchars($application['portfolio_url']) . '" target="_blank">View Portfolio</a>
                    </div>
                </div>';
    }
    
    $html .= '
                <div class="section">
                    <h2>💌 Cover Letter</h2>
                    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #000000; font-style: italic; line-height: 1.6;">
                        ' . nl2br(htmlspecialchars($application['cover_letter'])) . '
                    </div>
                </div>
                
                <div class="section">
                    <h2>⚡ Quick Actions</h2>
                    <p style="text-align: center; margin: 20px 0;">
                        <a href="' . $_SERVER['HTTP_HOST'] . '/frontend-admin/" style="background: #000000; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: 600; display: inline-block;">Review Application in Admin Panel</a>
                    </p>
                </div>
            </div>
            
            <div class="email-footer">
                <p><strong>SOFINDEX 
                </strong></p>
                <p>Building the future through innovative technology solutions and exceptional talent</p>
                <p>This is an automated notification. Please do not reply to this email.</p>
            </div>
        </div>
    </body>
    </html>';
    
    return $html;
}

function getUserConfirmationEmail($application, $job) {
    // Get the email body - either custom questions or default message
    if (!empty($job['custom_email_body'])) {
        // Custom questions format
        $emailBody = "Hi " . htmlspecialchars($application['full_name']) . ",\n\n" . $job['custom_email_body'] . "\n\nBest of luck,\nSOFINDEX";
    } else {
        // Default message format
        $emailBody = "Hello " . htmlspecialchars($application['full_name']) . ",\n\nThank you for applying for the " . htmlspecialchars($job['title']) . " role at SOFINDEX. We've received your application and appreciate you taking the time to share your experience with us.\n\nWe'll review your submission and be in touch soon with next steps. If you need to contact us, email careers@sofindex.com.\n\nBest regards,\nSOFINDEX";
    }
    
    $html = '
    <!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="utf-8" />
      <meta name="viewport" content="width=device-width, initial-scale=1" />
      <title>Application Received - SOFINDEX</title>
      <style>
        /* Page-level styles */
        body {
          font-family: Arial, sans-serif;
          line-height: 1.6;
          color: #333;
          margin: 0;
          padding: 30px;
          background-color: #f4f4f4;
        }

        /* Plain email-style body text (no boxed container) */
        .body-text {
          font-size: 16px;
          color: #222;
          white-space: pre-wrap; /* preserves newlines */
          margin: 0 0 28px 0;
        }

        /* Footer kept visually separate */
        .email-footer {
          background: #f8f9fa;
          padding: 20px 16px;
          text-align: center;
          border-top: 1px solid #dee2e6;
          margin-top: 18px;
        }
        .email-footer p { margin: 6px 0; color: #6c757d; font-size: 14px; }
        .email-footer a { color: #000; text-decoration: none; }
        .email-footer a:hover { text-decoration: underline; }
      </style>
    </head>
    <body>
      <!-- Plain text body (no white box, no header) -->
      <div class="body-text">
        ' . nl2br(htmlspecialchars($emailBody)) . '
      </div>

      <!-- Footer (unchanged) -->
      <div class="email-footer">
        <p><strong>SOFCAREERS</strong></p>
        <p>
          <a href="https://www.linkedin.com/company/sofindex" target="_blank" rel="noopener">LinkedIn</a> |
          <a href="mailto:careers@sofindex.com">careers@sofindex.com</a> |
          <a href="https://sofindex.com" target="_blank" rel="noopener">sofindex.com</a>
        </p>
        <p style="margin-top: 10px; font-size: 12px; color: #999;">
          Building the future through innovative technology solutions and exceptional talent
        </p>
      </div>
    </body>
    </html>';
    
    return $html;
}
?>
