<?php
session_start();
require 'pdo.php';
require 'auth_helper.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please log in to add comments.']);
    exit;
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Get user ID
$userId = getUserId();

// Validate and get recipe_id and comment
$recipeId = isset($_POST['recipe_id']) ? intval($_POST['recipe_id']) : 0;
$comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

if ($recipeId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid recipe ID.']);
    exit;
}

if (empty($comment)) {
    echo json_encode(['success' => false, 'message' => 'Comment cannot be empty.']);
    exit;
}

// Validate comment length (max 2000 characters)
if (strlen($comment) > 2000) {
    echo json_encode(['success' => false, 'message' => 'Comment is too long. Maximum 2000 characters allowed.']);
    exit;
}

try {
    // Check if comments table exists, if not create it
    $checkTable = $pdo->query("SHOW TABLES LIKE 'comments'");
    if ($checkTable->rowCount() === 0) {
        $pdo->exec("CREATE TABLE IF NOT EXISTS comments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            recipe_id INT NOT NULL,
            user_id INT NOT NULL,
            comment TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (recipe_id) REFERENCES recipes(recipe_id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
        )");
    }
    
    // Verify recipe exists
    $recipeCheck = $pdo->prepare("SELECT recipe_id FROM recipes WHERE recipe_id = ?");
    $recipeCheck->execute([$recipeId]);
    if ($recipeCheck->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Recipe not found.']);
        exit;
    }
    
    // Insert comment
    $stmt = $pdo->prepare("
        INSERT INTO comments (recipe_id, user_id, comment) 
        VALUES (?, ?, ?)
    ");
    
    $stmt->execute([$recipeId, $userId, $comment]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Comment posted successfully!'
    ]);
    
} catch (PDOException $e) {
    error_log("Error posting comment: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to post comment. Please try again.']);
}
