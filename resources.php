<?php
require 'auth_helper.php';
include 'header.php';
require 'pdo.php';
?>

<style>
    .resources-hero {
        background: linear-gradient(135deg, rgba(167, 199, 231, 0.9), rgba(173, 216, 229, 0.9)), 
                    url('https://images.unsplash.com/photo-1504674900247-087703934569?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
        background-size: cover;
        background-position: center;
        padding: 100px 0;
        color: #0A1128;
        text-align: center;
        margin-bottom: 40px;
    }
    
    .resources-hero .btn {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        font-weight: 600;
    }
    
    .resources-hero .btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.2);
    }
    
    html {
        scroll-behavior: smooth;
    }
    
    .resource-section {
        padding: 60px 0;
    }
    
    .resource-section:nth-child(even) {
        background-color: #f8f9fa;
    }
    
    .resource-card {
        border: none;
        border-radius: 15px;
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        margin-bottom: 30px;
        height: 100%;
        background: white;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }
    
    .resource-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.1);
    }
    
    .resource-card-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        color: white;
        font-size: 2rem;
    }
    
    .resource-card-body {
        padding: 30px;
        text-align: center;
    }
    
    .resource-card h4 {
        color: var(--dark-color);
        margin-bottom: 15px;
        font-weight: 600;
    }
    
    .resource-card p {
        color: var(--text-muted);
        margin-bottom: 20px;
        line-height: 1.6;
    }
    
    .resource-link {
        color: var(--accent-yellow);
        text-decoration: none;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: color 0.3s ease;
    }
    
    .resource-link:hover {
        color: var(--dark-color);
    }
    
    .filter-section {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }
    
    .recipe-sidebar-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: 1px solid #e9ecef;
    }
    
    .recipe-sidebar-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .search-box {
        position: relative;
    }
    
    .search-box .form-control {
        border-radius: 25px;
        border: 2px solid #e9ecef;
        padding: 10px 20px;
    }
    
    .search-box .form-control:focus {
        border-color: var(--accent-yellow);
        box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
    }
    
    .search-box .btn {
        border-radius: 25px;
        padding: 10px 20px;
    }
    
    #recipeCardsSidebar::-webkit-scrollbar {
        width: 8px;
    }
    
    #recipeCardsSidebar::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    
    #recipeCardsSidebar::-webkit-scrollbar-thumb {
        background: #ffc107;
        border-radius: 10px;
    }
    
    #recipeCardsSidebar::-webkit-scrollbar-thumb:hover {
        background: #ffb300;
    }
    
    /* Horizontal Scrolling Recipe Cards */
    .recipe-cards-scroll-container {
        position: relative;
        width: 100%;
        overflow: hidden;
    }
    
    .recipe-cards-scroll {
        display: flex;
        gap: 20px;
        overflow-x: auto;
        overflow-y: hidden;
        padding: 10px 0 20px 0;
        scroll-behavior: smooth;
        -webkit-overflow-scrolling: touch;
    }
    
    .recipe-cards-scroll::-webkit-scrollbar {
        height: 8px;
    }
    
    .recipe-cards-scroll::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    
    .recipe-cards-scroll::-webkit-scrollbar-thumb {
        background: #ffc107;
        border-radius: 10px;
    }
    
    .recipe-cards-scroll::-webkit-scrollbar-thumb:hover {
        background: #ffb300;
    }
    
    .recipe-card-scroll-item {
        flex: 0 0 280px;
        min-width: 280px;
        max-width: 280px;
    }
    
    .recipe-card-scroll-item .card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: 1px solid #e9ecef;
        height: 100%;
    }
    
    .recipe-card-scroll-item .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }
    
    .section-title {
        text-align: center;
        margin-bottom: 50px;
    }
    
    .section-title h2 {
        color: var(--dark-color);
        font-weight: 700;
        margin-bottom: 15px;
        position: relative;
        display: inline-block;
    }
    
    .section-title h2::after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 4px;
        background: var(--accent-yellow);
        border-radius: 2px;
    }
    
    .section-title p {
        color: var(--text-muted);
        font-size: 1.1rem;
        margin-top: 20px;
    }
    
    .video-card {
        border: none;
        border-radius: 15px;
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        margin-bottom: 30px;
        background: white;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }
    
    .video-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    
    .video-thumbnail {
        width: 100%;
        height: 200px;
        position: relative;
        overflow: hidden;
        cursor: pointer;
    }
    
    .video-thumbnail img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    
    .video-thumbnail:hover img {
        transform: scale(1.05);
    }
    
    .play-overlay {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: rgba(255,255,255,0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--dark-color);
        font-size: 2rem;
        transition: transform 0.3s ease, background 0.3s ease;
    }
    
    .video-thumbnail:hover .play-overlay {
        transform: translate(-50%, -50%) scale(1.1);
        background: rgba(255,255,255,1);
    }
    
    .video-modal .modal-dialog {
        max-width: 900px;
    }
    
    .video-modal .modal-content {
        background: #000;
    }
    
    .video-modal .modal-dialog {
        max-width: 900px;
        margin: 1.75rem auto;
    }
    
    .video-modal .modal-content {
        background: #000;
        border: none;
    }
    
    .video-modal .modal-body {
        padding: 0;
        position: relative;
        padding-bottom: 56.25%; /* 16:9 aspect ratio */
        height: 0;
        overflow: hidden;
    }
    
    .video-modal iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border: none;
    }
    
    .video-modal .btn-close {
        position: absolute;
        top: -15px;
        right: -15px;
        z-index: 1055;
        background: rgba(255,255,255,0.9);
        border-radius: 50%;
        width: 40px;
        height: 40px;
        opacity: 1;
        border: 2px solid #000;
    }
    
    .video-modal .btn-close:hover {
        background: rgba(255,255,255,1);
        opacity: 1;
    }
</style>

    <!-- Hero Section -->
    <section class="resources-hero">
        <div class="container">
            <h1 class="display-4 fw-bold mb-4">Culinary & Educational Resources</h1>
            <p class="lead mb-4">Enhance your cooking skills and expand your culinary knowledge</p>
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <a href="#educational-resources" class="btn btn-light btn-lg px-5">
                    <i class="fas fa-graduation-cap me-2"></i>Educational Resources
                </a>
                <a href="#culinary-resources" class="btn btn-warning btn-lg px-5">
                    <i class="fas fa-utensils me-2"></i>Culinary Resources
                </a>
            </div>
        </div>
    </section>

    <!-- Educational Resources Section -->
    <section id="educational-resources" class="resource-section">
        <div class="container">
            <div class="section-title">
                <h2>Educational Resources</h2>
                <p>Providing videos on renewable energy topics</p>
            </div>
            
            <div class="row g-4">
                <!-- Educational Videos - Displayed directly -->
                <div class="col-md-6 col-lg-4">
                    <div class="video-card">
                        <div class="video-thumbnail" style="cursor: pointer;" onclick="openVideoModal('L_q6LRgKpTw')">
                            <?php
                            $videoId = "L_q6LRgKpTw";
                            echo "<img src='https://img.youtube.com/vi/$videoId/maxresdefault.jpg' alt='Video Thumbnail' onerror=\"this.src='https://img.youtube.com/vi/$videoId/hqdefault.jpg'\">";
                            ?>
                            <div class="play-overlay">
                                <i class="fas fa-play"></i>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <h5 class="card-title mb-2">How do Solar cells work?</h5>
                            <p class="card-text text-muted small mb-2">Duration: 7 min 3 sec</p>
                            <p class="card-text text-muted small mb-3">Learn the basics of solar power and how photovoltaic systems work</p>
                            <button class="btn btn-warning w-100" onclick="openVideoModal('L_q6LRgKpTw')">
                                <i class="fas fa-play me-2"></i>Watch Video
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="video-card">
                        <div class="video-thumbnail" style="cursor: pointer;" onclick="openVideoModal('Hf875eOVrVI')">
                            <?php
                            $videoId = "Hf875eOVrVI";
                            echo "<img src='https://img.youtube.com/vi/$videoId/maxresdefault.jpg' alt='Video Thumbnail' onerror=\"this.src='https://img.youtube.com/vi/$videoId/hqdefault.jpg'\">";
                            ?>
                            <div class="play-overlay">
                                <i class="fas fa-play"></i>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <h5 class="card-title mb-2">Wind Turbine Technology</h5>
                            <p class="card-text text-muted small mb-2">Duration: 22 min 2 sec</p>
                            <p class="card-text text-muted small mb-3">Understanding how wind turbines generate electricity</p>
                            <button class="btn btn-warning w-100" onclick="openVideoModal('Hf875eOVrVI')">
                                <i class="fas fa-play me-2"></i>Watch Video
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="video-card">
                        <div class="video-thumbnail" style="cursor: pointer;" onclick="openVideoModal('H_cm_Lvml70')">
                            <?php
                            $videoId = "H_cm_Lvml70";
                            echo "<img src='https://img.youtube.com/vi/$videoId/maxresdefault.jpg' alt='Video Thumbnail' onerror=\"this.src='https://img.youtube.com/vi/$videoId/hqdefault.jpg'\">";
                            ?>
                            <div class="play-overlay">
                                <i class="fas fa-play"></i>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <h5 class="card-title mb-2">Hydroelectric Power Explained</h5>
                            <p class="card-text text-muted small mb-2">Duration: 9 min 34 sec</p>
                            <p class="card-text text-muted small mb-3">How hydroelectric dams generate renewable energy</p>
                            <button class="btn btn-warning w-100" onclick="openVideoModal('H_cm_Lvml70')">
                                <i class="fas fa-play me-2"></i>Watch Video
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="video-card">
                        <div class="video-thumbnail" style="cursor: pointer;" onclick="openVideoModal('zXV1Vy_XECY')">
                            <?php
                            $videoId = "zXV1Vy_XECY";
                            echo "<img src='https://img.youtube.com/vi/$videoId/maxresdefault.jpg' alt='Video Thumbnail' onerror=\"this.src='https://img.youtube.com/vi/$videoId/hqdefault.jpg'\">";
                            ?>
                            <div class="play-overlay">

                            <i class="fas fa-play"></i>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <h5 class="card-title mb-2">Energy Storage Solutions</h5>
                            <p class="card-text text-muted small mb-2">Duration: 6 min 49 sec</p>
                            <p class="card-text text-muted small mb-3">Battery technology and energy storage systems</p>
                            <button class="btn btn-warning w-100" onclick="openVideoModal('zXV1Vy_XECY')">
                                <i class="fas fa-play me-2"></i>Watch Video
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="video-card">
                        <div class="video-thumbnail" style="cursor: pointer;" onclick="openVideoModal('U2EXdn2iVK0')">
                            <?php
                            $videoId = "U2EXdn2iVK0";
                            echo "<img src='https://img.youtube.com/vi/$videoId/maxresdefault.jpg' alt='Video Thumbnail' onerror=\"this.src='https://img.youtube.com/vi/$videoId/hqdefault.jpg'\">";
                            ?>
                            <div class="play-overlay">
                                <i class="fas fa-play"></i>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <h5 class="card-title mb-2">Sustainable Living Tips</h5>
                            <p class="card-text text-muted small mb-2">Duration: 6 min 14 sec</p>
                            <p class="card-text text-muted small mb-3">Practical ways to reduce your environmental impact</p>
                            <button class="btn btn-warning w-100" onclick="openVideoModal('U2EXdn2iVK0')">
                                <i class="fas fa-play me-2"></i>Watch Video
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="video-card">
                        <div class="video-thumbnail" style="cursor: pointer;" onclick="openVideoModal('gLvkWpnzba8')">
                            <?php
                            $videoId = "gLvkWpnzba8";
                            echo "<img src='https://img.youtube.com/vi/$videoId/maxresdefault.jpg' alt='Video Thumbnail' onerror=\"this.src='https://img.youtube.com/vi/$videoId/hqdefault.jpg'\">";
                            ?>
                            <div class="play-overlay">
                                <i class="fas fa-play"></i>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <h5 class="card-title mb-2">Future of Renewable Energy</h5>
                            <p class="card-text text-muted small mb-2">Duration: 24 min 28 sec</p>
                            <p class="card-text text-muted small mb-3">Emerging technologies and trends in renewable energy</p>
                            <button class="btn btn-warning w-100" onclick="openVideoModal('gLvkWpnzba8')">
                                <i class="fas fa-play me-2"></i>Watch Video
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Culinary Resources Section -->
    <section id="culinary-resources" class="resource-section">
        <div class="container">
            <div class="section-title">
                <h2>Culinary Resources</h2>
                <p>Providing downloadable recipe cards of all recipes, cooking tutorial videos, and instructional videos on various cooking techniques and kitchen hacks</p>
            </div>
            
            <!-- Downloadable Recipe Cards - Horizontal Scroll -->
            <div class="mb-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="mb-0">Downloadable Recipe Cards</h3>
                    <div class="search-box" style="max-width: 350px; width: 100%;">
                        <form class="d-flex" id="recipeSearchForm">
                            <input type="text" class="form-control" id="recipeSearchInput" placeholder="Search recipes...">
                            <button class="btn btn-warning ms-2" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>
                </div>
                <div class="recipe-cards-scroll-container">
                    <div class="recipe-cards-scroll" id="recipeCardsScroll">
                        <?php
                        try {
                            // Fetch recipes from database for downloadable cards
                            $recipesStmt = $pdo->query("
                                SELECT r.recipe_id, r.title, r.description, r.cook_time, r.difficulty, r.image_url,
                                       c.name as category_name
                                FROM recipes r
                                LEFT JOIN categories c ON r.category_id = c.category_id
                                ORDER BY r.created_at DESC
                                LIMIT 20
                            ");
                            $recipes = $recipesStmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            if (empty($recipes)) {
                                echo '<div class="col-12"><p class="text-center text-muted">No recipes available yet.</p></div>';
                            } else {
                                foreach ($recipes as $recipe) {
                                    $recipeId = htmlspecialchars($recipe['recipe_id']);
                                    $title = htmlspecialchars($recipe['title']);
                                    $description = htmlspecialchars($recipe['description']);
                                    $cookTime = htmlspecialchars($recipe['cook_time']);
                                    $difficulty = htmlspecialchars($recipe['difficulty']);
                                    $categoryName = htmlspecialchars($recipe['category_name'] ?? 'Uncategorized');
                                    $imageUrl = $recipe['image_url'];
                                    
                                    echo '<div class="recipe-card-scroll-item" data-title="' . strtolower($title) . '" data-description="' . strtolower($description) . '">';
                                    echo '<div class="card h-100">';
                                    
                                    // Recipe image or placeholder
                                    if (!empty($imageUrl)) {
                                        $cleanImageUrl = str_replace('\\', '/', trim($imageUrl));
                                        $urlParts = explode('/', $cleanImageUrl);
                                        $encodedParts = array_map('rawurlencode', $urlParts);
                                        $encodedUrl = implode('/', $encodedParts);
                                        echo '<img src="' . htmlspecialchars($encodedUrl) . '" alt="' . $title . '" class="card-img-top" style="height: 180px; object-fit: cover;" ';
                                        echo 'onerror="this.onerror=null; this.style.display=\'none\'; this.nextElementSibling.style.display=\'flex\';">';
                                      
                                    } 
                                    else {
                                        echo '<div class="bg-light d-flex align-items-center justify-content-center" style="height: 180px;">';
                                        echo '<i class="fas fa-image fa-3x text-muted"></i>';
                                        echo '</div>';
                                    }
                                    
                                    echo '<div class="card-body p-3">';
                                    echo '<div class="d-flex align-items-center mb-2">';
                                    echo '<i class="fas fa-file-pdf text-warning me-2"></i>';
                                    echo '<h6 class="card-title mb-0" style="font-size: 0.95rem; flex: 1;">' . $title . '</h6>';
                                    echo '</div>';
                                    echo '<p class="card-text small text-muted mb-2"><i class="far fa-clock me-1"></i>' . $cookTime . ' mins | <span class="badge bg-warning text-dark">' . $difficulty . '</span></p>';
                                    echo '<p class="card-text small mb-3" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">' . mb_substr($description, 0, 100) . (mb_strlen($description) > 100 ? '...' : '') . '</p>';
                                    echo '<a href="recipe_detail.php?id=' . $recipeId . '" class="btn btn-sm btn-outline-warning w-100">';
                                    echo '<i class="fas fa-eye me-1"></i>View Recipe';
                                    echo '</a>';
                                    echo '</div>';
                                    echo '</div>';
                                    echo '</div>';
                                }
                            }
                        } catch (PDOException $e) {
                            error_log("Error loading recipes: " . $e->getMessage());
                            echo '<div class="col-12"><p class="text-center text-danger">Error loading recipes. Please try again later.</p></div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
            
            <!-- Cooking Techniques Videos -->
            <div class="mb-5">
                <h3 class="mb-4 text-center">Cooking Techniques Videos</h3>
                <div class="row g-4">
                    <div class="col-md-6 col-lg-4">
                        <div class="video-card">
                            <div class="video-thumbnail" style="cursor: pointer;" onclick="openVideoModal('ZJy1ajvMU1k')">
                                <?php
                                $videoId = "ZJy1ajvMU1k";
                                echo "<img src='https://img.youtube.com/vi/$videoId/maxresdefault.jpg' alt='Video Thumbnail' onerror=\"this.src='https://img.youtube.com/vi/$videoId/hqdefault.jpg'\">";
                                ?>
                                <div class="play-overlay">
                                    <i class="fas fa-play"></i>
                                </div>
                            </div>
                            <div class="card-body p-4">
                                <h5 class="card-title mb-2">5 Basic Cooking Skills</h5>
                                <p class="card-text text-muted small mb-2">Duration: 7 min 39 sec</p>
                                <p class="card-text text-muted small mb-3">Learn fundamental cooking techniques every chef should know</p>
                                <button class="btn btn-warning w-100" onclick="openVideoModal('ZJy1ajvMU1k')">
                                    <i class="fas fa-play me-2"></i>Watch Video
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 col-lg-4">
                        <div class="video-card">
                            <div class="video-thumbnail" style="cursor: pointer;" onclick="openVideoModal('YrHpeEwk_-U')">
                                <?php
                                $videoId = "YrHpeEwk_-U";
                                echo "<img src='https://img.youtube.com/vi/$videoId/maxresdefault.jpg' alt='Video Thumbnail' onerror=\"this.src='https://img.youtube.com/vi/$videoId/hqdefault.jpg'\">";
                                ?>
                                <div class="play-overlay">
                                    <i class="fas fa-play"></i>
                                </div>
                            </div>
                            <div class="card-body p-4">
                                <h5 class="card-title mb-2">Knife Skills Masterclass</h5>
                                <p class="card-text text-muted small mb-2">Duration: 20 min</p>
                                <p class="card-text text-muted small mb-3">Master essential knife skills for efficient and safe cooking</p>
                                <button class="btn btn-warning w-100" onclick="openVideoModal('YrHpeEwk_-U')">
                                    <i class="fas fa-play me-2"></i>Watch Video
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 col-lg-4">
                        <div class="video-card">
                            <div class="video-thumbnail" style="cursor: pointer;" onclick="openVideoModal('BHcyuzXRqLs')">
                                <?php
                                $videoId = "BHcyuzXRqLs";
                                echo "<img src='https://img.youtube.com/vi/$videoId/maxresdefault.jpg' alt='Video Thumbnail' onerror=\"this.src='https://img.youtube.com/vi/$videoId/hqdefault.jpg'\">";
                                ?>
                                <div class="play-overlay">
                                    <i class="fas fa-play"></i>
                                </div>
                            </div>
                            <div class="card-body p-4">
                                <h5 class="card-title mb-2">50 Cooking Tips</h5>
                                <p class="card-text text-muted small mb-2">Duration:20 min 18 sec</p>
                                <p class="card-text text-muted small mb-3"> Cooking tips for beginners and advanced chefs alike</p>
                                <button class="btn btn-warning w-100" onclick="openVideoModal('BHcyuzXRqLs')">
                                    <i class="fas fa-play me-2"></i>Watch Video
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Cooking Tutorials Videos -->
            <div>
                <h3 class="mb-4 text-center">Cooking Tutorials Videos</h3>
                <div class="row g-4">
                    <div class="col-md-6 col-lg-4">
                        <div class="video-card">
                            <div class="video-thumbnail" style="cursor: pointer;" onclick="openVideoModal('xniS7kMpW4I')">
                                <?php
                                $videoId = "xniS7kMpW4I";
                                echo "<img src='https://img.youtube.com/vi/$videoId/maxresdefault.jpg' alt='Video Thumbnail' onerror=\"this.src='https://img.youtube.com/vi/$videoId/hqdefault.jpg'\">";
                                ?>
                                <div class="play-overlay">
                                    <i class="fas fa-play"></i>
                                </div>
                            </div>
                            <div class="card-body p-4">
                                <h5 class="card-title mb-2">5 Sauces Needs to Learn</h5>
                                <p class="card-text text-muted small mb-2">Duration: 19 min 54 sec</p>
                                <p class="card-text text-muted small mb-3">Learn to make 5 sauces that will take your cooking to the next level</p>
                                <button class="btn btn-warning w-100" onclick="openVideoModal('xniS7kMpW4I')">
                                    <i class="fas fa-play me-2"></i>Watch Video
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 col-lg-4">
                        <div class="video-card">
                            <div class="video-thumbnail" style="cursor: pointer;" onclick="openVideoModal('b52h7kraC3A')">
                                <?php
                                $videoId = "b52h7kraC3A";
                                echo "<img src='https://img.youtube.com/vi/$videoId/maxresdefault.jpg' alt='Video Thumbnail' onerror=\"this.src='https://img.youtube.com/vi/$videoId/hqdefault.jpg'\">";
                                ?>
                                <div class="play-overlay">
                                    <i class="fas fa-play"></i>
                                </div>
                            </div>
                            <div class="card-body p-4">
                                <h5 class="card-title mb-2">Every ways to cook Eggs</h5>
                                <p class="card-text text-muted small mb-2">Duration: 11 min 12 sec</p>
                                <p class="card-text text-muted small mb-3">This is the only guide to eggs you will ever need!</p>
                                <button class="btn btn-warning w-100" onclick="openVideoModal('b52h7kraC3A')">
                                    <i class="fas fa-play me-2"></i>Watch Video
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 col-lg-4">
                        <div class="video-card">
                            <div class="video-thumbnail" style="cursor: pointer;" onclick="openVideoModal('Ew-3-8itpjc')">
                                <?php
                                $videoId = "Ew-3-8itpjc";
                                echo "<img src='https://img.youtube.com/vi/$videoId/maxresdefault.jpg' alt='Video Thumbnail' onerror=\"this.src='https://img.youtube.com/vi/$videoId/hqdefault.jpg'\">";
                                ?>
                                <div class="play-overlay">
                                    <i class="fas fa-play"></i>
                                </div>
                            </div>
                            <div class="card-body p-4">
                                <h5 class="card-title mb-2">Handmade Pasta Shapes</h5>
                                <p class="card-text text-muted small mb-2">Duration: 26 min 43 sec</p>
                                <p class="card-text text-muted small mb-3">Learn to make fresh pasta from scratch</p>
                                <button class="btn btn-warning w-100" onclick="openVideoModal('Ew-3-8itpjc')">
                                    <i class="fas fa-play me-2"></i>Watch Video
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Video Modal -->
    <div class="modal fade video-modal" id="videoModal" tabindex="-1" aria-labelledby="videoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="ratio ratio-16x9">
                        <iframe id="videoFrame" src="" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Make function globally available
function openVideoModal(videoId) {
    const modalElement = document.getElementById('videoModal');
    const iframe = document.getElementById('videoFrame');
    
    if (!modalElement || !iframe) {
        console.error('Modal elements not found');
        return;
    }
    
    // Set the video source
    iframe.src = 'https://www.youtube.com/embed/' + videoId + '?autoplay=1&rel=0&modestbranding=1';
    
    // Show modal using Bootstrap
    const modal = new bootstrap.Modal(modalElement, {
        backdrop: true,
        keyboard: true
    });
    modal.show();
}

// Clear video when modal is closed
document.addEventListener('DOMContentLoaded', function() {
    const videoModal = document.getElementById('videoModal');
    if (videoModal) {
        videoModal.addEventListener('hidden.bs.modal', function () {
            const iframe = document.getElementById('videoFrame');
            if (iframe) {
                iframe.src = '';
            }
        });
    }
    
    // Recipe search functionality
    const searchInput = document.getElementById('recipeSearchInput');
    const searchForm = document.getElementById('recipeSearchForm');
    const recipeCards = document.querySelectorAll('.recipe-card-scroll-item');
    
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            filterRecipeCards();
        });
    }
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            filterRecipeCards();
        });
    }
    
    function filterRecipeCards() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        
        recipeCards.forEach(function(card) {
            const title = card.getAttribute('data-title') || '';
            const description = card.getAttribute('data-description') || '';
            
            if (title.includes(searchTerm) || description.includes(searchTerm)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }
});
</script>

<?php include 'footer.php'; ?>
