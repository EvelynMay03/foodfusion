<?php
require 'auth_helper.php';
include 'header.php';
require 'pdo.php';

// Get recipe ID from URL
$recipeId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($recipeId <= 0) {
    header('Location: recipes.php');
    exit;
}

// Get current user ID if logged in
$currentUserId = isLoggedIn() ? getUserId() : null;
$isSaved = false;

try {
    // Fetch recipe details
    $recipeStmt = $pdo->prepare("
        SELECT r.recipe_id, r.title, r.description, r.cook_time, r.difficulty, r.image_url, 
               r.created_at, r.category_id, r.did,
               c.name as category_name, d.name as dietary_name,
               u.user_id as author_id, u.firstname, u.lastname, u.image as author_image
        FROM recipes r
        LEFT JOIN categories c ON r.category_id = c.category_id
        LEFT JOIN dietary d ON r.did = d.did
        LEFT JOIN users u ON r.user_id = u.user_id
        WHERE r.recipe_id = ?
    ");
    $recipeStmt->execute([$recipeId]);
    $recipe = $recipeStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$recipe) {
        header('Location: recipes.php');
        exit;
    }
    
    // Check if recipe is saved by current user
    if ($currentUserId) {
        try {
            $checkSavedStmt = $pdo->prepare("SELECT id FROM saved_recipes WHERE user_id = ? AND recipe_id = ?");
            $checkSavedStmt->execute([$currentUserId, $recipeId]);
            $isSaved = $checkSavedStmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error checking saved recipe: " . $e->getMessage());
        }
    }
    
    // Fetch ingredients
    $ingredientsStmt = $pdo->prepare("
        SELECT i.name as ingredient_name, ri.quantity
        FROM recipe_ingredients ri
        INNER JOIN ingredients i ON ri.ingredient_id = i.ingredient_id
        WHERE ri.recipe_id = ?
        ORDER BY ri.recipeIngre_id ASC
    ");
    $ingredientsStmt->execute([$recipeId]);
    $ingredients = $ingredientsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fetch instructions
    $instructionsStmt = $pdo->prepare("
        SELECT instruction, instruction_id
        FROM instructions
        WHERE recipe_id = ?
        ORDER BY instruction_id ASC
    ");
    $instructionsStmt->execute([$recipeId]);
    $instructions = $instructionsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fetch ratings if available
    $avgRating = 0;
    $ratingCount = 0;
    $userRating = 0; // Current user's rating
    try {
        $checkRatings = $pdo->query("SHOW TABLES LIKE 'ratings'");
        if ($checkRatings->rowCount() > 0) {
            // Get average rating and count
            $ratingsStmt = $pdo->prepare("
                SELECT COUNT(*) as rating_count, AVG(rating) as avg_rating
                FROM ratings
                WHERE recipe_id = ?
            ");
            $ratingsStmt->execute([$recipeId]);
            $ratingData = $ratingsStmt->fetch(PDO::FETCH_ASSOC);
            if ($ratingData) {
                $ratingCount = intval($ratingData['rating_count']);
                $avgRating = round(floatval($ratingData['avg_rating']), 1);
            }
            
            // Get current user's rating if logged in
            if ($currentUserId) {
                $userRatingStmt = $pdo->prepare("SELECT rating FROM ratings WHERE recipe_id = ? AND user_id = ?");
                $userRatingStmt->execute([$recipeId, $currentUserId]);
                $userRatingData = $userRatingStmt->fetch(PDO::FETCH_ASSOC);
                if ($userRatingData) {
                    $userRating = intval($userRatingData['rating']);
                }
            }
        }
    } catch (PDOException $e) {
        error_log("Error fetching ratings: " . $e->getMessage());
    }
    
    // Fetch comments
    $comments = [];
    try {
        $checkComments = $pdo->query("SHOW TABLES LIKE 'comments'");
        if ($checkComments->rowCount() > 0) {
            $commentsStmt = $pdo->prepare("
                SELECT c.id, c.comment, c.created_at,
                       u.user_id, u.firstname, u.lastname, u.image as user_image
                FROM comments c
                INNER JOIN users u ON c.user_id = u.user_id
                WHERE c.recipe_id = ?
                ORDER BY c.created_at DESC
            ");
            $commentsStmt->execute([$recipeId]);
            $comments = $commentsStmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        error_log("Error fetching comments: " . $e->getMessage());
    }
    
    // Prepare data for display
    $title = htmlspecialchars($recipe['title']);
    $description = htmlspecialchars($recipe['description']);
    $cookTime = htmlspecialchars($recipe['cook_time']);
    $difficulty = htmlspecialchars($recipe['difficulty']);
    $categoryName = htmlspecialchars($recipe['category_name'] ?? 'Uncategorized');
    $dietaryName = htmlspecialchars($recipe['dietary_name'] ?? '');
    $imageUrl = $recipe['image_url'];
    $authorName = htmlspecialchars(trim(($recipe['firstname'] ?? '') . ' ' . ($recipe['lastname'] ?? '')));
    $authorId = $recipe['author_id'];
    $createdAt = $recipe['created_at'];
    
    // Determine difficulty badge color
    $difficultyBadgeClass = 'bg-warning text-dark';
    if ($difficulty === 'Easy') {
        $difficultyBadgeClass = 'bg-success';
    } elseif ($difficulty === 'Hard') {
        $difficultyBadgeClass = 'bg-danger';
    }
    
} catch (PDOException $e) {
    error_log("Error loading recipe: " . $e->getMessage());
    header('Location: recipes.php');
    exit;
}
?>

<style>
    body {
        font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    .recipe-detail-hero {
        background: linear-gradient(135deg,rgb(67, 130, 172) 0%,rgb(240, 249, 181) 100%);
        color: white;
        padding: 60px 0;
        margin-bottom: 40px;
    }
    .recipe-image-container {
        position: relative;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        margin-bottom: 30px;
    }
    .recipe-image-container img {
        width: 100%;
        height: 500px;
        object-fit: cover;
    }
    .recipe-image-placeholder {
        width: 100%;
        height: 500px;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6c757d;
    }
    .recipe-meta {
        background: #ffffff;
        border-radius: 20px;
        padding: 32px;
        border: 1px solid #f0f4f8;
        margin-bottom: 30px;
        font-family: 'Poppins', sans-serif;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }
    
    .recipe-meta h4 {
        font-family: 'Poppins', sans-serif;
        font-weight: 700;
        font-size: 1.5rem;
        color: #0f172a;
        margin-bottom: 28px;
        letter-spacing: -0.5px;
        border-bottom: 2px solid #f1f5f9;
        padding-bottom: 16px;
    }
    
    .recipe-meta > .mb-3,
    .recipe-meta > div.mb-3 {
        padding: 14px 0;
        border-bottom: 1px solid #f8fafc;
        margin-bottom: 0;
        transition: all 0.2s ease;
    }
    
    .recipe-meta > .mb-3:last-child,
    .recipe-meta > div.mb-3:last-of-type {
        border-bottom: none;
    }
    
    .recipe-meta > .mb-3:hover,
    .recipe-meta > div.mb-3:hover {
        background: #fafbfc;
        margin-left: -12px;
        margin-right: -12px;
        padding-left: 12px;
        padding-right: 12px;
        border-radius: 10px;
    }
    
    .recipe-meta strong {
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        font-size: 0.8125rem;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        display: flex;
        align-items: center;
        margin-bottom: 8px;
    }
    
    .recipe-meta strong i {
        font-size: 0.875rem;
        margin-right: 10px;
        color: #94a3b8;
        width: 18px;
        text-align: center;
    }
    
    .recipe-meta .text-muted {
        font-family: 'Poppins', sans-serif;
        font-weight: 400;
        font-size: 0.9375rem;
        color: #334155;
        line-height: 1.5;
    }
    
    .recipe-meta .badge {
        font-family: 'Poppins', sans-serif;
        font-weight: 500;
        font-size: 0.8125rem;
        padding: 6px 12px;
        border-radius: 8px;
        letter-spacing: 0.3px;
    }
    
    .recipe-meta .author-info {
        background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 20px;
        margin-bottom: 24px;
    }
    
    .recipe-meta .author-info strong {
        font-family: 'Poppins', sans-serif;
        font-weight: 500;
        font-size: 0.75rem;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: block;
        margin-bottom: 6px;
    }
    
    .recipe-meta .author-info .text-muted {
        font-family: 'Poppins', sans-serif;
        font-weight: 500;
        font-size: 0.9375rem;
        color: #1e293b;
    }
    
    .recipe-meta .btn {
        font-family: 'Poppins', sans-serif;
        font-weight: 500;
        border-radius: 10px;
        padding: 12px 20px;
        transition: all 0.2s ease;
    }
    
    .recipe-meta .alert {
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        font-family: 'Poppins', sans-serif;
    }
    .ingredients-section, .instructions-section {
        background: white;
        border-radius: 10px;
        padding: 30px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        margin-bottom: 30px;
    }
    .ingredient-item {
        padding: 12px 15px;
        margin-bottom: 10px;
        background: #f8f9fa;
        border-radius: 8px;
        border-left: 4px solid var(--accent-yellow, #ffc107);
    }
    .instruction-step {
        padding: 20px;
        margin-bottom: 20px;
        background: #f8f9fa;
        border-radius: 8px;
        border-left: 4px solid var(--accent-yellow, #ffc107);
        position: relative;
    }
    .instruction-step-number {
        position: absolute;
        left: -15px;
        top: 20px;
        background: var(--accent-yellow, #ffc107);
        color: white;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 14px;
    }
    .author-info {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 16px;
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        border-radius: 12px;
        margin-bottom: 24px;
        border: 1px solid #e9ecef;
        transition: all 0.3s ease;
    }
    
    .author-info:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        transform: translateY(-2px);
    }
    
    .author-info strong {
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        font-size: 0.75rem;
        color: #718096;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-bottom: 4px;
        display: block;
    }
    
    .author-info .text-muted {
        font-family: 'Poppins', sans-serif;
        font-weight: 500;
        font-size: 0.9375rem;
        color: #2d3748;
    }
    
    .author-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #e9ecef;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .author-placeholder {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #adb5bd;
        border: 2px solid #e9ecef;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .author-placeholder i {
        font-size: 1.2rem;
    }
    .star-rating {
        display: flex;
        flex-direction: row-reverse;
        justify-content: flex-end;
        gap: 5px;
    }
    .star-rating input[type="radio"] {
        display: none;
    }
    .star-rating label {
        cursor: pointer;
        font-size: 30px;
        color: #ddd;
        transition: color 0.2s;
    }
    .star-rating label:hover,
    .star-rating label:hover ~ label {
        color: #ffc107;
    }
    .star-rating input[type="radio"]:checked ~ label,
    .star-rating input[type="radio"]:checked ~ label ~ label {
        color: #ffc107;
    }
    .star-rating input[type="radio"]:checked + label {
        color: #ffc107;
    }
</style>

<!-- Hero Section -->
<div class="recipe-detail-hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb" style="background: rgba(255,255,255,0.2); padding: 10px 15px; border-radius: 5px;">
                        <li class="breadcrumb-item"><a href="index.php" class="text-white">Home</a></li>
                        <li class="breadcrumb-item"><a href="recipes.php" class="text-white">Recipes</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page"><?php echo $title; ?></li>
                    </ol>
                </nav>
                <h1 class="display-4 fw-bold mb-3"><?php echo $title; ?></h1>
                <p class="lead mb-4"><?php echo $description; ?></p>
                <div class="d-flex flex-wrap gap-3 align-items-center">
                    <span class="badge <?php echo $difficultyBadgeClass; ?> fs-6 px-3 py-2">
                        <i class="fas fa-signal me-1"></i><?php echo $difficulty; ?>
                    </span>
                    <span class="badge bg-primary fs-6 px-3 py-2">
                        <i class="far fa-clock me-1"></i><?php echo $cookTime; ?> mins
                    </span>
                    <span class="badge bg-danger fs-6 px-3 py-2">
                        <i class="fas fa-tag me-1"></i><?php echo $categoryName; ?>
                    </span>
                    <?php if (!empty($dietaryName)): ?>
                    <span class="badge bg-success fs-6 px-3 py-2">
                        <i class="fas fa-leaf me-1"></i><?php echo $dietaryName; ?>
                    </span>
                    <?php endif; ?>
                    <?php if ($ratingCount > 0): ?>
                    <span class="badge bg-warning text-dark fs-6 px-3 py-2">
                        <i class="fas fa-star me-1"></i><?php echo $avgRating; ?> (<?php echo $ratingCount; ?>)
                    </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recipe Content -->
<div class="container mb-5">
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Recipe Image -->
            <div class="recipe-image-container">
                <?php if (!empty($imageUrl)): 
                    $cleanImageUrl = trim($imageUrl);
                    $cleanImageUrl = str_replace('\\', '/', $cleanImageUrl);
                    $urlParts = explode('/', $cleanImageUrl);
                    $encodedParts = array_map('rawurlencode', $urlParts);
                    $encodedUrl = implode('/', $encodedParts);
                ?>
                    <img src="<?php echo htmlspecialchars($encodedUrl); ?>" 
                         alt="<?php echo $title; ?>" 
                         onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="recipe-image-placeholder" style="display: none;">
                        <i class="fas fa-image fa-5x"></i>
                    </div>
                <?php else: ?>
                    <div class="recipe-image-placeholder">
                        <i class="fas fa-image fa-5x"></i>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Recipe Meta -->
            <div class="recipe-meta">
                <h4 class="mb-4">Recipe Details</h4>
                
                <!-- Author Info -->
                <?php if (!empty($authorName)): ?>
                <div class="author-info">
                    <?php 
                    // Get author profile image using the same helper function as elsewhere
                    $authorImageUrl = null;
                    if (file_exists('image_upload_helper.php')) {
                        require_once 'image_upload_helper.php';
                        $authorImageFilename = $recipe['author_image'] ?? null;
                        if (!empty($authorImageFilename)) {
                            $authorImageUrl = getProfileImageUrl($authorImageFilename);
                            // Verify file exists
                            if ($authorImageUrl && !file_exists(__DIR__ . '/' . $authorImageUrl)) {
                                $authorImageUrl = null;
                            }
                        }
                    }
                    ?>
                    <?php if (!empty($authorImageUrl)): ?>
                        <img src="<?php echo htmlspecialchars($authorImageUrl); ?>" 
                             alt="<?php echo $authorName; ?>" 
                             class="author-avatar"
                             onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="author-placeholder" style="display: none;">
                            <i class="fas fa-user"></i>
                        </div>
                    <?php else: ?>
                        <div class="author-placeholder">
                            <i class="fas fa-user"></i>
                        </div>
                    <?php endif; ?>
                    <div>
                        <strong>Created by</strong><br>
                        <span class="text-muted"><?php echo $authorName; ?></span>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="mb-3">
                    <strong><i class="far fa-clock me-2"></i>Cooking Time:</strong>
                    <span class="text-muted"><?php echo $cookTime; ?> minutes</span>
                </div>
                
                <div class="mb-3">
                    <strong><i class="fas fa-signal me-2"></i>Difficulty:</strong>
                    <span class="badge <?php echo $difficultyBadgeClass; ?>"><?php echo $difficulty; ?></span>
                </div>
                
                <div class="mb-3">
                    <strong><i class="fas fa-tag me-2"></i>Category:</strong>
                    <span class="text-muted"><?php echo $categoryName; ?></span>
                </div>
                
                <?php if (!empty($dietaryName)): ?>
                <div class="mb-3">
                    <strong><i class="fas fa-leaf me-2"></i>Dietary:</strong>
                    <span class="badge bg-success"><?php echo $dietaryName; ?></span>
                </div>
                <?php endif; ?>
                
                <?php if ($ratingCount > 0): ?>
                <div class="mb-3">
                    <strong><i class="fas fa-star me-2"></i>Rating:</strong>
                    <div class="mt-2">
                        <div class="text-warning mb-1">
                            <?php 
                            $fullStars = floor($avgRating);
                            $halfStar = ($avgRating - $fullStars) >= 0.5;
                            for ($i = 0; $i < $fullStars; $i++) {
                                echo '<i class="fas fa-star"></i>';
                            }
                            if ($halfStar) {
                                echo '<i class="fas fa-star-half-alt"></i>';
                            }
                            for ($i = $fullStars + ($halfStar ? 1 : 0); $i < 5; $i++) {
                                echo '<i class="far fa-star"></i>';
                            }
                            ?>
                        </div>
                        <span class="text-muted"><?php echo $avgRating; ?> / 5.0</span>
                        <small class="text-muted d-block">(<?php echo $ratingCount; ?> <?php echo $ratingCount === 1 ? 'rating' : 'ratings'; ?>)</small>
                        <?php if ($userRating > 0): ?>
                        <small class="text-success d-block mt-1"><i class="fas fa-check-circle me-1"></i>You rated: <?php echo $userRating; ?> stars</small>
                        <?php endif; ?>
                    </div>
                </div>
                <?php elseif ($currentUserId): ?>
                <div class="mb-3">
                    <strong><i class="fas fa-star me-2"></i>Rating:</strong>
                    <span class="text-muted">No ratings yet</span>
                </div>
                <?php endif; ?>
                
                <div class="mb-3">
                    <strong><i class="far fa-calendar me-2"></i>Created:</strong>
                    <span class="text-muted"><?php echo date('F j, Y', strtotime($createdAt)); ?></span>
                </div>
                
                <!-- Save Recipe Button (if logged in) -->
                <?php if ($currentUserId): ?>
                <div class="mt-4">
                    <button id="saveRecipeBtn" class="btn <?php echo $isSaved ? 'btn-warning' : 'btn-outline-warning'; ?> w-100 mb-3" 
                            data-recipe-id="<?php echo $recipeId; ?>" 
                            data-is-saved="<?php echo $isSaved ? '1' : '0'; ?>">
                        <i class="fas fa-bookmark me-2"></i>
                        <span id="saveRecipeText"><?php echo $isSaved ? 'Saved' : 'Save Recipe'; ?></span>
                    </button>
                </div>
                <?php else: ?>
                <div class="alert alert-info mt-4">
                    <i class="fas fa-info-circle me-2"></i>
                    <a href="login.php" class="alert-link">Log in</a> to save this recipe to your cookbook.
                </div>
                <?php endif; ?>
                
                <!-- Download PDF Button -->
                <div class="mt-3">
                    <a href="download_recipe_pdf.php?id=<?php echo $recipeId; ?>" class="btn btn-danger w-100">
                        <i class="fas fa-file-pdf me-2"></i>Download Recipe PDF
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Ingredients and Instructions Row -->
    <div class="row mt-4">
        <!-- Ingredients Section (col-lg-4 - same size as recipe details) -->
        <div class="col-lg-4">
            <div class="ingredients-section">
                <h2 class="mb-4">
                    <i class="fas fa-shopping-basket me-2 text-warning"></i>Ingredients
                </h2>
                <?php if (empty($ingredients)): ?>
                    <p class="text-muted">No ingredients listed for this recipe.</p>
                <?php else: ?>
                    <?php foreach ($ingredients as $ingredient): ?>
                        <div class="ingredient-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-semibold"><?php echo htmlspecialchars($ingredient['ingredient_name']); ?></span>
                                <?php if (!empty($ingredient['quantity'])): ?>
                                    <span class="text-muted"><?php echo htmlspecialchars($ingredient['quantity']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Instructions Section (col-lg-8 - same size as recipe image) -->
        <div class="col-lg-8">
            <div class="instructions-section">
                <h2 class="mb-4">
                    <i class="fas fa-list-ol me-2 text-warning"></i>Instructions
                </h2>
                <?php if (empty($instructions)): ?>
                    <p class="text-muted">No instructions available for this recipe.</p>
                <?php else: ?>
                    <?php foreach ($instructions as $index => $instruction): ?>
                        <div class="instruction-step">
                            <div class="instruction-step-number"><?php echo $index + 1; ?></div>
                            <div style="margin-left: 25px;">
                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($instruction['instruction'])); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Rating and Comments Section (Full Width) -->
    <div class="row mt-4">
        <div class="col-12">
            <!-- Rating and Comments Section -->
            <div class="rating-comments-section" style="background: white; border-radius: 10px; padding: 30px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin-bottom: 30px;">
                <h2 class="mb-4">
                    <i class="fas fa-star me-2 text-warning"></i>Ratings & Reviews
                </h2>
                
                <!-- Rating Form (if logged in) -->
                <?php if ($currentUserId): ?>
                <div class="rating-form mb-4 p-4" style="background: #f8f9fa; border-radius: 8px;">
                    <h5 class="mb-3">Rate this Recipe</h5>
                    <form id="ratingForm">
                        <div class="mb-3">
                            <label class="form-label">Your Rating</label>
                            <div class="star-rating" id="starRating">
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <input type="radio" name="rating" value="<?php echo $i; ?>" id="star<?php echo $i; ?>" 
                                           <?php echo ($userRating == $i) ? 'checked' : ''; ?>>
                                    <label for="star<?php echo $i; ?>" class="star-label">
                                        <i class="fas fa-star"></i>
                                    </label>
                                <?php endfor; ?>
                            </div>
                            <small class="text-muted" id="ratingText"><?php echo $userRating > 0 ? 'You rated this recipe ' . $userRating . ' stars' : 'Click a star to rate'; ?></small>
                        </div>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-star me-2"></i><?php echo $userRating > 0 ? 'Update Rating' : 'Submit Rating'; ?>
                        </button>
                    </form>
                </div>
                <?php else: ?>
                <div class="alert alert-info mb-4">
                    <i class="fas fa-info-circle me-2"></i>
                    <a href="login.php" class="alert-link">Log in</a> to rate this recipe.
                </div>
                <?php endif; ?>
                
                <!-- Average Rating Display -->
                <?php if ($ratingCount > 0): ?>
                <div class="average-rating mb-4 p-3" style="background: #fff3cd; border-radius: 8px;">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <h3 class="mb-0"><?php echo $avgRating; ?></h3>
                            <div class="text-warning">
                                <?php 
                                $fullStars = floor($avgRating);
                                $halfStar = ($avgRating - $fullStars) >= 0.5;
                                for ($i = 0; $i < $fullStars; $i++) {
                                    echo '<i class="fas fa-star"></i>';
                                }
                                if ($halfStar) {
                                    echo '<i class="fas fa-star-half-alt"></i>';
                                }
                                for ($i = $fullStars + ($halfStar ? 1 : 0); $i < 5; $i++) {
                                    echo '<i class="far fa-star"></i>';
                                }
                                ?>
                            </div>
                        </div>
                        <div>
                            <p class="mb-0"><strong><?php echo $ratingCount; ?></strong> <?php echo $ratingCount === 1 ? 'rating' : 'ratings'; ?></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Comment Form (if logged in) -->
                <?php if ($currentUserId): ?>
                <div class="comment-form mb-4 p-4" style="background: #f8f9fa; border-radius: 8px;">
                    <h5 class="mb-3">Add a Comment</h5>
                    <form id="commentForm">
                        <div class="mb-3">
                            <textarea class="form-control" id="commentText" name="comment" rows="4" 
                                      placeholder="Share your thoughts about this recipe..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-comment me-2"></i>Post Comment
                        </button>
                    </form>
                </div>
                <?php else: ?>
                <div class="alert alert-info mb-4">
                    <i class="fas fa-info-circle me-2"></i>
                    <a href="login.php" class="alert-link">Log in</a> to add a comment.
                </div>
                <?php endif; ?>
                
                <!-- Comments List -->
                <div class="comments-list">
                    <h5 class="mb-3">
                        <i class="fas fa-comments me-2"></i>Comments (<?php echo count($comments); ?>)
                    </h5>
                    <?php if (empty($comments)): ?>
                        <p class="text-muted">No comments yet. Be the first to comment!</p>
                    <?php else: ?>
                        <?php foreach ($comments as $comment): 
                            $commentAuthorName = htmlspecialchars(trim(($comment['firstname'] ?? '') . ' ' . ($comment['lastname'] ?? '')));
                            $commentAuthorImage = $comment['user_image'] ?? '';
                            $commentText = htmlspecialchars($comment['comment']);
                            $commentDate = date('F j, Y \a\t g:i A', strtotime($comment['created_at']));
                        ?>
                        <div class="comment-item mb-4 p-3" style="background: #f8f9fa; border-radius: 8px; border-left: 4px solid var(--accent-yellow, #ffc107);">
                            <div class="d-flex align-items-start">
                                <?php if (!empty($commentAuthorImage)): 
                                    $cleanCommentImage = str_replace('\\', '/', trim($commentAuthorImage));
                                    $commentImageParts = explode('/', $cleanCommentImage);
                                    $encodedCommentParts = array_map('rawurlencode', $commentImageParts);
                                    $encodedCommentUrl = implode('/', $encodedCommentParts);
                                ?>
                                    <img src="<?php echo htmlspecialchars($encodedCommentUrl); ?>" 
                                         alt="<?php echo $commentAuthorName; ?>" 
                                         class="author-avatar me-3"
                                         style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;"
                                         onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="author-placeholder me-3" style="display: none; width: 40px; height: 40px; border-radius: 50%; background: #dee2e6; display: flex; align-items: center; justify-content: center; color: #6c757d;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                <?php else: ?>
                                    <div class="author-placeholder me-3" style="width: 40px; height: 40px; border-radius: 50%; background: #dee2e6; display: flex; align-items: center; justify-content: center; color: #6c757d;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <strong><?php echo $commentAuthorName; ?></strong>
                                            <small class="text-muted ms-2"><?php echo $commentDate; ?></small>
                                        </div>
                                    </div>
                                    <p class="mb-0"><?php echo nl2br($commentText); ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const recipeId = <?php echo $recipeId; ?>;
    
    // Save Recipe functionality
    const saveRecipeBtn = document.getElementById('saveRecipeBtn');
    if (saveRecipeBtn) {
        saveRecipeBtn.addEventListener('click', function() {
            const isSaved = this.getAttribute('data-is-saved') === '1';
            const action = isSaved ? 'unsave' : 'save';
            
            // Disable button during request
            this.disabled = true;
            const originalText = this.innerHTML;
            
            fetch('save_recipe.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `recipe_id=${recipeId}&action=${action}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update button state
                    const newIsSaved = action === 'save';
                    this.setAttribute('data-is-saved', newIsSaved ? '1' : '0');
                    this.className = newIsSaved ? 'btn btn-warning w-100' : 'btn btn-outline-warning w-100';
                    document.getElementById('saveRecipeText').textContent = newIsSaved ? 'Saved' : 'Save Recipe';
                    
                    showToast(data.message, 'success');
                } else {
                    showToast(data.message || 'Failed to save recipe', 'danger');
                }
                this.disabled = false;
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred. Please try again.', 'danger');
                this.disabled = false;
            });
        });
    }
    
    // Star rating hover effect
    const starLabels = document.querySelectorAll('.star-label');
    const ratingText = document.getElementById('ratingText');
    const starInputs = document.querySelectorAll('input[name="rating"]');
    
    starLabels.forEach((label, index) => {
        label.addEventListener('mouseenter', function() {
            const value = 5 - index;
            ratingText.textContent = value === 1 ? '1 star - Poor' : 
                                    value === 2 ? '2 stars - Fair' :
                                    value === 3 ? '3 stars - Good' :
                                    value === 4 ? '4 stars - Very Good' :
                                    '5 stars - Excellent';
        });
    });
    
    starInputs.forEach(input => {
        input.addEventListener('change', function() {
            const value = parseInt(this.value);
            ratingText.textContent = value === 1 ? 'You rated this recipe 1 star - Poor' : 
                                    value === 2 ? 'You rated this recipe 2 stars - Fair' :
                                    value === 3 ? 'You rated this recipe 3 stars - Good' :
                                    value === 4 ? 'You rated this recipe 4 stars - Very Good' :
                                    'You rated this recipe 5 stars - Excellent';
        });
    });
    
    // Rating form submission
    const ratingForm = document.getElementById('ratingForm');
    if (ratingForm) {
        ratingForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const rating = formData.get('rating');
            
            if (!rating) {
                showToast('Please select a rating', 'warning');
                return;
            }
            
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...';
            
            fetch('rate_recipe.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `recipe_id=${recipeId}&rating=${rating}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showToast(data.message || 'Failed to submit rating', 'danger');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred. Please try again.', 'danger');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }
    
    // Comment form submission
    const commentForm = document.getElementById('commentForm');
    if (commentForm) {
        commentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const comment = formData.get('comment').trim();
            
            if (!comment) {
                showToast('Please enter a comment', 'warning');
                return;
            }
            
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Posting...';
            
            fetch('comment_recipe.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `recipe_id=${recipeId}&comment=${encodeURIComponent(comment)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    this.reset();
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showToast(data.message || 'Failed to post comment', 'danger');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred. Please try again.', 'danger');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }
    
    // Toast notification function
    function showToast(message, type) {
        const toast = document.createElement('div');
        toast.className = `alert alert-${type} position-fixed top-0 start-50 translate-middle-x mt-3`;
        toast.style.zIndex = '9999';
        toast.style.minWidth = '300px';
        toast.innerHTML = '<i class="fas fa-' + (type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-circle' : 'info-circle') + ' me-2"></i>' + message;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.3s';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
});
</script>

<?php include 'footer.php'; ?>
