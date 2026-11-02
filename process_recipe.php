<?php
session_start();
require 'pdo.php';
require 'auth_helper.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'You must be logged in to add a recipe.']);
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
    
    // Validate required fields
    $title = trim($_POST['title'] ?? '');
    $categoryId = intval($_POST['category_id'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $cookTime = intval($_POST['cook_time'] ?? 0);
    $difficulty = trim($_POST['difficulty'] ?? '');
    $dietary = isset($_POST['dietary']) && is_array($_POST['dietary']) && !empty($_POST['dietary']) 
        ? intval($_POST['dietary'][0]) : null; // Take first dietary option if multiple selected
    $ingredients = $_POST['ingredients'] ?? [];
    $instructions = $_POST['instructions'] ?? [];
    
    // Validation
    if (empty($title) || strlen($title) > 100) {
        echo json_encode(['success' => false, 'message' => 'Recipe title is required and must be 100 characters or less.']);
        exit;
    }
    
    if ($categoryId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Please select a valid category.']);
        exit;
    }
    
    if (empty($description)) {
        echo json_encode(['success' => false, 'message' => 'Recipe description is required.']);
        exit;
    }
    
    if ($cookTime <= 0) {
        echo json_encode(['success' => false, 'message' => 'Cooking time must be greater than 0.']);
        exit;
    }
    
    if (!in_array($difficulty, ['Easy', 'Medium', 'Hard'])) {
        echo json_encode(['success' => false, 'message' => 'Please select a valid difficulty level.']);
        exit;
    }
    
    if (empty($ingredients) || !is_array($ingredients)) {
        echo json_encode(['success' => false, 'message' => 'Please add at least one ingredient.']);
        exit;
    }
    
    if (empty($instructions) || !is_array($instructions)) {
        echo json_encode(['success' => false, 'message' => 'Please add at least one instruction step.']);
        exit;
    }
    
    // Validate category exists
    $checkCategory = $pdo->prepare("SELECT category_id FROM categories WHERE category_id = ?");
    $checkCategory->execute([$categoryId]);
    if ($checkCategory->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Selected category does not exist.']);
        exit;
    }
    
    // Validate dietary if provided
    if ($dietary !== null) {
        $checkDietary = $pdo->prepare("SELECT did FROM dietary WHERE did = ?");
        $checkDietary->execute([$dietary]);
        if ($checkDietary->rowCount() === 0) {
            $dietary = null; // Set to null if invalid dietary option
        }
    }
    
    // Handle image upload (REQUIRED - recipe cannot be saved without image)
    $imageUrl = null;
    $imageUploadError = null;
    
    // Check if image file is provided
    if (!isset($_FILES['recipe_image']) || $_FILES['recipe_image']['error'] === UPLOAD_ERR_NO_FILE) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Recipe image is required. Please upload an image for your recipe.']);
        exit;
    }
    
    if (isset($_FILES['recipe_image']) && $_FILES['recipe_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $imageFile = $_FILES['recipe_image'];
        
        // Validate file
        if ($imageFile['error'] === UPLOAD_ERR_OK) {
            // Check file size (5MB max)
            $maxSize = 5 * 1024 * 1024;
            if ($imageFile['size'] > $maxSize) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Image file size exceeds 5MB limit. Please upload a smaller image.']);
                exit;
            } else {
                // Validate file type
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $fileExtension = strtolower(pathinfo($imageFile['name'], PATHINFO_EXTENSION));
                
                if (!in_array($fileExtension, $allowedExtensions)) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Invalid file type. Allowed formats: JPG, PNG, GIF, WEBP.']);
                    exit;
                } else {
                    // Create upload directory if it doesn't exist
                    $uploadDir = __DIR__ . '/uploads/recipe_images/';
                    
                    // Ensure parent directory exists
                    $parentDir = dirname($uploadDir);
                    if (!file_exists($parentDir)) {
                        @mkdir($parentDir, 0777, true);
                        @chmod($parentDir, 0777);
                    }
                    
                    if (!file_exists($uploadDir)) {
                        if (!@mkdir($uploadDir, 0777, true)) {
                            error_log("Failed to create upload directory: " . $uploadDir);
                            header('Content-Type: application/json');
                            echo json_encode(['success' => false, 'message' => 'Failed to create upload directory. Please contact administrator.']);
                            exit;
                        }
                    }
                    
                    // Set directory permissions to be writable
                    @chmod($uploadDir, 0777);
                    @chmod($parentDir, 0777);
                    
                    // Check if directory is writable
                    if (!is_writable($uploadDir)) {
                        // Try to make it writable with different permission levels
                        $permissions = [0777, 0775, 0755];
                        $isWritable = false;
                        
                        foreach ($permissions as $perm) {
                            @chmod($uploadDir, $perm);
                            @chmod($parentDir, $perm);
                            if (is_writable($uploadDir)) {
                                $isWritable = true;
                                error_log("Directory made writable with permissions: " . decoct($perm));
                                break;
                            }
                        }
                        
                        // Final check - try to create a test file
                        if (!$isWritable) {
                            $testFile = $uploadDir . '.test_write_' . time() . '.tmp';
                            $testResult = @file_put_contents($testFile, 'test');
                            if ($testResult !== false) {
                                @unlink($testFile);
                                $isWritable = true;
                                error_log("Directory is writable (test file creation succeeded)");
                            } else {
                                error_log("Test file creation failed. Last error: " . error_get_last()['message']);
                            }
                        }
                        
                        if (!$isWritable) {
                            error_log("Upload directory is not writable: " . $uploadDir);
                            error_log("Current permissions: " . substr(sprintf('%o', fileperms($uploadDir)), -4));
                            error_log("PHP user: " . get_current_user());
                            error_log("PHP process user: " . (function_exists('posix_getpwuid') && function_exists('posix_geteuid') ? posix_getpwuid(posix_geteuid())['name'] : 'unknown'));
                            
                            header('Content-Type: application/json');
                            echo json_encode([
                                'success' => false, 
                                'message' => 'Upload directory is not writable. Please run: chmod -R 777 uploads/recipe_images/ in your project directory or contact administrator.'
                            ]);
                            exit;
                        }
                    }
                    
                    // Proceed with upload
                    {
                        // Generate unique filename
                        $filename = 'recipe_' . $userId . '_' . time() . '_' . uniqid() . '.' . $fileExtension;
                        $filepath = $uploadDir . $filename;
                        
                        // Move uploaded file
                        if (move_uploaded_file($imageFile['tmp_name'], $filepath)) {
                            // Set image URL - move_uploaded_file success means file was saved
                            $imageUrl = 'uploads/recipe_images/' . $filename;
                            error_log("=== IMAGE UPLOAD SUCCESS ===");
                            error_log("Image URL set to: " . $imageUrl);
                            error_log("File path: " . $filepath);
                            error_log("imageUrl variable type: " . gettype($imageUrl));
                            error_log("imageUrl length: " . strlen($imageUrl));
                            
                            // Double-check file exists (for debugging)
                            if (!file_exists($filepath)) {
                                error_log("WARNING: move_uploaded_file succeeded but file_exists() returns false for: " . $filepath);
                            } else {
                                error_log("File confirmed to exist at: " . $filepath);
                            }
                            error_log("===========================");
                        } else {
                            $lastError = error_get_last();
                            $errorMsg = 'Failed to upload image.';
                            if ($lastError && isset($lastError['message'])) {
                                error_log("move_uploaded_file failed: " . $lastError['message']);
                                error_log("Source: " . $imageFile['tmp_name']);
                                error_log("Destination: " . $filepath);
                                $errorMsg .= ' Error: ' . $lastError['message'];
                            }
                            header('Content-Type: application/json');
                            echo json_encode(['success' => false, 'message' => $errorMsg . ' Recipe cannot be saved without an image.']);
                            exit;
                        }
                    }
                }
            }
        } else {
            // Handle upload errors - prevent recipe from being saved if image fails
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive.',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive.',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.'
            ];
            $errorMsg = $errorMessages[$imageFile['error']] ?? 'Unknown upload error.';
            error_log("Image upload error code: " . $imageFile['error']);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Image upload failed: ' . $errorMsg . ' Recipe cannot be saved without an image.']);
            exit;
        }
    }
    
    // Final check: Ensure image was uploaded successfully
    if (empty($imageUrl) || $imageUrl === null) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Recipe image is required. Please upload a valid image file (JPG, PNG, GIF, WEBP, max 5MB).']);
        exit;
    }
    
    // Debug: Log image upload status
    error_log("=== Recipe Image Upload Debug ===");
    error_log("Image file provided: " . (isset($_FILES['recipe_image']) ? 'YES' : 'NO'));
    if (isset($_FILES['recipe_image'])) {
        error_log("Upload error code: " . $_FILES['recipe_image']['error']);
        error_log("File name: " . ($_FILES['recipe_image']['name'] ?? 'N/A'));
        error_log("File size: " . ($_FILES['recipe_image']['size'] ?? 'N/A'));
        error_log("Tmp name: " . ($_FILES['recipe_image']['tmp_name'] ?? 'N/A'));
    }
    error_log("Final imageUrl value BEFORE DB: " . var_export($imageUrl, true));
    error_log("imageUrl is null: " . ($imageUrl === null ? 'YES' : 'NO'));
    error_log("imageUrl is empty: " . (empty($imageUrl) ? 'YES' : 'NO'));
    error_log("imageUrl type: " . gettype($imageUrl));
    if ($imageUrl !== null) {
        error_log("imageUrl length: " . strlen($imageUrl));
    }
    error_log("Image upload error: " . ($imageUploadError ?? 'NONE'));
    error_log("================================");
    
    // Start transaction
    $pdo->beginTransaction();
    
    try {
        // Log image URL before insertion for debugging
        error_log("Inserting recipe with image_url: " . ($imageUrl ?? 'NULL'));
        
        // Insert recipe
        // Image is required, so imageUrl should never be null at this point
        // But add a safety check just in case
        if (empty($imageUrl) || $imageUrl === null) {
            $pdo->rollBack();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Image is required. Recipe cannot be saved without an image.']);
            exit;
        }
        
        $imageUrlForDb = $imageUrl;
        
        error_log("About to insert recipe with imageUrlForDb: " . var_export($imageUrlForDb, true));
        
        $insertRecipe = $pdo->prepare("
            INSERT INTO recipes (user_id, did, category_id, title, description, cook_time, difficulty, image_url) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        try {
            $result = $insertRecipe->execute([
                $userId,
                $dietary,
                $categoryId,
                $title,
                $description,
                $cookTime,
                $difficulty,
                $imageUrlForDb
            ]);
            
            if (!$result) {
                $errorInfo = $insertRecipe->errorInfo();
                error_log("INSERT failed: " . print_r($errorInfo, true));
                throw new PDOException("Insert failed: " . print_r($errorInfo, true));
            }
        } catch (PDOException $e) {
            error_log("PDO Exception during insert: " . $e->getMessage());
            throw $e;
        }
        
        $recipeId = $pdo->lastInsertId();
        
        // Verify the image_url was saved
        $verifyStmt = $pdo->prepare("SELECT image_url FROM recipes WHERE recipe_id = ?");
        $verifyStmt->execute([$recipeId]);
        $savedRecipe = $verifyStmt->fetch(PDO::FETCH_ASSOC);
        $savedImageUrl = $savedRecipe['image_url'] ?? null;
        
        error_log("=== DATABASE VERIFICATION ===");
        error_log("Recipe ID: $recipeId");
        error_log("Expected image_url: " . var_export($imageUrlForDb, true));
        error_log("Saved image_url: " . var_export($savedImageUrl, true));
        error_log("Match: " . ($imageUrlForDb === $savedImageUrl ? 'YES' : 'NO'));
        
        // If image_url wasn't saved but should have been, try to update it
        if ($imageUrlForDb !== null && $savedImageUrl === null) {
            error_log("WARNING: image_url was not saved! Attempting UPDATE...");
            $updateStmt = $pdo->prepare("UPDATE recipes SET image_url = ? WHERE recipe_id = ?");
            $updateResult = $updateStmt->execute([$imageUrlForDb, $recipeId]);
            error_log("UPDATE result: " . ($updateResult ? 'SUCCESS' : 'FAILED'));
            if (!$updateResult) {
                $updateError = $updateStmt->errorInfo();
                error_log("UPDATE error: " . print_r($updateError, true));
            } else {
                // Verify again
                $verifyStmt2 = $pdo->prepare("SELECT image_url FROM recipes WHERE recipe_id = ?");
                $verifyStmt2->execute([$recipeId]);
                $savedRecipe2 = $verifyStmt2->fetch(PDO::FETCH_ASSOC);
                error_log("After UPDATE, image_url: " . var_export($savedRecipe2['image_url'] ?? null, true));
            }
        }
        error_log("============================");
        
        // Process ingredients
        foreach ($ingredients as $ingredient) {
            $ingredientName = trim($ingredient['name'] ?? '');
            $quantity = trim($ingredient['quantity'] ?? '');
            
            if (empty($ingredientName)) {
                continue; // Skip empty ingredients
            }
            
            // Check if ingredient exists, if not create it
            $checkIngredient = $pdo->prepare("SELECT ingredient_id FROM ingredients WHERE name = ?");
            $checkIngredient->execute([$ingredientName]);
            
            if ($checkIngredient->rowCount() > 0) {
                $ingredientId = $checkIngredient->fetch(PDO::FETCH_ASSOC)['ingredient_id'];
            } else {
                // Create new ingredient
                $insertIngredient = $pdo->prepare("INSERT INTO ingredients (name) VALUES (?)");
                $insertIngredient->execute([$ingredientName]);
                $ingredientId = $pdo->lastInsertId();
            }
            
            // Link ingredient to recipe
            $linkIngredient = $pdo->prepare("
                INSERT INTO recipe_ingredients (recipe_id, ingredient_id, quantity) 
                VALUES (?, ?, ?)
            ");
            $linkIngredient->execute([$recipeId, $ingredientId, $quantity]);
        }
        
        // Process instructions
        foreach ($instructions as $index => $instruction) {
            $instructionText = trim($instruction);
            
            if (empty($instructionText)) {
                continue; // Skip empty instructions
            }
            
            $insertInstruction = $pdo->prepare("
                INSERT INTO instructions (recipe_id, instruction) 
                VALUES (?, ?)
            ");
            $insertInstruction->execute([$recipeId, $instructionText]);
        }
        
        // Commit transaction
        $pdo->commit();
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true, 
            'message' => 'Recipe added successfully with image!',
            'recipe_id' => $recipeId,
            'image_url' => $imageUrl
        ]);
        
    } catch (PDOException $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        
        // Delete uploaded image if recipe insertion failed
        if ($imageUrl && file_exists(__DIR__ . '/' . $imageUrl)) {
            unlink(__DIR__ . '/' . $imageUrl);
        }
        
        error_log("Recipe insertion error: " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false, 
            'message' => 'An error occurred while adding the recipe. Please try again.'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Recipe processing error: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false, 
        'message' => 'An unexpected error occurred. Please try again later.'
    ]);
}
?>
