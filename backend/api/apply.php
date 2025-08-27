<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../database/db_connection.php';

// Handle GET request to fetch all applications (for admin)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $action = $_GET['action'] ?? '';
        
        if ($action === 'getAll') {
            // Fetch all applications with job titles (excluding rejected ones)
            $sql = "SELECT ja.*, j.title as job_title 
                    FROM job_applications ja 
                    LEFT JOIN jobs j ON ja.job_id = j.id 
                    WHERE ja.status != 'Rejected'
                    ORDER BY ja.submitted_at DESC";
            
            $result = $conn->query($sql);
            $applications = [];
            
            while ($row = $result->fetch_assoc()) {
                $applications[] = $row;
            }
            
            echo json_encode([
                'success' => true,
                'applications' => $applications
            ]);
        } elseif ($action === 'checkDuplicate') {
            // Check for duplicate submissions
            $email = $_GET['email'] ?? '';
            $phone = $_GET['phone'] ?? '';
            
            if (!$email && !$phone) {
                echo json_encode(['error' => 'Email or phone required']);
                exit;
            }
            
            $sql = "SELECT id, job_id, submitted_at FROM job_applications WHERE (email = ? OR phone = ?) AND submitted_at > DATE_SUB(NOW(), INTERVAL 30 DAY) ORDER BY submitted_at DESC LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ss', $email, $phone);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $duplicate = $result->fetch_assoc();
                $days_ago = floor((time() - strtotime($duplicate['submitted_at'])) / (60 * 60 * 24));
                echo json_encode([
                    'success' => true,
                    'isDuplicate' => true,
                    'message' => "You have already submitted an application within the last 30 days (submitted $days_ago days ago).",
                    'daysAgo' => $days_ago
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'isDuplicate' => false
                ]);
            }
        } else {
            echo json_encode(['error' => 'Invalid action']);
        }
    } catch (Exception $e) {
        error_log("Error fetching applications: " . $e->getMessage());
        echo json_encode(['error' => 'Failed to fetch applications']);
    }
    exit;
}

// Handle POST request for application submission or status update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if this is a status update request
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (isset($input['action']) && $input['action'] === 'updateStatus') {
        // Handle status update
        try {
            $application_id = intval($input['application_id'] ?? 0);
            $new_status = trim($input['status'] ?? '');
            
            if (!$application_id || !$new_status) {
                echo json_encode(['error' => 'Missing required fields']);
                exit;
            }
            
            // Validate status
            $valid_statuses = ['Pending', 'Under Review', 'Shortlisted', 'Rejected', 'Hired'];
            if (!in_array($new_status, $valid_statuses)) {
                echo json_encode(['error' => 'Invalid status']);
                exit;
            }
            
            if ($new_status === 'Rejected') {
                // Delete rejected application from database
                $stmt = $conn->prepare('DELETE FROM job_applications WHERE id = ?');
                $stmt->bind_param('i', $application_id);
                
                if ($stmt->execute()) {
                    if ($stmt->affected_rows > 0) {
                        echo json_encode([
                            'success' => true, 
                            'message' => 'Application rejected and removed from database',
                            'action' => 'deleted'
                        ]);
                    } else {
                        echo json_encode(['error' => 'Application not found or already deleted']);
                    }
                } else {
                    echo json_encode(['error' => 'Failed to remove rejected application']);
                }
            } else {
                // Update application status for non-rejected statuses
                $stmt = $conn->prepare('UPDATE job_applications SET status = ?, reviewed_at = NOW() WHERE id = ?');
                $stmt->bind_param('si', $new_status, $application_id);
                
                if ($stmt->execute()) {
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Status updated successfully',
                        'action' => 'updated'
                    ]);
                } else {
                    echo json_encode(['error' => 'Failed to update status']);
                }
            }
        } catch (Exception $e) {
            error_log("Status update error: " . $e->getMessage());
            echo json_encode(['error' => 'An error occurred while updating status']);
        }
        exit;
    }
    
    // Handle regular application submission
    try {
        // Get form data
        $job_id = intval($_POST['job_id'] ?? 0);
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $linkedin_url = trim($_POST['linkedin_url'] ?? '');
        $portfolio_url = trim($_POST['portfolio_url'] ?? '');
        
        // Get CV data based on type
        $cv_type = $_POST['cv_type'] ?? '';
        
        // Initialize variables
        $resume_url = '';
        $cover_letter = '';
        
        // Handle CV based on type
        if ($cv_type === 'pdf') {
            if (!isset($_FILES['cv_file']) || $_FILES['cv_file']['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['error' => 'Please upload your CV/Resume PDF file.']);
                exit;
            }
            
            // Get job details for folder structure
            $job_query = $conn->prepare('SELECT title, department, custom_email_body FROM jobs WHERE id = ?');
            $job_query->bind_param('i', $job_id);
            $job_query->execute();
            $job_result = $job_query->get_result();
            
            if ($job_result->num_rows === 0) {
                echo json_encode(['error' => 'Job not found.']);
                exit;
            }
            
            $job_upload = $job_result->fetch_assoc();
            $job_title = preg_replace('/[^a-zA-Z0-9\s-]/', '', $job_upload['title']); // Clean job title for folder name
            $job_title = str_replace(' ', '-', strtolower(trim($job_title)));
            
            // Clean full name for folder name
            $clean_name = preg_replace('/[^a-zA-Z0-9\s-]/', '', $full_name);
            $clean_name = str_replace(' ', '-', strtolower(trim($clean_name)));
            
            // Create upload directory structure
            $upload_dir = "uploads/{$job_title}/{$clean_name}";
            $full_upload_path = "../{$upload_dir}";
            
            if (!is_dir($full_upload_path)) {
                mkdir($full_upload_path, 0755, true);
            }
            
            // Generate unique filename
            $file_extension = pathinfo($_FILES['cv_file']['name'], PATHINFO_EXTENSION);
            $filename = "hisCV." . $file_extension;
            $file_path = $full_upload_path . "/" . $filename;
            
            // Move uploaded file
            if (move_uploaded_file($_FILES['cv_file']['tmp_name'], $file_path)) {
                $resume_url = $upload_dir . "/" . $filename;
            } else {
                echo json_encode(['error' => 'Failed to upload CV file.']);
                exit;
            }
        } else {
            $resume_url = trim($_POST['cv_url'] ?? '');
        }
        
        // Handle cover letter (text only)
        $cover_letter = trim($_POST['cover_letter_text'] ?? '');

        // Validate required fields
        if (!$job_id || !$full_name || !$email || !$phone || !$linkedin_url || !$resume_url || !$cover_letter) {
            echo json_encode(['error' => 'All required fields must be filled.']);
            exit;
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['error' => 'Please enter a valid email address.']);
            exit;
        }
        
        // Validate LinkedIn URL
        if (!filter_var($linkedin_url, FILTER_VALIDATE_URL)) {
            echo json_encode(['error' => 'Please enter a valid LinkedIn profile URL.']);
            exit;
        }
        
        // Validate CV URL if provided as link
        if ($cv_type === 'link' && !filter_var($resume_url, FILTER_VALIDATE_URL)) {
            echo json_encode(['error' => 'Please enter a valid CV/Resume URL.']);
            exit;
        }

        // Check if job exists and is active
        $job_check = $conn->prepare('SELECT id, title, department, custom_email_body FROM jobs WHERE id = ? AND status = "Active"');
        $job_check->bind_param('i', $job_id);
        $job_check->execute();
        $job_result = $job_check->get_result();
        
        if ($job_result->num_rows === 0) {
            echo json_encode(['error' => 'Job position not found or no longer accepting applications.']);
            exit;
        }
        
        $job = $job_result->fetch_assoc();

        // Check if user has already applied for this job (within last 30 days)
        $duplicate_check = $conn->prepare('SELECT id FROM job_applications WHERE job_id = ? AND (email = ? OR phone = ?) AND submitted_at > DATE_SUB(NOW(), INTERVAL 30 DAY)');
        $duplicate_check->bind_param('iss', $job_id, $email, $phone);
        $duplicate_check->execute();
        
        if ($duplicate_check->get_result()->num_rows > 0) {
            echo json_encode(['error' => 'You have already applied for this position within the last 30 days using the same email or phone number.']);
            exit;
        }
        
        // Additional check for any application with same email or phone in last 30 days (across all jobs)
        $global_duplicate_check = $conn->prepare('SELECT id, job_id FROM job_applications WHERE (email = ? OR phone = ?) AND submitted_at > DATE_SUB(NOW(), INTERVAL 30 DAY) LIMIT 1');
        $global_duplicate_check->bind_param('ss', $email, $phone);
        $global_duplicate_check->execute();
        $global_result = $global_duplicate_check->get_result();
        
        if ($global_result->num_rows > 0) {
            $existing_app = $global_result->fetch_assoc();
            if ($existing_app['job_id'] != $job_id) {
                echo json_encode(['error' => 'You have already submitted an application within the last 30 days using the same email or phone number.']);
                exit;
            }
        }

        // Insert application
        $stmt = $conn->prepare('INSERT INTO job_applications (job_id, full_name, email, phone, cover_letter, resume_url, linkedin_url, portfolio_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('isssssss', $job_id, $full_name, $email, $phone, $cover_letter, $resume_url, $linkedin_url, $portfolio_url);
        
        if ($stmt->execute()) {
            // Get the inserted application data for email
            $application_id = $conn->insert_id;
            
            // Prepare application data for email
            $application_data = array(
                'id' => $application_id,
                'full_name' => $full_name,
                'email' => $email,
                'phone' => $phone,
                'cover_letter' => $cover_letter,
                'resume_url' => $resume_url,
                'linkedin_url' => $linkedin_url,
                'portfolio_url' => $portfolio_url,
                'submitted_at' => date('Y-m-d H:i:s')
            );
            
            // Send emails using Hostinger-compatible system
            require_once '../send_email_hostinger.php';
            
            // Debug: Log the data being sent to email functions
            error_log("Application data: " . json_encode($application_data));
            error_log("Job data: " . json_encode($job));
            error_log("Job custom_email_body: " . ($job['custom_email_body'] ?? 'NULL'));
            
            $email_results = sendApplicationEmailsHostinger($application_data, $job);
            
            // Log email results for debugging
            error_log("Email sending results: " . json_encode($email_results));
            
            $response = [
                'success' => true, 
                'message' => 'Application submitted successfully!',
                'job_title' => $job['title'],
                'email_status' => $email_results
            ];
            
            // Add email status to response (for debugging)
            if (!$email_results['admin_sent'] || !$email_results['applicant_sent']) {
                $response['email_warning'] = 'Application saved but some emails may not have been sent.';
                error_log("Email warning: Admin sent: " . ($email_results['admin_sent'] ? 'YES' : 'NO') . ", Applicant sent: " . ($email_results['applicant_sent'] ? 'YES' : 'NO'));
            } else {
                error_log("All emails sent successfully!");
            }
            
            echo json_encode($response);
        } else {
            echo json_encode(['error' => 'Failed to submit application. Please try again.']);
        }

    } catch (Exception $e) {
        error_log("Application submission error: " . $e->getMessage());
        echo json_encode(['error' => 'An error occurred. Please try again later.']);
    }
}

$conn->close();
?>
