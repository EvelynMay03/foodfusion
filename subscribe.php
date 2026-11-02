<?php
session_start();
require 'pdo.php';
require 'auth_helper.php';

// Set JSON header
header('Content-Type: application/json');

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get email from POST
    $email = trim($_POST['email'] ?? '');
    
    // Validate email
    if (empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Email is required.']);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
        exit;
    }
    
    // Validate email length (max 100 characters to match VARCHAR(100))
    if (strlen($email) > 100) {
        echo json_encode(['success' => false, 'message' => 'Email must be 100 characters or less.']);
        exit;
    }
    
    // Get user_id if logged in
    $userId = null;
    if (isLoggedIn()) {
        $userId = getUserId();
    }
    
    try {
        // Check if subscription table exists
        $checkTable = $pdo->query("SHOW TABLES LIKE 'subscription'");
        if ($checkTable->rowCount() === 0) {
            echo json_encode(['success' => false, 'message' => 'Subscription table does not exist. Please create it first.']);
            exit;
        }
        
        // Check if email is already subscribed
        $checkEmail = $pdo->prepare("SELECT sid, user_id FROM subscription WHERE email = ?");
        $checkEmail->execute([$email]);
        $existingSubscription = $checkEmail->fetch(PDO::FETCH_ASSOC);
        
        if ($existingSubscription) {
            // If subscription exists, update user_id if user is logged in and subscription doesn't have a user_id
            if ($userId && !$existingSubscription['user_id']) {
                $updateStmt = $pdo->prepare("UPDATE subscription SET user_id = ? WHERE email = ?");
                $updateResult = $updateStmt->execute([$userId, $email]);
                
                if ($updateResult) {
                    echo json_encode(['success' => true, 'message' => 'Subscription updated successfully!']);
                } else {
                    $updateError = $updateStmt->errorInfo();
                    error_log("Update subscription failed: " . ($updateError[2] ?? 'Unknown error'));
                    echo json_encode(['success' => false, 'message' => 'Failed to update subscription.']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'This email is already subscribed.']);
            }
            exit;
        }
        
        // If user_id is provided, verify it exists in users table
        if ($userId !== null) {
            $checkUser = $pdo->prepare("SELECT user_id FROM users WHERE user_id = ?");
            $checkUser->execute([$userId]);
            if ($checkUser->rowCount() === 0) {
                error_log("Invalid user_id provided: " . $userId);
                $userId = null; // Set to null if user doesn't exist
            }
        }
        
        // Insert new subscription
        $stmt = $pdo->prepare("INSERT INTO subscription (email, user_id) VALUES (?, ?)");
        
        try {
            $result = $stmt->execute([$email, $userId]);
        } catch (PDOException $insertException) {
            error_log("Subscription insert exception: " . $insertException->getMessage());
            echo json_encode(['success' => false, 'message' => 'Subscription failed: ' . $insertException->getMessage(), 'error' => $insertException->getMessage()]);
            exit;
        }
        
        // Check if execute was successful
        if ($result === false) {
            $errorInfo = $stmt->errorInfo();
            $errorMsg = $errorInfo[2] ?? 'Unknown error';
            error_log("Subscription insert failed: " . $errorMsg);
            echo json_encode(['success' => false, 'message' => 'Subscription failed: ' . $errorMsg, 'error' => $errorMsg]);
            exit;
        }
        
        // Verify by checking if the email exists in the database
        $verifyStmt = $pdo->prepare("SELECT sid, email, user_id FROM subscription WHERE email = ?");
        $verifyStmt->execute([$email]);
        $subscription = $verifyStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($subscription) {
            // Success - subscription was inserted
            echo json_encode(['success' => true, 'message' => 'Thank you for subscribing! You will receive our latest recipes and cooking tips.']);
        } else {
            // Insert seemed to succeed but record not found
            $errorInfo = $stmt->errorInfo();
            error_log("Subscription verification failed: Record not found after insert. Email: " . $email);
            echo json_encode(['success' => false, 'message' => 'Subscription failed. Please try again.', 'error' => 'Verification failed']);
        }
        
    } catch (PDOException $e) {
        error_log("Subscription error: " . $e->getMessage());
        $errorMessage = 'An error occurred. Please try again later.';
        if (ini_get('display_errors')) {
            $errorMessage .= ' Error: ' . $e->getMessage();
        }
        echo json_encode(['success' => false, 'message' => $errorMessage, 'error' => $e->getMessage()]);
    }
    
} else {
    // If not POST request, return error
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid request method. This endpoint only accepts POST requests. Please use the subscription form on the website.',
        'error' => 'Expected POST request, received ' . $_SERVER['REQUEST_METHOD']
    ]);
}
?>
