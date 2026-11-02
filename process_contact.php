<?php
session_start();
require 'pdo.php';
require 'auth_helper.php';

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

try {
    // Get form data
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Get user ID if logged in
    $userId = isLoggedIn() ? getUserId() : null;
    
    // Validation
    if (empty($name) || strlen($name) > 50) {
        echo json_encode(['success' => false, 'message' => 'Name is required and must be 50 characters or less.']);
        exit;
    }
    
    if (empty($email) || strlen($email) > 100) {
        echo json_encode(['success' => false, 'message' => 'Email is required and must be 100 characters or less.']);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
        exit;
    }
    
    if (empty($subject) || strlen($subject) > 255) {
        echo json_encode(['success' => false, 'message' => 'Subject is required and must be 255 characters or less.']);
        exit;
    }
    
    if (empty($message)) {
        echo json_encode(['success' => false, 'message' => 'Message is required.']);
        exit;
    }
    
    // Check if contact table exists, if not create it
    try {
        $checkTable = $pdo->query("SHOW TABLES LIKE 'contact'");
        if ($checkTable->rowCount() === 0) {
            // Create contact table
            $pdo->exec("CREATE TABLE IF NOT EXISTS contact (
                contact_id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(50) NOT NULL,
                email VARCHAR(100) NOT NULL,
                subject VARCHAR(255) NOT NULL,
                message TEXT NOT NULL,
                user_id INT, 
                submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
            )");
        }
    } catch (PDOException $e) {
        error_log("Error checking/creating contact table: " . $e->getMessage());
        // Continue anyway - table might already exist
    }
    
    // Insert contact message into database
    $insertStmt = $pdo->prepare("
        INSERT INTO contact (name, email, subject, message, user_id) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $result = $insertStmt->execute([
        $name,
        $email,
        $subject,
        $message,
        $userId
    ]);
    
    if ($result) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Thank you for your message! We will get back to you soon.'
        ]);
    } else {
        $errorInfo = $insertStmt->errorInfo();
        error_log("Contact form insertion error: " . print_r($errorInfo, true));
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Failed to send message. Please try again later.'
        ]);
    }
    
} catch (PDOException $e) {
    error_log("Contact form error: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while sending your message. Please try again later.'
    ]);
} catch (Exception $e) {
    error_log("Contact form general error: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'An unexpected error occurred. Please try again later.'
    ]);
}
?>
