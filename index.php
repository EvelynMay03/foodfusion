<?php
    require 'auth_helper.php';
    include 'header.php';
    require 'pdo.php';
    session_start();
    
    // Check if user is logged in
    $isLoggedIn = isLoggedIn();
?>
<style>
    #featured .card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: none;
    }
    
    #featured .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.12) !important;
    }
    
    #featured .card-img-top {
        transition: transform 0.3s ease;
    }
    
    #featured .card:hover .card-img-top {
        transform: scale(1.05);
    }
    
    #featured .card-title {
        color: #1a202c;
        transition: color 0.2s ease;
    }
    
    #featured .card:hover .card-title {
        color: #28a745;
    }
    
    .featured-recipe-item {
        animation: fadeInUp 0.5s ease forwards;
        opacity: 0;
    }
    
    .featured-recipe-item.show {
        opacity: 1;
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    #seeMoreBtn {
        transition: all 0.3s ease;
    }
    
    #seeMoreBtn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(40, 167, 69, 0.3);
    }
</style>

    <!-- Hero Banner -->
    <section class="hero-banner position-relative">
        <div class="container h-100">
            <div class="row h-100 align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold text-navy mb-4">Discover & Share Amazing Recipes</h1>
                    <?php if ($isLoggedIn): ?>
                        <p class="lead text-navy mb-4">Welcome back! Explore recipes and share your culinary creations with our community.</p>
                        <div class="d-flex gap-3">
                            <a href="recipes.php" class="btn btn-light btn-lg px-4">Explore Recipes</a>
                            <a href="cookbook.php" class="btn btn-outline-light btn-lg">My Cookbook</a>
                        </div>
                    <?php else: ?>
                    <p class="lead text-navy mb-4">Join our community of food enthusiasts and explore a world of flavors</p>
                    <div class="d-flex gap-3">
                        <a href="#featured" class="btn btn-light btn-lg px-4">Explore Recipes</a>
                        <button class="btn btn-outline-light btn-lg" data-bs-toggle="modal" data-bs-target="#registerModal">Join Community</button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Recipes -->
    <section id="featured" class="py-5">
        <div class="container">
            <h2 class="text-center mb-4">Featured Recipes</h2>
            
            <!-- Search Bar -->
            <div class="row justify-content-center mb-5">
                <div class="col-md-8">
                    <form class="d-flex" id="mainSearchForm">
                        <input class="form-control form-control-lg me-2" type="search" id="mainSearchInput" placeholder="Search for recipes, ingredients, or cuisines...">
                        <button class="btn btn-success btn-lg px-4" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
            </div>
            <div id="noResultsMessage" class="row d-none">
                <div class="col-12">
                    <div class="alert alert-warning text-center">
                        <i class="fas fa-search me-2"></i>No recipes match your search. Try different keywords.
                    </div>
                </div>
            </div>
            <div class="row g-4" id="featuredRecipesContainer">
                <?php
                
                $initialLimit = 3;
                $totalRecipes = 0;
                
                try {
                    // First, get total count
                    $countStmt = $pdo->query("SELECT COUNT(*) as total FROM recipes");
                    $totalRecipes = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
                    
                    // Fetch ALL recipes
                    $recipesStmt = $pdo->prepare("
                        SELECT r.recipe_id, r.title, r.description, r.cook_time, r.difficulty, r.image_url,
                               c.name as category_name, d.name as dietary_name
                        FROM recipes r
                        LEFT JOIN categories c ON r.category_id = c.category_id
                        LEFT JOIN dietary d ON r.did = d.did
                        ORDER BY r.created_at DESC
                    ");
                    $recipesStmt->execute();
                    $recipes = $recipesStmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (empty($recipes)) {
                        echo '<div class="col-12"><p class="text-center text-muted">No recipes available yet. Be the first to share a recipe!</p></div>';
                    } else {
                        foreach ($recipes as $index => $recipe) {
                            $recipeId = htmlspecialchars($recipe['recipe_id']);
                            $title = htmlspecialchars($recipe['title']);
                            $description = htmlspecialchars($recipe['description'] ?? '');
                            // Truncate description if too long
                            if (strlen($description) > 100) {
                                $description = substr($description, 0, 100) . '...';
                            }
                            $cookTime = htmlspecialchars($recipe['cook_time'] ?? 'N/A');
                            $difficulty = htmlspecialchars($recipe['difficulty'] ?? 'Medium');
                            $categoryName = htmlspecialchars($recipe['category_name'] ?? 'Uncategorized');
                            $dietaryName = htmlspecialchars($recipe['dietary_name'] ?? '');
                            $imageUrl = $recipe['image_url'] ?? '';
                            
                            // Determine difficulty badge color
                            $difficultyBadgeClass = 'bg-warning text-dark';
                            if ($difficulty === 'Easy') {
                                $difficultyBadgeClass = 'bg-success';
                            } elseif ($difficulty === 'Hard') {
                                $difficultyBadgeClass = 'bg-danger';
                            }
                            
                            // Handle image URL
                            $displayImageUrl = '';
                            if (!empty($imageUrl)) {
                                // Encode the URL properly
                                $urlParts = explode('/', $imageUrl);
                                $encodedParts = array_map('rawurlencode', $urlParts);
                                $displayImageUrl = implode('/', $encodedParts);
                            }
                            
                            // Add class to hide recipes beyond initial limit
                            $hiddenClass = ($index >= $initialLimit) ? 'd-none featured-recipe-hidden' : '';
                            
                            // Create data attributes for search filtering
                            $searchData = 'data-title="' . strtolower($title) . '" data-description="' . strtolower($description) . '" data-category="' . strtolower($categoryName) . '"';
                            
                            echo '<div class="col-md-6 col-lg-4 featured-recipe-item ' . $hiddenClass . '" ' . $searchData . '>';
                            echo '<div class="card h-100 shadow-sm" style="border-radius: 12px; overflow: hidden; transition: transform 0.3s ease, box-shadow 0.3s ease;">';
                            echo '<a href="recipe_detail.php?id=' . $recipeId . '" class="text-decoration-none text-dark">';
                            
                            // Recipe Image
                            if (!empty($displayImageUrl)) {
                                echo '<img src="' . htmlspecialchars($displayImageUrl) . '" class="card-img-top" alt="' . $title . '" style="height: 200px; object-fit: cover;" ';
                                echo 'onerror="this.onerror=null; this.src=\'https://via.placeholder.com/400x200?text=' . urlencode($title) . '\';">';
                            } else {
                                echo '<div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">';
                                echo '<i class="fas fa-image fa-3x text-muted"></i>';
                                echo '</div>';
                            }
                            
                            echo '<div class="card-body d-flex flex-column">';
                            echo '<h5 class="card-title mb-2" style="font-family: \'Poppins\', sans-serif; font-weight: 600;">' . $title . '</h5>';
                            
                            if (!empty($description)) {
                                echo '<p class="card-text text-muted small mb-3 flex-grow-1" style="font-family: \'Poppins\', sans-serif;">' . $description . '</p>';
                            }
                            
                            echo '<div class="d-flex justify-content-between align-items-center mt-auto">';
                            echo '<span class="badge ' . $difficultyBadgeClass . '" style="font-family: \'Poppins\', sans-serif; font-weight: 500;">' . $difficulty . '</span>';
                            echo '<small class="text-muted" style="font-family: \'Poppins\', sans-serif;"><i class="far fa-clock me-1"></i>' . $cookTime . ' mins</small>';
                            echo '</div>';
                            
                            if (!empty($categoryName)) {
                                echo '<div class="mt-2">';
                                echo '<span class="badge bg-warning text-dark" style="font-family: \'Poppins\', sans-serif; font-weight: 500; font-size: 0.75rem;">' . $categoryName . '</span>';
                                if (!empty($dietaryName)) {
                                    echo ' <span class="badge bg-success" style="font-family: \'Poppins\', sans-serif; font-weight: 500; font-size: 0.75rem;">' . $dietaryName . '</span>';
                                }
                                echo '</div>';
                            }
                            
                            echo '</div>';
                            echo '</a>';
                            echo '</div>';
                            echo '</div>';
                        }
                    }
                } catch (PDOException $e) {
                    error_log("Error loading featured recipes: " . $e->getMessage());
                    echo '<div class="col-12"><p class="text-center text-danger">Error loading recipes. Please try again later.</p></div>';
                }
                ?>
            </div>
            
            <?php if ($totalRecipes > $initialLimit): ?>
            <div class="text-center mt-5">
                <button id="seeMoreBtn" class="btn btn-success btn-lg px-5" style="font-family: 'Poppins', sans-serif; font-weight: 600; border-radius: 12px;">
                    <i class="fas fa-arrow-down me-2"></i>See More Recipes
                </button>
                <a href="recipes.php" class="btn btn-outline-success btn-lg px-5 ms-3" style="font-family: 'Poppins', sans-serif; font-weight: 600; border-radius: 12px;">
                    <i class="fas fa-list me-2"></i>View All Recipes
                </a>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Categories -->
    <section id="categories" class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5">Recipe Categories</h2>
            <div class="row g-4">
                <?php
                try {
                    // Fetch all categories from database
                    $categoriesStmt = $pdo->query("SELECT category_id, name FROM categories ORDER BY name");
                    $categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (empty($categories)) {
                        echo '<div class="col-12"><p class="text-center text-muted">No categories available yet.</p></div>';
                    } else {
                        foreach ($categories as $category) {
                            $categoryId = htmlspecialchars($category['category_id']);
                            $categoryName = htmlspecialchars($category['name']);
                            // // Use a placeholder image or you can add image_url column to categories table later
                            // $categoryImage = 'https://via.placeholder.com/150?text=' . urlencode($categoryName);
                            
                            echo '<div class="col-6 col-md-4 col-lg-2">';
                            echo '<a href="recipes.php?category=' . $categoryId . '" class="category-card text-decoration-none">';
                            echo '<div class="card h-100">';
                            // echo '<img src="' . htmlspecialchars($categoryImage) . '" class="card-img-top" alt="' . $categoryName . '" ';
                            // echo 'onerror="this.onerror=null; this.src=\'https://via.placeholder.com/150?text=' . urlencode($categoryName) . '\';">';
                            echo '<div class="card-body text-center">';
                            echo '<h5 class="card-title mb-0">' . $categoryName . '</h5>';
                            echo '</div>';
                            echo '</div>';
                            echo '</a>';
                            echo '</div>';
                        }
                    }
                } catch (PDOException $e) {
                    error_log("Error loading categories: " . $e->getMessage());
                    echo '<div class="col-12"><p class="text-center text-danger">Error loading categories. Please try again later.</p></div>';
                }
                ?>
            </div>
        </div>
    </section>

    <!-- Community Highlights -->
    <section id="community" class="py-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0" style="font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 2rem; color: #1a202c;">Community Highlights</h2>
                <a href="recipes.php" class="text-decoration-none" style="font-family: 'Poppins', sans-serif; font-weight: 500; color: #3182ce; font-size: 0.9375rem;">View All</a>
            </div>
            <div class="row g-4">
                <?php
                try {
                    // Check if comments table exists
                    $checkComments = $pdo->query("SHOW TABLES LIKE 'comments'");
                    $recentComments = [];
                    
                    if ($checkComments->rowCount() > 0) {
                        // Fetch recent comments with recipe and user information
                        $commentsStmt = $pdo->prepare("
                            SELECT c.id, c.comment, c.created_at,
                                   r.recipe_id, r.title as recipe_title,
                                   u.user_id, u.firstname, u.lastname, u.image as user_image
                            FROM comments c
                            INNER JOIN recipes r ON c.recipe_id = r.recipe_id
                            INNER JOIN users u ON c.user_id = u.user_id
                            ORDER BY c.created_at DESC
                            LIMIT 3
                        ");
                        $commentsStmt->execute();
                        $recentComments = $commentsStmt->fetchAll(PDO::FETCH_ASSOC);
                    }
                    
                    // Fetch ratings for these recipes if available
                    $ratingsData = [];
                    if (!empty($recentComments)) {
                        $recipeIds = array_column($recentComments, 'recipe_id');
                        $userIds = array_column($recentComments, 'user_id');
                        
                        try {
                            $checkRatings = $pdo->query("SHOW TABLES LIKE 'ratings'");
                            if ($checkRatings->rowCount() > 0) {
                                $placeholders = str_repeat('?,', count($recipeIds) - 1) . '?';
                                $ratingsStmt = $pdo->prepare("
                                    SELECT recipe_id, user_id, rating
                                    FROM ratings
                                    WHERE recipe_id IN ($placeholders) AND user_id IN ($placeholders)
                                ");
                                $ratingsStmt->execute(array_merge($recipeIds, $userIds));
                                $ratingsResults = $ratingsStmt->fetchAll(PDO::FETCH_ASSOC);
                                
                                foreach ($ratingsResults as $rating) {
                                    $key = $rating['recipe_id'] . '_' . $rating['user_id'];
                                    $ratingsData[$key] = intval($rating['rating']);
                                }
                            }
                        } catch (PDOException $e) {
                            error_log("Error fetching ratings: " . $e->getMessage());
                        }
                    }
                    
                    if (empty($recentComments)) {
                        echo '<div class="col-12"><p class="text-center text-muted">No community activity yet. Be the first to comment on a recipe!</p></div>';
                    } else {
                        foreach ($recentComments as $comment) {
                            $commentId = htmlspecialchars($comment['id']);
                            $commentText = htmlspecialchars($comment['comment']);
                            $recipeId = htmlspecialchars($comment['recipe_id']);
                            $recipeTitle = htmlspecialchars($comment['recipe_title']);
                            $userId = htmlspecialchars($comment['user_id']);
                            $firstName = htmlspecialchars($comment['firstname'] ?? '');
                            $lastName = htmlspecialchars($comment['lastname'] ?? '');
                            $userName = trim($firstName . ' ' . $lastName);
                            if (empty($userName)) {
                                $userName = 'Anonymous';
                            }
                            $userImage = $comment['user_image'] ?? '';
                            $createdAt = $comment['created_at'];
                            
                            // Calculate time ago
                            $timestamp = strtotime($createdAt);
                            $timeAgo = '';
                            $diff = time() - $timestamp;
                            if ($diff < 60) {
                                $timeAgo = 'Just now';
                            } elseif ($diff < 3600) {
                                $mins = floor($diff / 60);
                                $timeAgo = $mins . ' ' . ($mins === 1 ? 'MINUTE' : 'MINUTES') . ' AGO';
                            } elseif ($diff < 86400) {
                                $hours = floor($diff / 3600);
                                $timeAgo = $hours . ' ' . ($hours === 1 ? 'HOUR' : 'HOURS') . ' AGO';
                            } elseif ($diff < 604800) {
                                $days = floor($diff / 86400);
                                $timeAgo = $days . ' ' . ($days === 1 ? 'DAY' : 'DAYS') . ' AGO';
                            } elseif ($diff < 2592000) {
                                $weeks = floor($diff / 604800);
                                $timeAgo = $weeks . ' ' . ($weeks === 1 ? 'WEEK' : 'WEEKS') . ' AGO';
                            } else {
                                $timeAgo = date('M j, Y', $timestamp);
                            }
                            
                            // Get user rating if available
                            $userRating = 0;
                            if (!empty($ratingsData)) {
                                $key = $recipeId . '_' . $userId;
                                if (isset($ratingsData[$key])) {
                                    $userRating = $ratingsData[$key];
                                }
                            }
                            
                            // Get user profile image URL
                            $avatarUrl = null;
                            if (!empty($userImage) && file_exists('image_upload_helper.php')) {
                                require_once 'image_upload_helper.php';
                                $avatarUrl = getProfileImageUrl($userImage);
                            }
                            
                            echo '<div class="col-md-4">';
                            echo '<div class="card h-100 shadow-sm" style="background: white; border-radius: 12px; border: 1px solid #e2e8f0; padding: 20px; transition: all 0.3s ease;">';
                            echo '<div class="card-body p-0 d-flex flex-column">';
                            
                            // User info and action header
                            echo '<div class="d-flex align-items-center mb-3">';
                            
                            // Avatar
                            if (!empty($avatarUrl)) {
                                echo '<img src="' . htmlspecialchars($avatarUrl) . '" alt="' . $userName . '" class="rounded-circle me-3" style="width: 48px; height: 48px; object-fit: cover; border: 2px solid #e2e8f0; flex-shrink: 0;" ';
                                echo 'onerror="this.onerror=null; this.style.display=\'none\'; this.nextElementSibling.style.display=\'flex\';">';
                                echo '<div class="rounded-circle d-none align-items-center justify-content-center me-3" style="width: 48px; height: 48px; background: #e2e8f0; border: 2px solid #e2e8f0; flex-shrink: 0;">';
                                echo '<i class="fas fa-user text-muted"></i>';
                                echo '</div>';
                            } else {
                                echo '<div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px; background: #e2e8f0; border: 2px solid #e2e8f0; flex-shrink: 0;">';
                                echo '<i class="fas fa-user text-muted"></i>';
                                echo '</div>';
                            }
                            
                            // Username and action
                            echo '<div class="flex-grow-1" style="min-width: 0;">';
                            echo '<div style="font-family: \'Poppins\', sans-serif; font-weight: 500; font-size: 0.875rem; line-height: 1.4; word-wrap: break-word;">';
                            echo '<a href="profile.php?id=' . $userId . '" class="text-decoration-none" style="color: #3182ce; font-weight: 600;">' . $userName . '</a>';
                            echo '<span style="color: #1a202c;"> reviewed </span>';
                            echo '<a href="recipe_detail.php?id=' . $recipeId . '" class="text-decoration-none" style="color: #3182ce; font-weight: 600;">' . $recipeTitle . '</a>';
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';
                            
                            // Rating stars (if user rated)
                            if ($userRating > 0) {
                                echo '<div class="mb-3" style="color: #dc3545; font-size: 0.875rem;">';
                                for ($i = 0; $i < 5; $i++) {
                                    if ($i < $userRating) {
                                        echo '<i class="fas fa-star"></i>';
                                    } else {
                                        echo '<i class="far fa-star"></i>';
                                    }
                                }
                                echo '</div>';
                            }
                            
                            // Comment text
                            echo '<p class="mb-3 flex-grow-1" style="font-family: \'Poppins\', sans-serif; font-weight: 400; font-size: 0.9375rem; color: #1a202c; line-height: 1.6; margin-bottom: 1rem;">' . nl2br($commentText) . '</p>';
                            
                            // Footer with timestamp and actions
                            echo '<div class="d-flex justify-content-between align-items-center mt-auto pt-3" style="border-top: 1px solid #e2e8f0;">';
                            echo '<small style="font-family: \'Poppins\', sans-serif; font-weight: 400; font-size: 0.75rem; color: #718096; text-transform: uppercase; letter-spacing: 0.5px;">' . $timeAgo . '</small>';
                            echo '<div class="d-flex align-items-center gap-3">';
                           
                     
                            echo '</div>';
                            echo '</div>';
                            
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';
                        }
                    }
                } catch (PDOException $e) {
                    error_log("Error loading community highlights: " . $e->getMessage());
                    echo '<div class="col-12"><p class="text-center text-danger">Error loading community activity. Please try again later.</p></div>';
                }
                ?>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-5 bg-light">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h2>About FoodFusion</h2>
                    <p class="lead">Connecting food lovers and home chefs from around the world.</p>
                    <p>FoodFusion is a community-driven platform where food enthusiasts can discover, share, and celebrate recipes from diverse cuisines. Our mission is to make cooking enjoyable and accessible to everyone.</p>
                </div>
                <div class="col-lg-6">
                    <img src="uploads/profiles/foodLover.gif" alt="About Us" class="img-fluid rounded">
                </div>
            </div>
        </div>
    </section>

   

    
    <!-- Cookie Consent Banner -->
    <div class="cookie-consent-banner position-fixed bottom-0 start-0 w-100 bg-dark text-white p-3 d-none">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8 mb-3 mb-md-0">
                    <p class="mb-0">We use cookies to enhance your experience. By continuing to visit this site, you agree to our use of cookies. <a href="#" class="text-white text-decoration-underline">Learn more</a></p>
                </div>
                <div class="col-md-4 text-md-end">
                    <button class="btn btn-outline-light me-2" id="cookieDecline">Decline</button>
                    <button class="btn btn-success" id="cookieAccept">Accept</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="js/main.js"></script>
    <script>
        // See More functionality for Featured Recipes
        document.addEventListener('DOMContentLoaded', function() {
            const seeMoreBtn = document.getElementById('seeMoreBtn');
            const hiddenRecipes = document.querySelectorAll('.featured-recipe-hidden');
            
            if (seeMoreBtn && hiddenRecipes.length > 0) {
                let recipesToShow = 3; // Show 3 more recipes at a time
                let currentIndex = 0;
                
                seeMoreBtn.addEventListener('click', function() {
                    // Show next batch of hidden recipes
                    const recipesToDisplay = Array.from(hiddenRecipes).slice(currentIndex, currentIndex + recipesToShow);
                    
                    if (recipesToDisplay.length > 0) {
                        recipesToDisplay.forEach((recipe, index) => {
                            setTimeout(() => {
                                recipe.classList.remove('d-none', 'featured-recipe-hidden');
                                recipe.classList.add('show');
                            }, index * 100); // Stagger animation
                        });
                        
                        currentIndex += recipesToShow;
                        
                        // Hide button if all recipes are shown
                        if (currentIndex >= hiddenRecipes.length) {
                            seeMoreBtn.style.display = 'none';
                        }
                    }
                });
            }
            
            // Search functionality for Featured Recipes
            const mainSearchForm = document.getElementById('mainSearchForm');
            const mainSearchInput = document.getElementById('mainSearchInput');
            const noResultsMessage = document.getElementById('noResultsMessage');
            const featuredRecipesContainer = document.getElementById('featuredRecipesContainer');
            
            function filterFeaturedRecipes() {
                const searchTerm = mainSearchInput.value.toLowerCase().trim();
                const allRecipes = document.querySelectorAll('.featured-recipe-item');
                let visibleCount = 0;
                
                allRecipes.forEach(recipe => {
                    const title = recipe.getAttribute('data-title') || '';
                    const description = recipe.getAttribute('data-description') || '';
                    const category = recipe.getAttribute('data-category') || '';
                    
                    // Check if search term matches title, description, or category
                    const matchesSearch = searchTerm === '' || 
                                        title.includes(searchTerm) || 
                                        description.includes(searchTerm) || 
                                        category.includes(searchTerm);
                    
                    if (matchesSearch) {
                        recipe.style.display = '';
                        recipe.classList.remove('d-none');
                        visibleCount++;
                    } else {
                        recipe.style.display = 'none';
                    }
                });
                
                // Show/hide no results message
                if (visibleCount === 0 && searchTerm !== '') {
                    noResultsMessage.classList.remove('d-none');
                } else {
                    noResultsMessage.classList.add('d-none');
                }
                
                // Hide "See More" button when searching
                if (searchTerm !== '' && seeMoreBtn) {
                    seeMoreBtn.style.display = 'none';
                } else if (seeMoreBtn && currentIndex < hiddenRecipes.length) {
                    seeMoreBtn.style.display = '';
                }
            }
            
            // Handle form submission
            if (mainSearchForm) {
                mainSearchForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    filterFeaturedRecipes();
                });
            }
            
            // Handle real-time search as user types
            if (mainSearchInput) {
                mainSearchInput.addEventListener('input', filterFeaturedRecipes);
            }
        });
    </script>
</body>
</html>
<?php
    include 'footer.php';
   ?>

