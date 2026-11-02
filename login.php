<?php
session_start();
require 'pdo.php';

// Function to login a user
function loginUser($pdo, $email, $password, $rememberMe = false) {
    try {
        // Validate input
        if (empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Email and password are required.'];
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email format.'];
        }


        try {
            $stmt = $pdo->prepare("SELECT user_id, firstname, lastname, email, password, image, fail_attempts, lock_time FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
           
            $stmt = $pdo->prepare("SELECT user_id, firstname, lastname, email, password, fail_attempts, lock_time FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $user['image'] = null;
        }

        if (!$user) {
            return ['success' => false, 'message' => 'Invalid email or password.'];
        }

        // Check if account is locked
        if (!empty($user['lock_time']) && $user['lock_time'] !== 'NULL') {
            // Try to parse lock_time (could be timestamp string or datetime)
            $lockTime = strtotime($user['lock_time']);
            $currentTime = time();
            $lockDuration = 3 * 60; // 3 minutes in seconds
            
            // If lock_time is valid and within lock duration
            if ($lockTime !== false && ($currentTime - $lockTime) < $lockDuration) {
                $remainingTime = ceil(($lockDuration - ($currentTime - $lockTime)) / 60);
                return ['success' => false, 'message' => 'Account is locked. Please try again in ' . $remainingTime . ' minutes.'];
            } else {
                // Unlock the account after lock duration
                $unlockStmt = $pdo->prepare("UPDATE users SET fail_attempts = NULL, lock_time = NULL WHERE user_id = ?");
                $unlockStmt->execute([$user['user_id']]);
                $user['fail_attempts'] = null;
                $user['lock_time'] = null;
            }
        }

        // Verify password
        if (!password_verify($password, $user['password'])) {
            // Increment failed attempts
            $failAttempts = ($user['fail_attempts'] ?? 0) + 1;
            $maxAttempts = 3;
            
            if ($failAttempts >= $maxAttempts) {
                // Lock the account - store current timestamp as string
                $lockStmt = $pdo->prepare("UPDATE users SET fail_attempts = ?, lock_time = ? WHERE user_id = ?");
                $lockStmt->execute([$failAttempts, date('Y-m-d H:i:s'), $user['user_id']]);
                return ['success' => false, 'message' => 'Too many failed login attempts. Your account has been locked for 3 minutes.'];
            } else {
                // Update failed attempts
                $updateStmt = $pdo->prepare("UPDATE users SET fail_attempts = ? WHERE user_id = ?");
                $updateStmt->execute([$failAttempts, $user['user_id']]);
                $remainingAttempts = $maxAttempts - $failAttempts;
                return ['success' => false, 'message' => 'Invalid email or password. ' . $remainingAttempts . ' attempt(s) remaining.'];
            }
        }

        // Login successful - reset failed attempts
        $resetStmt = $pdo->prepare("UPDATE users SET fail_attempts = NULL, lock_time = NULL WHERE user_id = ?");
        $resetStmt->execute([$user['user_id']]);

        // Set session variables
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_firstname'] = $user['firstname'];
        $_SESSION['user_lastname'] = $user['lastname'];
        $_SESSION['logged_in'] = true;
        
        // Set user image if exists
        if (isset($user['image']) && !empty($user['image'])) {
            $_SESSION['user_image'] = $user['image'];
        }

        // Handle "Remember Me" functionality
        if ($rememberMe) {
            // Set cookie to expire in 30 days
            $cookieValue = base64_encode($user['user_id'] . ':' . hash('sha256', $user['email'] . $user['password']));
            setcookie('remember_token', $cookieValue, time() + (30 * 24 * 60 * 60), '/', '', false, true);
        }

        return [
            'success' => true, 
            'message' => 'Login successful! Welcome back, ' . $user['firstname'] . '!',
            'user_id' => $user['user_id'],
            'user' => [
                'firstname' => $user['firstname'],
                'lastname' => $user['lastname'],
                'email' => $user['email']
            ]
        ];

    } catch (PDOException $e) {
        $errorMessage = 'An error occurred during login.';
        if (ini_get('display_errors')) {
            $errorMessage .= ' Error: ' . $e->getMessage();
        }
        error_log("Login error: " . $e->getMessage());
        return ['success' => false, 'message' => $errorMessage, 'error' => $e->getMessage()];
    }
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize input
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $rememberMe = isset($_POST['rememberMe']) ? true : false;

    // Login the user
    $result = loginUser($pdo, $email, $password, $rememberMe);

    // Check if this is an AJAX request
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
              strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    
    // Also check Accept header for JSON
    $acceptsJson = !empty($_SERVER['HTTP_ACCEPT']) && 
                   strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;

    // If AJAX request or accepts JSON, return JSON response
    if ($isAjax || $acceptsJson) {
        // Set headers to prevent caching and ensure JSON content type
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
        
        // For debugging: include error details if available
        if (isset($result['error']) && ini_get('display_errors')) {
            $result['debug'] = $result['error'];
        }
        
        echo json_encode($result);
        exit;
    } else {
        // If not AJAX, redirect based on result
        if ($result['success']) {
            // Login successful - redirect to home page
            header('Location: index.php');
            exit;
        } else {
            // Login failed - redirect back with error message
            $_SESSION['login_error'] = $result['message'];
            header('Location: index.php');
            exit;
        }
    }
} else {
    // If not POST request, redirect to home
    header('Location: index.php');
    exit;
}
?>

