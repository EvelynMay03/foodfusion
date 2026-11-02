   <?php 
    require 'auth_helper.php';
    include 'header.php';
    require 'pdo.php';
    
    // Get current user ID if logged in
    $currentUserId = isLoggedIn() ? getUserId() : null;
    
    // Get category filter from URL
    $selectedCategoryId = isset($_GET['category']) ? intval($_GET['category']) : null;
    $selectedCategoryName = null;
    
    // Get search query from URL
    $searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
    
    // If category is selected, get category name
    if ($selectedCategoryId) {
        try {
            $categoryStmt = $pdo->prepare("SELECT name FROM categories WHERE category_id = ?");
            $categoryStmt->execute([$selectedCategoryId]);
            $categoryData = $categoryStmt->fetch(PDO::FETCH_ASSOC);
            if ($categoryData) {
                $selectedCategoryName = htmlspecialchars($categoryData['name']);
            } else {
                // Invalid category ID, reset to null
                $selectedCategoryId = null;
            }
        } catch (PDOException $e) {
            error_log("Error fetching category: " . $e->getMessage());
            $selectedCategoryId = null;
        }
    }
    
    // Pagination settings
    $recipesPerPage = 9; // 3 columns x 3 rows
    $currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $offset = ($currentPage - 1) * $recipesPerPage;
    
    // Get saved recipes for current user (if logged in)
    $savedRecipeIds = [];
    if ($currentUserId) {
        try {
            // Check if saved_recipes table exists, if not create it
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
            
            // Get saved recipe IDs for current user
            $savedStmt = $pdo->prepare("SELECT recipe_id FROM saved_recipes WHERE user_id = ?");
            $savedStmt->execute([$currentUserId]);
            $savedRecipes = $savedStmt->fetchAll(PDO::FETCH_COLUMN);
            $savedRecipeIds = $savedRecipes;
        } catch (PDOException $e) {
            error_log("Error checking/creating saved_recipes table: " . $e->getMessage());
        }
    }
    
    // Get total count of recipes (filtered by category if selected)
    try {
        if ($selectedCategoryId) {
            $countStmt = $pdo->prepare("SELECT COUNT(DISTINCT r.recipe_id) as total FROM recipes r WHERE r.category_id = ?");
            $countStmt->execute([$selectedCategoryId]);
        } else {
            $countStmt = $pdo->query("SELECT COUNT(DISTINCT r.recipe_id) as total FROM recipes r");
        }
        $totalRecipes = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
        $totalPages = ceil($totalRecipes / $recipesPerPage);
    } catch (PDOException $e) {
        error_log("Error counting recipes: " . $e->getMessage());
        $totalRecipes = 0;
        $totalPages = 1;
    }
    ?>
<head>
    <style>
        .recipes-hero {
            background: linear-gradient(135deg, rgba(167, 199, 231, 0.9), rgba(173, 216, 229, 0.9)), 
                        url('https://images.unsplash.com/photo-1504674900247-087703934569?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            padding: 100px 0;
            color: #0A1128;
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

        .recipe-time {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .recipe-rating {
            color: #FFD700;
            margin-bottom: 10px;
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

        .filter-group h5 {
            font-size: 1rem;
            margin-bottom: 15px;
            color: var(--dark-color);
            font-weight: 600;
        }

        .filter-option {
            display: block;
            margin-bottom: 8px;
            cursor: pointer;
            color: var(--text-muted);
            transition: color 0.3s ease;
        }

        .filter-option:hover {
            color: var(--accent-yellow);
        }

        .filter-option input[type="checkbox"] {
            margin-right: 8px;
        }

        .pagination .page-link {
            color: var(--dark-color);
            border: 1px solid #dee2e6;
            margin: 0 5px;
            border-radius: 8px !important;
        }

        .pagination .page-item.active .page-link {
            background-color: var(--accent-yellow);
            border-color: var(--accent-yellow);
            color: var(--dark-color);
        }

        .search-box {
            position: relative;
            margin-bottom: 30px;
        }

        .search-box input {
            padding: 12px 20px;
            border-radius: 30px;
            border: 2px solid #e9ecef;
            width: 100%;
            padding-right: 50px;
        }

        .search-box button {
            position: absolute;
            right: 5px;
            top: 5px;
            background: var(--accent-yellow);
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            color: var(--dark-color);
        }

        .difficulty-easy {
            color: #28a745;
        }

        .difficulty-medium {
            color: #ffc107;
        }

        .difficulty-hard {
            color: #dc3545;
        }
        
        .save-recipe-btn {
            position: absolute;
            top: 15px;
            left: 15px;
            background: rgba(255, 255, 255, 0.9);
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #dc3545;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 10;
        }
        
        .save-recipe-btn:hover {
            background: rgba(255, 255, 255, 1);
            transform: scale(1.1);
        }
        
        .save-recipe-btn.saved {
            color: #dc3545;
            background: rgba(220, 53, 69, 0.1);
        }
        
        .recipe-card:hover .save-recipe-btn {
            opacity: 1;
        }
    </style>
    <!-- Hero Section -->
    <section class="recipes-hero">
        <div class="container">
            <h1 class="display-4 fw-bold mb-4">
                <?php if ($selectedCategoryName): ?>
                    <?php echo $selectedCategoryName; ?> Recipes
                <?php else: ?>
                    Delicious Recipes
                <?php endif; ?>
            </h1>
            <p class="lead mb-0">
                <?php if ($selectedCategoryName): ?>
                    Discover amazing <?php echo strtolower($selectedCategoryName); ?> recipes from our community
                <?php else: ?>
                    Discover amazing recipes from around the world added by our community.
                <?php endif; ?>
            </p>
            <?php if ($selectedCategoryName): ?>
                <a href="recipes.php" class="btn btn-light mt-3">
                    <i class="fas fa-times me-2"></i>Clear Category Filter
                </a>
            <?php endif; ?>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container">
        <div class="row">
            <!-- Filters Sidebar -->
            <div class="col-lg-3">
                <div class="filter-section">
                    <div class="search-box">
                        <input type="text" id="recipeSearch" placeholder="Search recipes..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                        <button type="button"><i class="fas fa-search"></i></button>
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
                                $checked = ($selectedCategoryId && $selectedCategoryId == $categoryId) ? 'checked' : '';
                                echo '<input type="checkbox" name="category_filter" value="' . $categoryId . '" class="filter-category" ' . $checked . '> ' . $categoryName;
                                echo '</label>';
                            }
                        } catch (PDOException $e) {
                            error_log("Error loading categories: " . $e->getMessage());
                        }
                        ?>
                    </div>

                    <div class="filter-group">
                        <h5>Dietary</h5>
                        <?php
                        try {
                            $dietaryStmt = $pdo->query("SELECT did, name FROM dietary ORDER BY name");
                            $dietaryOptions = $dietaryStmt->fetchAll(PDO::FETCH_ASSOC);
                            if (!empty($dietaryOptions)) {
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
                        }
                        ?>
                    </div>

                    <div class="filter-group">
                        <h5>Difficulty</h5>
                        <label class="filter-option">
                            <input type="checkbox" name="difficulty" value="easy" class="filter-difficulty"> <span class="difficulty-easy">Easy</span>
                        </label>
                        <label class="filter-option">
                            <input type="checkbox" name="difficulty" value="medium" class="filter-difficulty"> <span class="difficulty-medium">Medium</span>
                        </label>
                        <label class="filter-option">
                            <input type="checkbox" name="difficulty" value="hard" class="filter-difficulty"> <span class="difficulty-hard">Hard</span>
                        </label>
                    </div>

                    <button class="btn btn-outline-secondary w-100" onclick="resetFilters()">
                        <i class="fas fa-redo me-2"></i>Reset Filters
                    </button>
                </div>
            </div>

            <!-- Recipe Grid -->
            <div class="col-lg-9">
                <div id="recipesContainer" class="row">
                    <?php
                    try {
                        // Fetch recipes for current page from database
                        $limit = intval($recipesPerPage);
                        $offsetValue = intval($offset);
                        
                        // Simple query without ratings first to avoid complexity
                        // Add category filter if category is selected
                        if ($selectedCategoryId) {
                            $sql = "
                                SELECT r.recipe_id, r.title, r.description, r.cook_time, r.difficulty, r.image_url, 
                                       r.created_at, r.category_id, r.did,
                                       c.name as category_name, d.name as dietary_name,
                                       u.firstname, u.lastname
                                FROM recipes r
                                LEFT JOIN categories c ON r.category_id = c.category_id
                                LEFT JOIN dietary d ON r.did = d.did
                                LEFT JOIN users u ON r.user_id = u.user_id
                                WHERE r.category_id = ?
                                ORDER BY r.created_at DESC
                                LIMIT $limit OFFSET $offsetValue
                            ";
                            $recipesStmt = $pdo->prepare($sql);
                            $recipesStmt->execute([$selectedCategoryId]);
                        } else {
                            $sql = "
                                SELECT r.recipe_id, r.title, r.description, r.cook_time, r.difficulty, r.image_url, 
                                       r.created_at, r.category_id, r.did,
                                       c.name as category_name, d.name as dietary_name,
                                       u.firstname, u.lastname
                                FROM recipes r
                                LEFT JOIN categories c ON r.category_id = c.category_id
                                LEFT JOIN dietary d ON r.did = d.did
                                LEFT JOIN users u ON r.user_id = u.user_id
                                ORDER BY r.created_at DESC
                                LIMIT $limit OFFSET $offsetValue
                            ";
                            $recipesStmt = $pdo->query($sql);
                        }
                        if ($recipesStmt === false) {
                            throw new PDOException("Query execution failed");
                        }
                        $recipes = $recipesStmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        // Get ratings separately for each recipe (if ratings table exists)
                        $ratingsData = [];
                        try {
                            $checkRatings = $pdo->query("SHOW TABLES LIKE 'ratings'");
                            if ($checkRatings->rowCount() > 0) {
                                $recipeIds = array_column($recipes, 'recipe_id');
                                if (!empty($recipeIds)) {
                                    $placeholders = str_repeat('?,', count($recipeIds) - 1) . '?';
                                    $ratingsStmt = $pdo->prepare("
                                        SELECT recipe_id, 
                                               COUNT(*) as rating_count,
                                               AVG(rating) as avg_rating
                                        FROM ratings
                                        WHERE recipe_id IN ($placeholders)
                                        GROUP BY recipe_id
                                    ");
                                    $ratingsStmt->execute($recipeIds);
                                    $ratingsResults = $ratingsStmt->fetchAll(PDO::FETCH_ASSOC);
                                    
                                    foreach ($ratingsResults as $rating) {
                                        $ratingsData[$rating['recipe_id']] = [
                                            'rating_count' => intval($rating['rating_count']),
                                            'avg_rating' => round(floatval($rating['avg_rating']), 1)
                                        ];
                                    }
                                }
                            }
                        } catch (PDOException $e) {
                            // Ratings table doesn't exist or error, continue without ratings
                            error_log("Ratings query error (non-critical): " . $e->getMessage());
                        }
                        
                        // Add rating data to recipes
                        foreach ($recipes as &$recipe) {
                            $recipeId = $recipe['recipe_id'];
                            if (isset($ratingsData[$recipeId])) {
                                $recipe['rating_count'] = $ratingsData[$recipeId]['rating_count'];
                                $recipe['avg_rating'] = $ratingsData[$recipeId]['avg_rating'];
                            } else {
                                $recipe['rating_count'] = 0;
                                $recipe['avg_rating'] = 0;
                            }
                        }
                        unset($recipe); // Break reference
                        
                        if (empty($recipes)) {
                            echo '<div class="col-12"><div class="alert alert-info text-center"><i class="fas fa-info-circle me-2"></i>No recipes found. Be the first to add one!</div></div>';
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
                                $authorName = htmlspecialchars(($recipe['firstname'] ?? '') . ' ' . ($recipe['lastname'] ?? ''));
                                $avgRating = round($recipe['avg_rating'], 1);
                                $ratingCount = intval($recipe['rating_count']);
                                $isSaved = in_array($recipeId, $savedRecipeIds);
                                
                                // Determine difficulty badge color
                                $difficultyBadgeClass = 'bg-warning text-dark';
                                if ($difficulty === 'Easy') {
                                    $difficultyBadgeClass = 'bg-success';
                                } elseif ($difficulty === 'Hard') {
                                    $difficultyBadgeClass = 'bg-danger';
                                }
                                
                                echo '<div class="col-md-6 col-lg-4 recipe-item" ';
                                echo 'data-category="' . htmlspecialchars($recipe['category_id'] ?? '') . '" ';
                                echo 'data-dietary="' . htmlspecialchars($recipe['did'] ?? '') . '" ';
                                echo 'data-difficulty="' . strtolower($difficulty) . '">';
                                echo '<div class="recipe-card">';
                                echo '<div class="position-relative">';
                                
                                // Save button (only for logged-in users)
                                if ($currentUserId) {
                                    $savedClass = $isSaved ? 'saved' : '';
                                    $savedIcon = $isSaved ? 'fas' : 'far';
                                    echo '<button class="save-recipe-btn ' . $savedClass . '" data-recipe-id="' . $recipeId . '" title="' . ($isSaved ? 'Remove from saved' : 'Save recipe') . '">';
                                    echo '<i class="' . $savedIcon . ' fa-bookmark"></i>';
                                    echo '</button>';
                                }
                                
                                // Show image or placeholder
                                if (!empty($imageUrl)) {
                                    $cleanImageUrl = trim($imageUrl);
                                    $cleanImageUrl = str_replace('\\', '/', $cleanImageUrl);
                                    $urlParts = explode('/', $cleanImageUrl);
                                    $encodedParts = array_map('rawurlencode', $urlParts);
                                    $encodedUrl = implode('/', $encodedParts);
                                    
                                    echo '<img src="' . htmlspecialchars($encodedUrl) . '" class="recipe-img" alt="' . htmlspecialchars($title) . '" ';
                                    echo 'style="height: 200px; object-fit: cover; width: 100%;" ';
                                    echo 'onerror="this.onerror=null; this.style.display=\'none\'; ';
                                    echo 'var placeholder = document.createElement(\'div\'); ';
                                    echo 'placeholder.className = \'recipe-img bg-light d-flex align-items-center justify-content-center\'; ';
                                    echo 'placeholder.style.cssText = \'height: 200px;\'; ';
                                    echo 'placeholder.innerHTML = \'<i class=\\\'fas fa-image fa-3x text-muted\\\'></i>\'; ';
                                    echo 'this.parentElement.insertBefore(placeholder, this.nextSibling);">';
                                } else {
                                    echo '<div class="recipe-img bg-light d-flex align-items-center justify-content-center" style="height: 200px;">';
                                    echo '<i class="fas fa-image fa-3x text-muted"></i>';
                                    echo '</div>';
                                }
                                
                                echo '<span class="recipe-category">' . $categoryName . '</span>';
                                echo '</div>';
                                echo '<div class="p-4">';
                                echo '<h4>' . $title . '</h4>';
                                
                                // Author name
                                if (!empty($authorName)) {
                                    echo '<p class="text-muted small mb-2"><i class="fas fa-user me-1"></i>' . $authorName . '</p>';
                                }
                                
                                echo '<div class="d-flex justify-content-between align-items-center mb-2">';
                                echo '<span class="recipe-time"><i class="far fa-clock me-1"></i> ' . $cookTime . ' mins</span>';
                                echo '<span class="badge ' . $difficultyBadgeClass . '">' . $difficulty . '</span>';
                                echo '</div>';
                                
                                // Rating display
                                if ($ratingCount > 0) {
                                    echo '<div class="recipe-rating mb-3">';
                                    $fullStars = floor($avgRating);
                                    $hasHalfStar = ($avgRating - $fullStars) >= 0.5;
                                    
                                    for ($i = 0; $i < $fullStars; $i++) {
                                        echo '<i class="fas fa-star"></i>';
                                    }
                                    if ($hasHalfStar) {
                                        echo '<i class="fas fa-star-half-alt"></i>';
                                    }
                                    for ($i = 0; $i < (5 - $fullStars - ($hasHalfStar ? 1 : 0)); $i++) {
                                        echo '<i class="far fa-star"></i>';
                                    }
                                    echo '<span class="text-muted ms-1">(' . $ratingCount . ')</span>';
                                    echo '</div>';
                                }
                                
                                echo '<p class="text-muted">' . mb_substr($description, 0, 100) . (mb_strlen($description) > 100 ? '...' : '') . '</p>';
                                
                                if (!empty($dietaryName)) {
                                    echo '<div class="mb-2"><span class="badge bg-info">' . $dietaryName . '</span></div>';
                                }
                                
                                echo '<a href="recipe_detail.php?id=' . $recipeId . '" class="btn btn-outline-warning w-100">View Recipe</a>';
                                echo '</div>';
                                echo '</div>';
                                echo '</div>';
                            }
                        }
                    } catch (PDOException $e) {
                        $errorInfo = $e->errorInfo ?? [];
                        error_log("Error loading recipes: " . $e->getMessage());
                        error_log("SQL Error Code: " . $e->getCode());
                        error_log("SQL Error Info: " . print_r($errorInfo, true));
                        if (isset($sql)) {
                            error_log("SQL Query: " . $sql);
                        }
                        
                        // Show user-friendly error message with details for debugging
                        $errorMessage = 'Error loading recipes. ';
                        if (!empty($errorInfo[2])) {
                            $errorMessage .= 'SQL Error: ' . htmlspecialchars($errorInfo[2]);
                        } else {
                            $errorMessage .= htmlspecialchars($e->getMessage());
                        }
                        echo '<div class="col-12"><div class="alert alert-danger">' . $errorMessage . '</div></div>';
                    } catch (Exception $e) {
                        error_log("General error loading recipes: " . $e->getMessage());
                        echo '<div class="col-12"><div class="alert alert-danger">Error loading recipes: ' . htmlspecialchars($e->getMessage()) . '</div></div>';
                    }
                    ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <nav aria-label="Recipe pagination" class="mt-5">
                    <ul class="pagination justify-content-center">
                        <!-- Previous Button -->
                        <li class="page-item <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo max(1, $currentPage - 1); ?><?php echo $selectedCategoryId ? '&category=' . $selectedCategoryId : ''; ?>" 
                               <?php echo $currentPage <= 1 ? 'tabindex="-1" aria-disabled="true"' : ''; ?>>
                                Previous
                            </a>
                        </li>
                        
                        <?php
                        // Calculate page range to display
                        $maxPagesToShow = 5; // Show max 5 page numbers
                        $startPage = max(1, $currentPage - floor($maxPagesToShow / 2));
                        $endPage = min($totalPages, $startPage + $maxPagesToShow - 1);
                        
                        // Adjust start page if we're near the end
                        if ($endPage - $startPage < $maxPagesToShow - 1) {
                            $startPage = max(1, $endPage - $maxPagesToShow + 1);
                        }
                        
                        // Show first page if not in range
                        if ($startPage > 1) {
                            echo '<li class="page-item"><a class="page-link" href="?page=1' . ($selectedCategoryId ? '&category=' . $selectedCategoryId : '') . '">1</a></li>';
                            if ($startPage > 2) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                        }
                        
                        // Show page numbers
                        for ($i = $startPage; $i <= $endPage; $i++) {
                            $activeClass = ($i == $currentPage) ? 'active' : '';
                            echo '<li class="page-item ' . $activeClass . '">';
                            echo '<a class="page-link" href="?page=' . $i . ($selectedCategoryId ? '&category=' . $selectedCategoryId : '') . '">' . $i . '</a>';
                            echo '</li>';
                        }
                        
                        // Show last page if not in range
                        if ($endPage < $totalPages) {
                            if ($endPage < $totalPages - 1) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                            echo '<li class="page-item"><a class="page-link" href="?page=' . $totalPages . ($selectedCategoryId ? '&category=' . $selectedCategoryId : '') . '">' . $totalPages . '</a></li>';
                        }
                        ?>
                        
                        <!-- Next Button -->
                        <li class="page-item <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo min($totalPages, $currentPage + 1); ?><?php echo $selectedCategoryId ? '&category=' . $selectedCategoryId : ''; ?>" 
                               <?php echo $currentPage >= $totalPages ? 'tabindex="-1" aria-disabled="true"' : ''; ?>>
                                Next
                            </a>
                        </li>
                    </ul>
                    <div class="text-center mt-2 text-muted">
                        Showing <?php echo count($recipes); ?> of <?php echo $totalRecipes; ?> recipes (Page <?php echo $currentPage; ?> of <?php echo $totalPages; ?>)
                    </div>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>

  <?php include 'footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle save recipe button clicks
            document.querySelectorAll('.save-recipe-btn').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const recipeId = this.getAttribute('data-recipe-id');
                    const isSaved = this.classList.contains('saved');
                    const action = isSaved ? 'unsave' : 'save';
                    
                    // Disable button during request
                    this.disabled = true;
                    const originalHTML = this.innerHTML;
                    
                    const formData = new FormData();
                    formData.append('recipe_id', recipeId);
                    formData.append('action', action);
                    
                    fetch('save_recipe.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        this.disabled = false;
                        
                        if (data.success) {
                            // Toggle saved state
                            if (data.action === 'saved') {
                                this.classList.add('saved');
                                this.innerHTML = '<i class="fas fa-bookmark"></i>';
                                this.title = 'Remove from saved';
                            } else {
                                this.classList.remove('saved');
                                this.innerHTML = '<i class="far fa-bookmark"></i>';
                                this.title = 'Save recipe';
                            }
                            
                            // Show toast notification
                            const toast = document.createElement('div');
                            toast.className = 'position-fixed bottom-0 end-0 m-3 p-3 bg-warning text-white rounded shadow';
                            toast.style.zIndex = '1100';
                            toast.textContent = data.message;
                            document.body.appendChild(toast);
                            
                            setTimeout(() => {
                                toast.remove();
                            }, 3000);
                        } else {
                            alert(data.message || 'An error occurred. Please try again.');
                            this.innerHTML = originalHTML;
                        }
                    })
                    .catch(error => {
                        this.disabled = false;
                        this.innerHTML = originalHTML;
                        console.error('Save recipe error:', error);
                        alert('An error occurred. Please try again.');
                    });
                });
            });
            
            // Filter recipes
            function filterRecipes() {
                const searchTerm = document.getElementById('recipeSearch').value.toLowerCase();
                // Get selected categories from checkboxes
                const selectedCategories = Array.from(document.querySelectorAll('.filter-category:checked')).map(cb => cb.value);
                // If URL has category parameter and no checkboxes are selected, use URL category
                const urlParams = new URLSearchParams(window.location.search);
                const urlCategory = urlParams.get('category');
                const effectiveCategories = selectedCategories.length > 0 ? selectedCategories : (urlCategory ? [urlCategory] : []);
                const selectedDietary = Array.from(document.querySelectorAll('.filter-dietary:checked')).map(cb => cb.value);
                const selectedDifficulty = Array.from(document.querySelectorAll('.filter-difficulty:checked')).map(cb => cb.value);
                const recipeItems = document.querySelectorAll('.recipe-item');
                
                recipeItems.forEach(item => {
                    const title = item.querySelector('h4').textContent.toLowerCase();
                    const description = item.querySelector('.text-muted').textContent.toLowerCase();
                    const categoryId = item.getAttribute('data-category');
                    const dietaryId = item.getAttribute('data-dietary');
                    const difficulty = item.getAttribute('data-difficulty');
                    
                    // Check search term
                    const matchesSearch = searchTerm === '' || title.includes(searchTerm) || description.includes(searchTerm);
                    
                    // Check category filter
                    const matchesCategory = effectiveCategories.length === 0 || effectiveCategories.includes(categoryId);
                    
                    // Check dietary filter
                    const matchesDietary = selectedDietary.length === 0 || (dietaryId && selectedDietary.includes(dietaryId));
                    
                    // Check difficulty filter
                    const matchesDifficulty = selectedDifficulty.length === 0 || (difficulty && selectedDifficulty.includes(difficulty));
                    
                    // Show/hide recipe card
                    if (matchesSearch && matchesCategory && matchesDietary && matchesDifficulty) {
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
            
            // Difficulty checkboxes
            document.querySelectorAll('.filter-difficulty').forEach(checkbox => {
                checkbox.addEventListener('change', filterRecipes);
            });
            
            // Reset filters function
            window.resetFilters = function() {
                document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                    checkbox.checked = false;
                });
                if (searchInput) {
                    searchInput.value = '';
                }
                filterRecipes();
            };
            
            // Trigger filter on page load if there's a search query
            if (searchInput && searchInput.value.trim() !== '') {
                filterRecipes();
            }
        });
    </script>
</body>
</html>
