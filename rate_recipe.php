<?php
session_start();
require 'pdo.php';
require 'auth_helper.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please log in to rate recipes.']);
    exit;
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Get user ID
$userId = getUserId();

// Validate and get recipe_id and rating
$recipeId = isset($_POST['recipe_id']) ? intval($_POST['recipe_id']) : 0;
$rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;

if ($recipeId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid recipe ID.']);
    exit;
}

if ($rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Rating must be between 1 and 5.']);
    exit;
}

try {
    // Check if ratings table exists, if not create it
    $checkTable = $pdo->query("SHOW TABLES LIKE 'ratings'");
    if ($checkTable->rowCount() === 0) {
        $pdo->exec("CREATE TABLE IF NOT EXISTS ratings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            recipe_id INT NOT NULL,
            user_id INT NOT NULL,
            rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE (recipe_id, user_id),
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
    
    // Insert or update rating (using ON DUPLICATE KEY UPDATE)
    $stmt = $pdo->prepare("
        INSERT INTO ratings (recipe_id, user_id, rating) 
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE rating = ?, created_at = CURRENT_TIMESTAMP
    ");
    
    $stmt->execute([$recipeId, $userId, $rating, $rating]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Rating submitted successfully!'
    ]);
    
} catch (PDOException $e) {
    error_log("Error submitting rating: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to submit rating. Please try again.']);
}
