<?php
session_start();
require 'pdo.php';
require 'auth_helper.php';

// Function to update user profile
function updateProfile($pdo, $userId, $firstname, $lastname, $email, $imageFile = null) {
    try {
        // Validate input
        if (empty($firstname) || empty($lastname) || empty($email)) {
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

        // Check if email is being changed and if new email already exists
        $currentUserStmt = $pdo->prepare("SELECT email FROM users WHERE user_id = ?");
        $currentUserStmt->execute([$userId]);
        $currentUser = $currentUserStmt->fetch(PDO::FETCH_ASSOC);

        if ($currentUser['email'] !== $email) {
            // Email is being changed, check if new email exists
            $checkEmail = $pdo->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
            $checkEmail->execute([$email, $userId]);
            
            if ($checkEmail->rowCount() > 0) {
                return ['success' => false, 'message' => 'Email already exists. Please use a different email.'];
            }
        }

        // Handle image upload if provided
        $imageFilename = null;
        if ($imageFile && file_exists('image_upload_helper.php')) {
            require 'image_upload_helper.php';
            
            // Get current user image before update
            try {
                $currentImageStmt = $pdo->prepare("SELECT image FROM users WHERE user_id = ?");
                $currentImageStmt->execute([$userId]);
                $currentImageData = $currentImageStmt->fetch(PDO::FETCH_ASSOC);
                $oldImage = $currentImageData['image'] ?? null;
            } catch (PDOException $e) {
                $oldImage = null;
            }

            if (isset($imageFile['tmp_name']) && !empty($imageFile['tmp_name'])) {
                $uploadResult = uploadProfileImage($imageFile, $userId);
                if ($uploadResult['success']) {
                    // Delete old image if exists
                    if ($oldImage) {
                        deleteProfileImage($oldImage);
                    }
                    $imageFilename = $uploadResult['filename'];
                } else {
                    // Image upload failed
                    return ['success' => false, 'message' => $uploadResult['message']];
                }
            } else {
                // No new image uploaded, keep old one
                $imageFilename = $oldImage;
            }
        }

        // Update user profile - check if image column exists
        $imageColumnExists = false;
        if ($imageFilename !== null) {
            // Check if image column exists
            try {
                $checkColumn = $pdo->query("SHOW COLUMNS FROM users LIKE 'image'");
                $imageColumnExists = $checkColumn->rowCount() > 0;
            } catch (PDOException $e) {
                $imageColumnExists = false;
            }
        }
        
        try {
            if ($imageFilename !== null && $imageColumnExists && file_exists('image_upload_helper.php')) {
                // Update with image column
                $stmt = $pdo->prepare("UPDATE users SET firstname = ?, lastname = ?, email = ?, image = ? WHERE user_id = ?");
                $result = $stmt->execute([$firstname, $lastname, $email, $imageFilename, $userId]);
            } else {
                // Update without image column
                $stmt = $pdo->prepare("UPDATE users SET firstname = ?, lastname = ?, email = ? WHERE user_id = ?");
                $result = $stmt->execute([$firstname, $lastname, $email, $userId]);
                
                // If image was uploaded but column doesn't exist, log warning
                if ($imageFilename !== null && !$imageColumnExists) {
                    error_log("Image uploaded but 'image' column doesn't exist in users table. Please add the column.");
                }
            }
        } catch (PDOException $e) {
            // If update fails, try without image column
            error_log("Profile update error: " . $e->getMessage());
            $stmt = $pdo->prepare("UPDATE users SET firstname = ?, lastname = ?, email = ? WHERE user_id = ?");
            $result = $stmt->execute([$firstname, $lastname, $email, $userId]);
        }

        if ($result && $stmt->rowCount() > 0) {
            // Update session variables
            $_SESSION['user_firstname'] = $firstname;
            $_SESSION['user_lastname'] = $lastname;
            $_SESSION['user_email'] = $email;
            
            // Update session image if image was uploaded
            if ($imageFilename !== null && file_exists('image_upload_helper.php')) {
                $_SESSION['user_image'] = $imageFilename;
            }

            $message = 'Profile updated successfully!';
            if ($imageFilename !== null && isset($imageFile)) {
                $message .= ' Profile picture updated.';
            }
            
            return ['success' => true, 'message' => $message];
        } else {
            return ['success' => false, 'message' => 'No changes were made to your profile.'];
        }

    } catch (PDOException $e) {
        $errorMessage = 'An error occurred while updating your profile.';
        if (ini_get('display_errors')) {
            $errorMessage .= ' Error: ' . $e->getMessage();
        }
        error_log("Profile update error: " . $e->getMessage());
        return ['success' => false, 'message' => $errorMessage, 'error' => $e->getMessage()];
    }
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if user is logged in
    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'You must be logged in to update your profile.']);
        exit;
    }

    // Get and sanitize input
    $userId = getUserId();
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $email = trim($_POST['email'] ?? '');

    // Handle image upload
    $imageFile = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $imageFile = $_FILES['image'];
    }

    // Update the profile
    $result = updateProfile($pdo, $userId, $firstname, $lastname, $email, $imageFile);

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

