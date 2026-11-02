<?php
session_start();
require 'pdo.php';

// Function to register a new user
function registerUser($pdo, $firstname, $lastname, $email, $password) {
    try {
        // Validate input
        if (empty($firstname) || empty($lastname) || empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'All fields are required.'];
        }

        // Validate firstname and lastname length (max 20 characters)
        if (strlen($firstname) > 20) {
            return ['success' => false, 'message' => 'First name must be 20 characters or less.'];
        }
        
        if (strlen($lastname) > 20) {
            return ['success' => false, 'message' => 'Last name must be 20 characters or less.'];
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email format.'];
        }

        // Validate email length (max 150 characters)
        if (strlen($email) > 150) {
            return ['success' => false, 'message' => 'Email must be 150 characters or less.'];
        }

        // Validate password length (minimum 8 characters recommended)
        if (strlen($password) < 8) {
            return ['success' => false, 'message' => 'Password must be at least 8 characters long.'];
        }

        // Validate password complexity
        $hasUppercase = preg_match('/[A-Z]/', $password);
        $hasLowercase = preg_match('/[a-z]/', $password);
        $hasSpecialChar = preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password);

        if (!$hasUppercase) {
            return ['success' => false, 'message' => 'Password must contain at least one uppercase letter.'];
        }
        
        if (!$hasLowercase) {
            return ['success' => false, 'message' => 'Password must contain at least one lowercase letter.'];
        }
        
        if (!$hasSpecialChar) {
            return ['success' => false, 'message' => 'Password must contain at least one special character (!@#$%^&*(),.?":{}|<>).'];
        }

        // Check if email already exists
        $checkEmail = $pdo->prepare("SELECT email FROM users WHERE email = ?");
        $checkEmail->execute([$email]);
        
        if ($checkEmail->rowCount() > 0) {
            return ['success' => false, 'message' => 'Email already exists. Please use a different email.'];
        }

        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user into database
        // Note: fail_attempts and lock_time are nullable, so we don't need to include them
        $stmt = $pdo->prepare("INSERT INTO users (firstname, lastname, email, password) VALUES (?, ?, ?, ?)");
        
        $result = $stmt->execute([$firstname, $lastname, $email, $hashedPassword]);
        
        // Check if execute was successful
        if ($result === false) {
            // Get error info from the statement
            $errorInfo = $stmt->errorInfo();
            return ['success' => false, 'message' => 'Registration failed: ' . ($errorInfo[2] ?? 'Unknown error')];
        }
        
        // Get the newly created user ID
        $userId = $pdo->lastInsertId();
        
        // Verify the insert was successful by checking if we got a user ID and row count
        if ($userId && $stmt->rowCount() > 0) {
            // Double-check by querying the database
            $verifyStmt = $pdo->prepare("SELECT user_id, firstname, lastname, email FROM users WHERE user_id = ?");
            $verifyStmt->execute([$userId]);
            $user = $verifyStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // Set session variables
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_firstname'] = $firstname;
                $_SESSION['user_lastname'] = $lastname;
                
                return ['success' => true, 'message' => 'Registration successful! Welcome to FoodFusion.', 'user_id' => $userId];
            } else {
                return ['success' => false, 'message' => 'Registration failed: User was not found after insertion.'];
            }
        } else {
            return ['success' => false, 'message' => 'Registration failed: Could not retrieve user ID or no rows were inserted.'];
        }

    } catch (PDOException $e) {
        // Return detailed error for debugging (remove detailed error in production)
        $errorMessage = 'An error occurred during registration.';
        if (ini_get('display_errors')) {
            $errorMessage .= ' Error: ' . $e->getMessage();
        }
        error_log("Registration error: " . $e->getMessage());
        return ['success' => false, 'message' => $errorMessage, 'error' => $e->getMessage()];
    }
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize input
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $terms = isset($_POST['terms']) ? true : false;

    // Validate terms acceptance
    if (!$terms) {
        echo json_encode(['success' => false, 'message' => 'You must agree to the Terms of Service and Privacy Policy.']);
        exit;
    }

    // Validate password confirmation
    if ($password !== $confirmPassword) {
        echo json_encode(['success' => false, 'message' => 'Passwords do not match.']);
        exit;
    }

    // Register the user
    $result = registerUser($pdo, $firstname, $lastname, $email, $password);

    // Return JSON response
    header('Content-Type: application/json');
    
    // For debugging: include error details if available
    if (isset($result['error']) && ini_get('display_errors')) {
        $result['debug'] = $result['error'];
    }
    
    echo json_encode($result);
    exit;
} else {
    // If not POST request, redirect to home
    header('Location: index.php');
    exit;
}
?>

