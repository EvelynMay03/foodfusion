<?php
session_start();
require 'pdo.php';
require 'auth_helper.php';

// Function to update user password
function updatePassword($pdo, $userId, $currentPassword, $newPassword) {
    try {
        // Validate input
        if (empty($currentPassword) || empty($newPassword)) {
            return ['success' => false, 'message' => 'Current password and new password are required.'];
        }

        // Validate new password length (minimum 8 characters)
        if (strlen($newPassword) < 8) {
            return ['success' => false, 'message' => 'New password must be at least 8 characters long.'];
        }

        // Get current user password from database
        $stmt = $pdo->prepare("SELECT password FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return ['success' => false, 'message' => 'User not found.'];
        }

        // Verify current password
        if (!password_verify($currentPassword, $user['password'])) {
            return ['success' => false, 'message' => 'Current password is incorrect.'];
        }

        // Check if new password is the same as current password
        if (password_verify($newPassword, $user['password'])) {
            return ['success' => false, 'message' => 'New password must be different from your current password.'];
        }

        // Hash the new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update password in database
        $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $result = $updateStmt->execute([$hashedPassword, $userId]);

        if ($result && $updateStmt->rowCount() > 0) {
            // Reset failed login attempts on password change
            $resetStmt = $pdo->prepare("UPDATE users SET fail_attempts = NULL, lock_time = NULL WHERE user_id = ?");
            $resetStmt->execute([$userId]);

            return ['success' => true, 'message' => 'Password changed successfully!'];
        } else {
            return ['success' => false, 'message' => 'Failed to update password. Please try again.'];
        }

    } catch (PDOException $e) {
        $errorMessage = 'An error occurred while changing your password.';
        if (ini_get('display_errors')) {
            $errorMessage .= ' Error: ' . $e->getMessage();
        }
        error_log("Password update error: " . $e->getMessage());
        return ['success' => false, 'message' => $errorMessage, 'error' => $e->getMessage()];
    }
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if user is logged in
    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'You must be logged in to change your password.']);
        exit;
    }

    // Get input
    $userId = getUserId();
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmNewPassword = $_POST['confirm_new_password'] ?? '';

    // Validate password confirmation
    if ($newPassword !== $confirmNewPassword) {
        echo json_encode(['success' => false, 'message' => 'New passwords do not match.']);
        exit;
    }

    // Update the password
    $result = updatePassword($pdo, $userId, $currentPassword, $newPassword);

    // Return JSON response
    header('Content-Type: application/json');
    
    // For debugging: include error details if available
    if (isset($result['error']) && ini_get('display_errors')) {
        $result['debug'] = $result['error'];
    }
    
    echo json_encode($result);
    exit;
} else {
    // If not POST request, redirect to profile page
    header('Location: profile.php');
    exit;
}
?>

