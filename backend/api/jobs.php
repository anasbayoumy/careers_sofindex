<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../database/db_connection.php';

try {
    // Check if this is a method override (for PUT/DELETE via POST)
    $method = $_SERVER['REQUEST_METHOD'];
    if ($method === 'POST' && isset($_GET['_method'])) {
        $method = strtoupper($_GET['_method']);
    }
    
    switch ($method) {
        case 'GET':
            // Get all jobs or specific job by ID (Admin can see all jobs)
            $job_id = $_GET['id'] ?? null;
            
            if ($job_id) {
                // Get specific job
                $stmt = $conn->prepare('SELECT * FROM jobs WHERE id = ?');
                $stmt->bind_param('i', $job_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 0) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Job not found']);
                    exit;
                }
                
                $job = $result->fetch_assoc();
                
                // Get applications count for this job
                $app_stmt = $conn->prepare('SELECT COUNT(*) as application_count FROM job_applications WHERE job_id = ?');
                $app_stmt->bind_param('i', $job_id);
                $app_stmt->execute();
                $app_result = $app_stmt->get_result();
                $app_count = $app_result->fetch_assoc()['application_count'];
                
                $job['application_count'] = $app_count;
                
                echo json_encode(['success' => true, 'job' => $job]);
            } else {
                // Get all jobs with optional filters (Admin sees all jobs)
                $department = $_GET['department'] ?? '';
                $location = $_GET['location'] ?? '';
                $type = $_GET['type'] ?? '';
                $status = $_GET['status'] ?? '';
                
                $where_conditions = [];
                $params = [];
                $types = '';
                
                if ($department) {
                    $where_conditions[] = 'department = ?';
                    $params[] = $department;
                    $types .= 's';
                }
                
                if ($location) {
                    $where_conditions[] = 'location = ?';
                    $params[] = $location;
                    $types .= 's';
                }
                
                if ($type) {
                    $where_conditions[] = 'type = ?';
                    $params[] = $type;
                    $types .= 's';
                }
                
                if ($status) {
                    $where_conditions[] = 'status = ?';
                    $params[] = $status;
                    $types .= 's';
                }
                
                $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
                // For user frontend, only show active jobs
                // For admin panel, show all jobs
                if (empty($status) && !isset($_GET['admin'])) {
                    $sql = "SELECT * FROM jobs WHERE status = 'Active' $where_clause ORDER BY posted DESC, created_at DESC";
                } else {
                    $sql = "SELECT * FROM jobs $where_clause ORDER BY posted DESC, created_at DESC";
                }
                
                if (!empty($params)) {
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param($types, ...$params);
                } else {
                    $stmt = $conn->prepare($sql);
                }
                
                $stmt->execute();
                $result = $stmt->get_result();
                $jobs = [];
                
                while ($row = $result->fetch_assoc()) {
                    // Get applications count for each job
                    $app_stmt = $conn->prepare('SELECT COUNT(*) as application_count FROM job_applications WHERE job_id = ?');
                    $app_stmt->bind_param('i', $row['id']);
                    $app_stmt->execute();
                    $app_result = $app_stmt->get_result();
                    $row['application_count'] = $app_result->fetch_assoc()['application_count'];
                    
                    $jobs[] = $row;
                }
                
                echo json_encode(['success' => true, 'jobs' => $jobs]);
            }
            break;
            
        case 'POST':
            // Create new job (Admin only)
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid JSON data']);
                exit;
            }
            
            $required_fields = ['title', 'department', 'location', 'type', 'experience', 'description', 'responsibilities', 'requirements', 'benefits', 'posted'];
            foreach ($required_fields as $field) {
                if (empty($data[$field])) {
                    http_response_code(400);
                    echo json_encode(['error' => "Missing required field: $field"]);
                    exit;
                }
            }
            
            $stmt = $conn->prepare('INSERT INTO jobs (title, department, location, type, experience, description, responsibilities, requirements, benefits, custom_email_body, posted, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $status = $data['status'] ?? 'Active';
            $custom_email_body = !empty($data['custom_email_body']) ? $data['custom_email_body'] : 'Thank you for your application!';
            $stmt->bind_param('ssssssssssss', 
                $data['title'], 
                $data['department'], 
                $data['location'], 
                $data['type'], 
                $data['experience'], 
                $data['description'], 
                $data['responsibilities'], 
                $data['requirements'], 
                $data['benefits'], 
                $custom_email_body,
                $data['posted'],
                $status
            );
            
            if ($stmt->execute()) {
                $job_id = $conn->insert_id;
                echo json_encode(['success' => true, 'message' => 'Job created successfully', 'job_id' => $job_id]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to create job: ' . $conn->error]);
            }
            break;
            
        case 'PUT':
            // Update existing job (Admin only)
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid JSON data']);
                exit;
            }
            
            if (empty($data['id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Job ID is required']);
                exit;
            }
            
            $job_id = $data['id'];
            
            // Check if job exists
            $check_stmt = $conn->prepare('SELECT id FROM jobs WHERE id = ?');
            $check_stmt->bind_param('i', $job_id);
            $check_stmt->execute();
            if ($check_stmt->get_result()->num_rows === 0) {
                http_response_code(404);
                echo json_encode(['error' => 'Job not found']);
                exit;
            }
            
            $stmt = $conn->prepare('UPDATE jobs SET title = ?, department = ?, location = ?, type = ?, experience = ?, description = ?, responsibilities = ?, requirements = ?, benefits = ?, custom_email_body = ?, posted = ?, status = ? WHERE id = ?');
            $custom_email_body = !empty($data['custom_email_body']) ? $data['custom_email_body'] : 'Thank you for your application!';
            $stmt->bind_param('ssssssssssssi', 
                $data['title'], 
                $data['department'], 
                $data['location'], 
                $data['type'], 
                $data['experience'], 
                $data['description'], 
                $data['responsibilities'], 
                $data['requirements'], 
                $data['benefits'], 
                $custom_email_body,
                $data['posted'],
                $data['status'], 
                $job_id
            );
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Job updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to update job: ' . $conn->error]);
            }
            break;
            
        case 'DELETE':
            // Delete job (Admin only)
            $job_id = $_GET['id'] ?? null;
            if (!$job_id) {
                http_response_code(400);
                echo json_encode(['error' => 'Job ID is required']);
                exit;
            }
            
            // Check if job exists
            $check_stmt = $conn->prepare('SELECT id FROM jobs WHERE id = ?');
            $check_stmt->bind_param('i', $job_id);
            $check_stmt->execute();
            if ($check_stmt->get_result()->num_rows === 0) {
                http_response_code(404);
                echo json_encode(['error' => 'Job not found']);
                exit;
            }
            
            // Check if job has applications
            $app_check = $conn->prepare('SELECT COUNT(*) as app_count FROM job_applications WHERE job_id = ?');
            $app_check->bind_param('i', $job_id);
            $app_check->execute();
            $app_count = $app_check->get_result()->fetch_assoc()['app_count'];
            
            if ($app_count > 0) {
                // Soft delete - change status to Closed
                $stmt = $conn->prepare('UPDATE jobs SET status = "Closed" WHERE id = ?');
                $stmt->bind_param('i', $job_id);
                
                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Job closed successfully (applications exist)']);
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'Failed to close job: ' . $conn->error]);
                }
            } else {
                // Hard delete - no applications exist
                $stmt = $conn->prepare('DELETE FROM jobs WHERE id = ?');
                $stmt->bind_param('i', $job_id);
                
                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Job deleted successfully']);
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'Failed to delete job: ' . $conn->error]);
                }
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
    
} catch (Exception $e) {
    error_log("Jobs API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error: ' . $e->getMessage()]);
}

$conn->close();
?>
