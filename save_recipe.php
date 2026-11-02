<?php
session_start();
require 'pdo.php';
require 'auth_helper.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'You must be logged in to save recipes.']);
    exit;
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

try {
    $userId = getUserId();
    $recipeId = intval($_POST['recipe_id'] ?? 0);
    $action = trim($_POST['action'] ?? ''); // 'save' or 'unsave'
    
    if ($recipeId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid recipe ID.']);
        exit;
    }
    
    if (!in_array($action, ['save', 'unsave'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
        exit;
    }
    
    // Ensure saved_recipes table exists
    try {
        $checkTable = $pdo->query("SHOW TABLES LIKE 'saved_recipes'");
        if ($checkTable->rowCount() === 0) {
            $pdo->exec("CREATE TABLE IF NOT EXISTS saved_recipes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                recipe_id INT NOT NULL,
                saved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_user_recipe (user_id, recipe_id),
                FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
                FOREIGN KEY (recipe_id) REFERENCES recipes(recipe_id) ON DELETE CASCADE
            )");
        }
    } catch (PDOException $e) {
        error_log("Error creating saved_recipes table: " . $e->getMessage());
    }
    
    // Verify recipe exists
    $checkRecipe = $pdo->prepare("SELECT recipe_id FROM recipes WHERE recipe_id = ?");
    $checkRecipe->execute([$recipeId]);
    if ($checkRecipe->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Recipe not found.']);
        exit;
    }
    
    if ($action === 'save') {
        // Save recipe
        try {
            $saveStmt = $pdo->prepare("INSERT INTO saved_recipes (user_id, recipe_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE saved_at = CURRENT_TIMESTAMP");
            $saveStmt->execute([$userId, $recipeId]);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Recipe saved successfully!',
                'action' => 'saved'
            ]);
        } catch (PDOException $e) {
            error_log("Error saving recipe: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Failed to save recipe. Please try again.'
            ]);
        }
    } else {
        // Unsave recipe
        try {
            $unsaveStmt = $pdo->prepare("DELETE FROM saved_recipes WHERE user_id = ? AND recipe_id = ?");
            $unsaveStmt->execute([$userId, $recipeId]);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Recipe removed from saved recipes.',
                'action' => 'unsaved'
            ]);
        } catch (PDOException $e) {
            error_log("Error unsaving recipe: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Failed to remove recipe. Please try again.'
            ]);
        }
    }
    
} catch (Exception $e) {
    error_log("Save recipe error: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'An unexpected error occurred. Please try again later.'
    ]);
}
?>
