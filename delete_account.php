<?php
session_start();
require 'pdo.php';
require 'auth_helper.php';

// Function to delete user account
function deleteUserAccount($pdo, $userId, $password) {
    try {
        // Validate input
        if (empty($password)) {
            return ['success' => false, 'message' => 'Password is required to delete your account.'];
        }

        // Get user data including password
        $stmt = $pdo->prepare("SELECT password, image FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return ['success' => false, 'message' => 'User not found.'];
        }

        // Verify password
        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Incorrect password. Account deletion cancelled.'];
        }

        // Delete user's profile image if exists
        if (!empty($user['image'])) {
            if (file_exists('image_upload_helper.php')) {
                require 'image_upload_helper.php';
                deleteProfileImage($user['image']);
            }
        }

        // Delete user account from database
        $deleteStmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
        $result = $deleteStmt->execute([$userId]);

        if ($result && $deleteStmt->rowCount() > 0) {
            // Destroy session
            $_SESSION = array();
            
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
            
            session_destroy();

            // Clear remember me cookie if exists
            if (isset($_COOKIE['remember_token'])) {
                setcookie('remember_token', '', time() - 3600, '/', '', false, true);
            }

            return ['success' => true, 'message' => 'Your account has been permanently deleted.'];
        } else {
            return ['success' => false, 'message' => 'Failed to delete account. Please try again.'];
        }

    } catch (PDOException $e) {
        $errorMessage = 'An error occurred while deleting your account.';
        if (ini_get('display_errors')) {
            $errorMessage .= ' Error: ' . $e->getMessage();
        }
        error_log("Account deletion error: " . $e->getMessage());
        return ['success' => false, 'message' => $errorMessage, 'error' => $e->getMessage()];
    }
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if user is logged in
    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'You must be logged in to delete your account.']);
        exit;
    }

    // Get input
    $userId = getUserId();
    $password = $_POST['password'] ?? '';

    // Delete the account
    $result = deleteUserAccount($pdo, $userId, $password);

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

