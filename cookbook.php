<?php  
require 'auth_helper.php';
requireLogin(); // Require user to be logged in to add recipes
include 'header.php';
require 'pdo.php';
?>
<head>
    
    <style>
        .cookbook-hero {
            background: linear-gradient(135deg, rgba(167, 199, 231, 0.9), rgba(173, 216, 229, 0.9));
            padding: 100px 0;
            text-align: center;
            margin-bottom: 40px;
        }
        
        .recipe-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 30px;
            height: 100%;
            background: white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .recipe-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        
        .recipe-img {
            height: 200px;
            width: 100%;
            object-fit: cover;
        }
        
        .recipe-category {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--accent-yellow);
            color: var(--dark-color);
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .filter-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .filter-group {
            margin-bottom: 20px;
        }
        
        .filter-option {
            display: block;
            margin-bottom: 8px;
            cursor: pointer;
            color: var(--text-muted);
        }
        
        .save-recipe {
            position: absolute;
            top: 15px;
            left: 15px;
            background: rgba(255, 255, 255, 0.9);
            border: none;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #dc3545;
            opacity: 0;
            transition: all 0.3s ease;
        }
        
        .recipe-card:hover .save-recipe {
            opacity: 1;
        }

        

    </style>
    <!-- Hero Section -->
    <section class="cookbook-hero">
        <div class="container">
            <h1 class="display-4 fw-bold mb-4">Community Cookbook</h1>
            <p class="lead">Your saved recipes shared by our amazing community of food lovers. <br>You can also add your own recipes to the cookbook and share them with the community.</p>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container">
        <div class="row">
            <!-- Filters Sidebar -->
            <div class="col-lg-3">
                <div class="filter-section">
                    <div class="search-box mb-4">
                        <input type="text" class="form-control" id="recipeSearch" placeholder="Search recipes...">
                    </div>

                    <div class="filter-group">
                        <h5>Categories</h5>
                        <?php
                        try {
                            $categoriesStmt = $pdo->query("SELECT category_id, name FROM categories ORDER BY name");
                            $categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($categories as $category) {
                                $categoryId = htmlspecialchars($category['category_id']);
                                $categoryName = htmlspecialchars($category['name']);
                                echo '<label class="filter-option">';
                                echo '<input type="checkbox" name="category_filter" value="' . $categoryId . '" class="filter-category"> ' . $categoryName;
                                echo '</label>';
                            }
                        } catch (PDOException $e) {
                            error_log("Error loading categories: " . $e->getMessage());
                            echo '<p class="text-muted small">Unable to load categories.</p>';
                        }
                        ?>
                    </div>

                    <div class="filter-group">
                        <h5>Dietary</h5>
                        <?php
                        try {
                            $dietaryStmt = $pdo->query("SELECT did, name FROM dietary ORDER BY name");
                            $dietaryOptions = $dietaryStmt->fetchAll(PDO::FETCH_ASSOC);
                            if (empty($dietaryOptions)) {
                                echo '<p class="text-muted small">No dietary options available.</p>';
                            } else {
                                foreach ($dietaryOptions as $dietary) {
                                    $dietaryId = htmlspecialchars($dietary['did']);
                                    $dietaryName = htmlspecialchars($dietary['name']);
                                    echo '<label class="filter-option">';
                                    echo '<input type="checkbox" name="dietary_filter" value="' . $dietaryId . '" class="filter-dietary"> ' . $dietaryName;
                                    echo '</label>';
                                }
                            }
                        } catch (PDOException $e) {
                            error_log("Error loading dietary options: " . $e->getMessage());
                            echo '<p class="text-muted small">Unable to load dietary options.</p>';
                        }
                        ?>
                    </div>

                    <div class="filter-group">
                        <h5>Recipe Type</h5>
                        <label class="filter-option">
                            <input type="checkbox" name="recipe_type_filter" value="created" class="filter-recipe-type"> My Recipes
                        </label>
                        <label class="filter-option">
                            <input type="checkbox" name="recipe_type_filter" value="saved" class="filter-recipe-type"> Saved Recipes
                        </label>
                    </div>

                    <button class="btn btn-outline-secondary w-100" onclick="resetFilters()">
                        <i class="fas fa-redo me-2"></i>Reset Filters
                    </button>
                </div>
            </div>

            <!-- Recipe Grid -->
            <div class="col-lg-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="mb-0">My Cookbook</h3>
                    <div class="d-flex gap-2">
                        <a href="cooking_tips.php" class="btn btn-success">
                            <i class="fas fa-lightbulb me-2"></i>Cooking Tips
                        </a>
                        <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#addRecipeModal">
                            <i class="fas fa-plus me-2"></i>Add New Recipe
                        </button>
                    </div>
                </div>
                <div id="recipesContainer" class="row">
                    <?php
                    $userId = getUserId();
                    try {
                        // Check if saved_recipes table exists
                        $savedRecipesTableExists = false;
                        try {
                            $checkSavedTable = $pdo->query("SHOW TABLES LIKE 'saved_recipes'");
                            $savedRecipesTableExists = $checkSavedTable->rowCount() > 0;
                        } catch (PDOException $e) {
                            // Table doesn't exist
                        }
                        
                        // Fetch recipes created by the user AND recipes saved by the user
                        if ($savedRecipesTableExists) {
                            // Use UNION to combine created and saved recipes, prioritizing created
                            $recipesStmt = $pdo->prepare("
                                SELECT r.recipe_id, r.title, r.description, r.cook_time, r.difficulty, r.image_url, 
                                       r.created_at, r.category_id, r.did,
                                       c.name as category_name, d.name as dietary_name,
                                       'created' as recipe_type,
                                       u.firstname, u.lastname
                                FROM recipes r
                                LEFT JOIN categories c ON r.category_id = c.category_id
                                LEFT JOIN dietary d ON r.did = d.did
                                LEFT JOIN users u ON r.user_id = u.user_id
                                WHERE r.user_id = ?
                                
                                UNION
                                
                                SELECT r.recipe_id, r.title, r.description, r.cook_time, r.difficulty, r.image_url, 
                                       r.created_at, r.category_id, r.did,
                                       c.name as category_name, d.name as dietary_name,
                                       'saved' as recipe_type,
                                       u.firstname, u.lastname
                                FROM recipes r
                                LEFT JOIN categories c ON r.category_id = c.category_id
                                LEFT JOIN dietary d ON r.did = d.did
                                LEFT JOIN users u ON r.user_id = u.user_id
                                INNER JOIN saved_recipes sr ON r.recipe_id = sr.recipe_id
                                WHERE sr.user_id = ? AND r.user_id != ?
                                ORDER BY created_at DESC
                            ");
                            $recipesStmt->execute([$userId, $userId, $userId]);
                        } else {
                            // If saved_recipes table doesn't exist, only show created recipes
                            $recipesStmt = $pdo->prepare("
                                SELECT r.recipe_id, r.title, r.description, r.cook_time, r.difficulty, r.image_url, 
                                       r.created_at, r.category_id, r.did,
                                       c.name as category_name, d.name as dietary_name,
                                       'created' as recipe_type,
                                       u.firstname, u.lastname
                                FROM recipes r
                                LEFT JOIN categories c ON r.category_id = c.category_id
                                LEFT JOIN dietary d ON r.did = d.did
                                LEFT JOIN users u ON r.user_id = u.user_id
                                WHERE r.user_id = ?
                                ORDER BY r.created_at DESC
                            ");
                            $recipesStmt->execute([$userId]);
                        }
                        
                        $recipes = $recipesStmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        if (empty($recipes)) {
                            echo '<div class="col-12"><div class="alert alert-info text-center"><i class="fas fa-info-circle me-2"></i>You haven\'t added or saved any recipes yet. Click "Add New Recipe" to create one, or browse recipes to save your favorites!</div></div>';
                        } else {
                            foreach ($recipes as $recipe) {
                                $recipeId = htmlspecialchars($recipe['recipe_id']);
                                $title = htmlspecialchars($recipe['title']);
                                $description = htmlspecialchars($recipe['description']);
                                $cookTime = htmlspecialchars($recipe['cook_time']);
                                $difficulty = htmlspecialchars($recipe['difficulty']);
                                $categoryName = htmlspecialchars($recipe['category_name'] ?? 'Uncategorized');
                                $dietaryName = htmlspecialchars($recipe['dietary_name'] ?? '');
                                $imageUrl = $recipe['image_url'];
                                $recipeType = $recipe['recipe_type'] ?? 'created';
                                $authorName = htmlspecialchars(($recipe['firstname'] ?? '') . ' ' . ($recipe['lastname'] ?? ''));
                                $isCreated = ($recipeType === 'created');
                                
                                // Determine difficulty badge color
                                $difficultyBadgeClass = 'bg-warning text-dark';
                                if ($difficulty === 'Easy') {
                                    $difficultyBadgeClass = 'bg-success';
                                } elseif ($difficulty === 'Hard') {
                                    $difficultyBadgeClass = 'bg-danger';
                                }
                                
                                // Handle image display
                                $imageAlt = $title;
                                
                                echo '<div class="col-md-6 col-lg-4 recipe-item" ';
                                echo 'data-category="' . htmlspecialchars($recipe['category_id'] ?? '') . '" ';
                                echo 'data-dietary="' . htmlspecialchars($recipe['did'] ?? '') . '" ';
                                echo 'data-recipe-type="' . $recipeType . '">';
                                echo '<div class="recipe-card">';
                                echo '<div class="position-relative">';
                                
                                // Recipe type badge (Created or Saved)
                                if ($isCreated) {
                                    echo '<span class="badge bg-success" style="position: absolute; top: 15px; left: 15px; z-index: 10;">My Recipe</span>';
                                } else {
                                    echo '<span class="badge bg-info" style="position: absolute; top: 15px; left: 15px; z-index: 10;"><i class="fas fa-bookmark me-1"></i>Saved</span>';
                                }
                                
                                // Show image OR placeholder (never both)
                                if (!empty($imageUrl)) {
                                    // Clean and prepare the image URL
                                    $cleanImageUrl = trim($imageUrl);
                                    $cleanImageUrl = str_replace('\\', '/', $cleanImageUrl);
                                    
                                    // Properly encode the URL for web display
                                    $urlParts = explode('/', $cleanImageUrl);
                                    $encodedParts = array_map('rawurlencode', $urlParts);
                                    $encodedUrl = implode('/', $encodedParts);
                                    
                                    // Show ONLY the image - no placeholder div rendered
                                    echo '<img src="' . htmlspecialchars($encodedUrl) . '" class="recipe-img" alt="' . htmlspecialchars($imageAlt) . '" ';
                                    echo 'style="height: 200px; object-fit: cover; width: 100%;" ';
                                    echo 'onerror="this.style.display=\'none\'; ';
                                    echo 'var placeholder = document.createElement(\'div\'); ';
                                    echo 'placeholder.className = \'recipe-img bg-light d-flex align-items-center justify-content-center\'; ';
                                    echo 'placeholder.style.cssText = \'height: 200px;\'; ';
                                    echo 'placeholder.innerHTML = \'<i class=\\\'fas fa-image fa-3x text-muted\\\'></i>\'; ';
                                    echo 'this.parentElement.insertBefore(placeholder, this.nextSibling);">';
                                } else {
                                    // No image URL, show placeholder only
                                    echo '<div class="recipe-img bg-light d-flex align-items-center justify-content-center" style="height: 200px;">';
                                    echo '<i class="fas fa-image fa-3x text-muted"></i>';
                                    echo '</div>';
                                }
                                
                                echo '<span class="recipe-category">' . $categoryName . '</span>';
                                echo '</div>';
                                echo '<div class="p-4">';
                                echo '<h5>' . $title . '</h5>';
                                
                                // Show author name if recipe is saved (not created by current user)
                                if (!$isCreated && !empty($authorName)) {
                                    echo '<p class="text-muted small mb-2"><i class="fas fa-user me-1"></i>' . $authorName . '</p>';
                                }
                                
                                echo '<p class="text-muted">' . mb_substr($description, 0, 100) . (mb_strlen($description) > 100 ? '...' : '') . '</p>';
                                echo '<div class="d-flex justify-content-between align-items-center">';
                                echo '<span class="text-muted"><i class="far fa-clock me-1"></i> ' . $cookTime . ' mins</span>';
                                echo '<span class="badge ' . $difficultyBadgeClass . '">' . $difficulty . '</span>';
                                echo '</div>';
                                if (!empty($dietaryName)) {
                                    echo '<div class="mt-2"><span class="badge bg-info">' . $dietaryName . '</span></div>';
                                }
                                echo '<div class="mt-3">';
                                echo '<a href="recipe_detail.php?id=' . $recipeId . '" class="btn btn-outline-warning w-100">View Recipe</a>';
                                echo '</div>';
                                echo '</div>';
                                echo '</div>';
                                echo '</div>';
                            }
                        }
                    } catch (PDOException $e) {
                        error_log("Error loading recipes: " . $e->getMessage());
                        echo '<div class="col-12"><div class="alert alert-danger">Error loading recipes. Please try again later.</div></div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Recipe Modal -->
    <div class="modal fade" id="addRecipeModal" tabindex="-1" aria-labelledby="addRecipeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title" id="addRecipeModalLabel">Add New Recipe</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="recipeForm" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div id="recipeAlert" class="alert d-none" role="alert"></div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="recipeTitle" class="form-label">Recipe Title *</label>
                                    <input type="text" class="form-control" id="recipeTitle" name="title" required>
                                </div>
                                <div class="mb-3">
                                    <label for="recipeCategory" class="form-label">Category *</label>
                                    <select class="form-select" id="recipeCategory" name="category_id" required>
                                        <option value="">Select a category</option>
                                        <?php
                                        try {
                                            $categoriesStmt = $pdo->query("SELECT category_id, name FROM categories ORDER BY name");
                                            $categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);
                                            foreach ($categories as $category) {
                                                echo '<option value="' . htmlspecialchars($category['category_id']) . '">' . htmlspecialchars($category['name']) . '</option>';
                                            }
                                        } catch (PDOException $e) {
                                            error_log("Error loading categories: " . $e->getMessage());
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Dietary Preferences</label>
                                    <?php
                                    try {
                                        $dietaryStmt = $pdo->query("SELECT did, name FROM dietary ORDER BY name");
                                        $dietaryOptions = $dietaryStmt->fetchAll(PDO::FETCH_ASSOC);
                                        if (empty($dietaryOptions)) {
                                            echo '<p class="text-muted small">No dietary options available.</p>';
                                        } else {
                                            foreach ($dietaryOptions as $dietary) {
                                                $dietaryId = htmlspecialchars($dietary['did']);
                                                $dietaryName = htmlspecialchars($dietary['name']);
                                                $dietaryIdAttr = 'dietary_' . $dietaryId;
                                                echo '<div class="form-check">';
                                                echo '<input class="form-check-input" type="checkbox" name="dietary[]" value="' . $dietaryId . '" id="' . $dietaryIdAttr . '">';
                                                echo '<label class="form-check-label" for="' . $dietaryIdAttr . '">' . $dietaryName . '</label>';
                                                echo '</div>';
                                            }
                                        }
                                    } catch (PDOException $e) {
                                        error_log("Error loading dietary options: " . $e->getMessage());
                                        echo '<p class="text-muted small">Unable to load dietary options.</p>';
                                    }
                                    ?>
                                </div>
                                <div class="mb-3">
                                    <label for="cookTime" class="form-label">Cooking Time (minutes) *</label>
                                    <input type="number" class="form-control" id="cookTime" name="cook_time" min="1" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="difficulty" class="form-label">Difficulty *</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="difficulty" id="easy" value="Easy" required>
                                        <label class="form-check-label" for="easy">Easy</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="difficulty" id="medium" value="Medium">
                                        <label class="form-check-label" for="medium">Medium</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="difficulty" id="hard" value="Hard">
                                        <label class="form-check-label" for="hard">Hard</label>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="recipeImage" class="form-label">Recipe Image *</label>
                                    <input type="file" class="form-control" id="recipeImage" name="recipe_image" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" required>
                                    <div class="form-text">Upload a high-quality image (JPG, PNG, GIF, WEBP, max 5MB). Image is required.</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Recipe Description *</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                        </div>
                        
                        <div class="ingredients-section mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label">Ingredients *</label>
                                <button type="button" class="btn btn-sm btn-outline-warning" id="addIngredient">
                                    <i class="fas fa-plus"></i> Add Ingredient
                                </button>
                            </div>
                            <div id="ingredientsList">
                                <div class="ingredient-row row g-2 mb-2">
                                    <div class="col-4">
                                        <input type="text" class="form-control" name="ingredients[0][quantity]" placeholder="Qty (e.g., 2 cups)" required>
                                    </div>
                                    <div class="col-6">
                                        <input type="text" class="form-control" name="ingredients[0][name]" placeholder="Ingredient name" required>
                                    </div>
                                    <div class="col-2">
                                        <button type="button" class="btn btn-outline-danger w-100 remove-ingredient">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="instructions-section">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label">Instructions *</label>
                                <button type="button" class="btn btn-sm btn-outline-warning" id="addStep">
                                    <i class="fas fa-plus"></i> Add Step
                                </button>
                            </div>
                            <div id="instructionsList">
                                <div class="input-group mb-2">
                                    <span class="input-group-text">1</span>
                                    <textarea class="form-control" name="instructions[]" rows="2" placeholder="Describe this step..." required></textarea>
                                    <button type="button" class="btn btn-outline-danger remove-step">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Save Recipe</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

   <?php include 'footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form handling for adding ingredients and instructions
        document.addEventListener('DOMContentLoaded', function() {
            // Add ingredient
            document.getElementById('addIngredient').addEventListener('click', function() {
                const ingredientsList = document.getElementById('ingredientsList');
                const ingredientCount = ingredientsList.children.length;
                
                const newIngredient = `
                    <div class="ingredient-row row g-2 mb-2">
                        <div class="col-4">
                            <input type="text" class="form-control" name="ingredients[${ingredientCount}][quantity]" placeholder="Qty (e.g., 2 cups)" required>
                        </div>
                        <div class="col-6">
                            <input type="text" class="form-control" name="ingredients[${ingredientCount}][name]" placeholder="Ingredient name" required>
                        </div>
                        <div class="col-2">
                            <button type="button" class="btn btn-outline-danger w-100 remove-ingredient">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>`;
                
                ingredientsList.insertAdjacentHTML('beforeend', newIngredient);
            });

            // Remove ingredient
            document.addEventListener('click', function(e) {
                if (e.target.closest('.remove-ingredient')) {
                    e.target.closest('.ingredient-row').remove();
                }
            });
            

            // Add instruction step
            document.getElementById('addStep').addEventListener('click', function() {
                const instructionsList = document.getElementById('instructionsList');
                const stepNumber = instructionsList.children.length + 1;
                const newStep = `
                    <div class="input-group mb-2">
                        <span class="input-group-text">${stepNumber}</span>
                        <textarea class="form-control" name="instructions[]" rows="2" placeholder="Describe this step..." required></textarea>
                        <button type="button" class="btn btn-outline-danger remove-step">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>`;
                instructionsList.insertAdjacentHTML('beforeend', newStep);
            });

            // Remove ingredient or instruction
            document.addEventListener('click', function(e) {
                if (e.target.closest('.remove-ingredient')) {
                    e.target.closest('.ingredient-row').remove();
                } else if (e.target.closest('.remove-step')) {
                    e.target.closest('.input-group').remove();
                    // Renumber remaining steps
                    const steps = document.querySelectorAll('#instructionsList .input-group-text');
                    steps.forEach((step, index) => {
                        step.textContent = index + 1;
                    });
                }
            });

            // Form submission with AJAX
            const form = document.getElementById('recipeForm');
            const recipeAlert = document.getElementById('recipeAlert');
            const submitBtn = form.querySelector('button[type="submit"]');
            
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    // Hide previous alerts
                    recipeAlert.classList.add('d-none');
                    
                    // Validate image file
                    const imageInput = document.getElementById('recipeImage');
                    if (!imageInput || !imageInput.files || imageInput.files.length === 0) {
                        recipeAlert.classList.remove('d-none');
                        recipeAlert.className = 'alert alert-danger';
                        recipeAlert.textContent = 'Recipe image is required. Please upload an image for your recipe.';
                        return false;
                    }
                    
                    // Validate image file size
                    const imageFile = imageInput.files[0];
                    const maxSize = 5 * 1024 * 1024; // 5MB
                    if (imageFile.size > maxSize) {
                        recipeAlert.classList.remove('d-none');
                        recipeAlert.className = 'alert alert-danger';
                        recipeAlert.textContent = 'Image file size exceeds 5MB limit. Please upload a smaller image.';
                        return false;
                    }
                    
                    // Validate file type
                    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                    if (!allowedTypes.includes(imageFile.type)) {
                        recipeAlert.classList.remove('d-none');
                        recipeAlert.className = 'alert alert-danger';
                        recipeAlert.textContent = 'Invalid file type. Allowed formats: JPG, PNG, GIF, WEBP.';
                        return false;
                    }
                    
                    // Validate ingredients and instructions
                    const ingredientInputs = document.querySelectorAll('input[name^="ingredients["][name$="[name]"]');
                    const instructionInputs = document.querySelectorAll('textarea[name="instructions[]"]');
                    
                    let hasValidIngredients = false;
                    let hasValidInstructions = false;
                    
                    ingredientInputs.forEach(input => {
                        if (input.value.trim() !== '') {
                            hasValidIngredients = true;
                        }
                    });
                    
                    instructionInputs.forEach(textarea => {
                        if (textarea.value.trim() !== '') {
                            hasValidInstructions = true;
                        }
                    });
                    
                    if (!hasValidIngredients || !hasValidInstructions) {
                        recipeAlert.classList.remove('d-none');
                        recipeAlert.className = 'alert alert-danger';
                        recipeAlert.textContent = 'Please add at least one ingredient and one instruction step.';
                        return false;
                    }
                    
                    // Disable submit button
                    submitBtn.disabled = true;
                    const originalBtnText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
                    
                    // Create FormData
                    const formData = new FormData(form);
                    
                    // Submit via AJAX
                    fetch('process_recipe.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                        
                        recipeAlert.classList.remove('d-none');
                        
                        if (data.success) {
                            // Show success message, but use warning style if there's an image warning
                            if (data.image_warning) {
                                recipeAlert.className = 'alert alert-warning';
                            } else {
                                recipeAlert.className = 'alert alert-success';
                            }
                            recipeAlert.textContent = data.message;
                            
                            // Reset form
                            form.reset();
                            
                            // Clear dynamic fields
                            const ingredientsList = document.getElementById('ingredientsList');
                            const instructionsList = document.getElementById('instructionsList');
                            ingredientsList.innerHTML = `
                                <div class="ingredient-row row g-2 mb-2">
                                    <div class="col-4">
                                        <input type="text" class="form-control" name="ingredients[0][quantity]" placeholder="Qty (e.g., 2 cups)" required>
                                    </div>
                                    <div class="col-6">
                                        <input type="text" class="form-control" name="ingredients[0][name]" placeholder="Ingredient name" required>
                                    </div>
                                    <div class="col-2">
                                        <button type="button" class="btn btn-outline-danger w-100 remove-ingredient">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            `;
                            instructionsList.innerHTML = `
                                <div class="input-group mb-2">
                                    <span class="input-group-text">1</span>
                                    <textarea class="form-control" name="instructions[]" rows="2" placeholder="Describe this step..." required></textarea>
                                    <button type="button" class="btn btn-outline-danger remove-step">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            `;
                            
                            // Close modal after 1.5 seconds and reload page
                            setTimeout(() => {
                                const modal = bootstrap.Modal.getInstance(document.getElementById('addRecipeModal'));
                                modal.hide();
                                window.location.reload();
                            }, 1500);
                        } else {
                            recipeAlert.className = 'alert alert-danger';
                            recipeAlert.textContent = data.message || 'An error occurred. Please try again.';
                        }
                    })
                    .catch(error => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                        
                        recipeAlert.classList.remove('d-none');
                        recipeAlert.className = 'alert alert-danger';
                        recipeAlert.textContent = 'An error occurred. Please try again later.';
                        console.error('Recipe submission error:', error);
                    });
                    
                    return false;
                });
            }
        });

        
        // Filter recipes based on selected categories, dietary options, and recipe type
        function filterRecipes() {
            const searchTerm = document.getElementById('recipeSearch').value.toLowerCase();
            const selectedCategories = Array.from(document.querySelectorAll('.filter-category:checked')).map(cb => cb.value);
            const selectedDietary = Array.from(document.querySelectorAll('.filter-dietary:checked')).map(cb => cb.value);
            const selectedRecipeTypes = Array.from(document.querySelectorAll('.filter-recipe-type:checked')).map(cb => cb.value);
            const recipeItems = document.querySelectorAll('.recipe-item');
            
            recipeItems.forEach(item => {
                const title = item.querySelector('h5').textContent.toLowerCase();
                const description = item.querySelector('.text-muted').textContent.toLowerCase();
                const categoryId = item.getAttribute('data-category');
                const dietaryId = item.getAttribute('data-dietary');
                const recipeType = item.getAttribute('data-recipe-type');
                
                // Check search term
                const matchesSearch = searchTerm === '' || title.includes(searchTerm) || description.includes(searchTerm);
                
                // Check category filter
                const matchesCategory = selectedCategories.length === 0 || selectedCategories.includes(categoryId);
                
                // Check dietary filter
                const matchesDietary = selectedDietary.length === 0 || (dietaryId && selectedDietary.includes(dietaryId));
                
                // Check recipe type filter
                const matchesRecipeType = selectedRecipeTypes.length === 0 || selectedRecipeTypes.includes(recipeType);
                
                // Show/hide recipe card
                if (matchesSearch && matchesCategory && matchesDietary && matchesRecipeType) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
            
            // Show message if no recipes match
            const visibleRecipes = Array.from(recipeItems).filter(item => item.style.display !== 'none');
            const container = document.getElementById('recipesContainer');
            let noResultsMsg = container.querySelector('.no-results-message');
            
            if (visibleRecipes.length === 0 && recipeItems.length > 0) {
                if (!noResultsMsg) {
                    noResultsMsg = document.createElement('div');
                    noResultsMsg.className = 'col-12 no-results-message';
                    noResultsMsg.innerHTML = '<div class="alert alert-warning text-center"><i class="fas fa-search me-2"></i>No recipes match your filters. Try adjusting your search criteria.</div>';
                    container.appendChild(noResultsMsg);
                }
            } else if (noResultsMsg) {
                noResultsMsg.remove();
            }
        }
        
        // Reset filters
        function resetFilters() {
            document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                checkbox.checked = false;
            });
            document.getElementById('recipeSearch').value = '';
            filterRecipes();
        }
        
        // Add event listeners for filtering
        document.addEventListener('DOMContentLoaded', function() {
            // Search input
            const searchInput = document.getElementById('recipeSearch');
            if (searchInput) {
                searchInput.addEventListener('input', filterRecipes);
            }
            
            // Category checkboxes
            document.querySelectorAll('.filter-category').forEach(checkbox => {
                checkbox.addEventListener('change', filterRecipes);
            });
            
            // Dietary checkboxes
            document.querySelectorAll('.filter-dietary').forEach(checkbox => {
                checkbox.addEventListener('change', filterRecipes);
            });
            
            // Recipe type checkboxes
            document.querySelectorAll('.filter-recipe-type').forEach(checkbox => {
                checkbox.addEventListener('change', filterRecipes);
            });
        });
    </script>
</body>
</html>
