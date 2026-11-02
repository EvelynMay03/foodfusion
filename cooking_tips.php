<?php
require 'auth_helper.php';
requireLogin();
include 'header.php';
require 'pdo.php';

// Handle form submission for new cooking tip
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_tip'])) {
    $tipText = trim($_POST['tip_text'] ?? '');
    $userId = getUserId();
    
    if (empty($tipText)) {
        $errorMessage = 'Please enter a cooking tip.';
    } else {
        try {
            // Check if tips table exists
            $checkTable = $pdo->query("SHOW TABLES LIKE 'tips'");
            if ($checkTable->rowCount() === 0) {
                $errorMessage = 'Tips table does not exist. Please create it first.';
            } else {
                // Insert new tip into tips table
                $stmt = $pdo->prepare("INSERT INTO tips (tips, user_id) VALUES (?, ?)");
                $stmt->execute([$tipText, $userId]);
                
                $successMessage = 'Your cooking tip has been shared successfully!';
            }
        } catch (PDOException $e) {
            error_log("Error adding cooking tip: " . $e->getMessage());
            $errorMessage = 'An error occurred. Please try again.';
        }
    }
}

// Get current user ID
$currentUserId = getUserId();
?>
<head>
    <style>
        .cooking-tips-hero {
            background: linear-gradient(135deg, rgba(167, 199, 231, 0.9), rgba(173, 216, 229, 0.9));
            padding: 100px 0;
            text-align: center;
            margin-bottom: 40px;
        }
        
        .tips-container {
            display: block;
            width: 100%;
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        .tip-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background: white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            width: 100% !important;
            max-width: 100% !important;
            min-width: 100% !important;
            margin: 0 0 15px 0 !important;
            padding: 0 !important;
            box-sizing: border-box !important;
            display: flex;
            flex-direction: column;
            clear: both;
        }
        
        .tip-card:last-child {
            margin-bottom: 0 !important;
        }
        
        .tip-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        
        .tip-header {
            background: linear-gradient(135deg, #ffc107, #ffd54f);
            padding: 20px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            box-sizing: border-box;
        }
        
        .tip-content {
            padding: 20px 30px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            width: 100%;
            box-sizing: border-box;
        }
        
        .tip-text {
            font-size: 1rem;
            line-height: 1;
            color: #333;
            margin-bottom: 10px;
            flex-grow: 1;
            white-space: pre-wrap;
            word-wrap: break-word;
            width: 100%;
            box-sizing: border-box;
            overflow-wrap: break-word;
        }
        
        .tip-footer {
            padding: 20px 30px;
            background: #f8f9fa;
            border-top: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            box-sizing: border-box;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #fff;
        }
        
        .add-tip-form {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 40px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        /* Override Bootstrap defaults for consistent width */
        .row {
            margin-left: 0 !important;
            margin-right: 0 !important;
        }
        
        .row .col-12 {
            padding-left: 15px !important;
            padding-right: 15px !important;
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
        }
        
        /* Ensure tips container takes full width of parent */
        #tipsContainer {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            box-sizing: border-box !important;
            display: block !important;
        }
        
        /* Force all tip cards to be exactly the same width */
        #tipsContainer > .tip-card,
        #tipsContainer .tip-card {
            width: 100% !important;
            max-width: 100% !important;
            min-width: 100% !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
        }
        
        /* Reset any potential flex or grid that might affect width */
        #tipsContainer * {
            max-width: 100%;
        }
    </style>
</head>

<!-- Hero Section -->
<section class="cooking-tips-hero">
    <div class="container">
        <h1 class="display-4 fw-bold mb-4">Cooking Tips</h1>
        <p class="lead">Share your cooking wisdom and learn from our amazing community of food lovers!</p>
    </div>
</section>

<!-- Main Content -->
<div class="container">
    <!-- Success/Error Messages -->
    <?php if (isset($successMessage)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($successMessage); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($errorMessage)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($errorMessage); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <!-- Add New Tip Form -->
    <div class="add-tip-form">
        <h3 class="mb-4"><i class="fas fa-lightbulb text-warning me-2"></i>Share Your Cooking Tip</h3>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="tip_text" class="form-label">Your Cooking Tip *</label>
                <textarea class="form-control" id="tip_text" name="tip_text" rows="4" 
                          placeholder="Share a helpful cooking tip, technique, or advice..." required></textarea>
                <small class="form-text text-muted">Share your knowledge with the community!</small>
            </div>
            <button type="submit" name="add_tip" class="btn btn-warning btn-lg">
                <i class="fas fa-share me-2"></i>Share Tip
            </button>
        </form>
    </div>
    
    <!-- Community Tips -->
    <div class="row">
        <div class="col-12">
            <h3 class="mb-4"><i class="fas fa-users me-2"></i>Community Cooking Tips</h3>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12" style="padding-left: 15px; padding-right: 15px; box-sizing: border-box;">
            <div class="tips-container" id="tipsContainer" style="width: 100%; box-sizing: border-box;">
        <?php
        try {
            // Check if tips table exists
            $checkTable = $pdo->query("SHOW TABLES LIKE 'tips'");
            if ($checkTable->rowCount() === 0) {
                echo '<div class="tip-card" style="width: 100%;"><div class="alert alert-info text-center m-3"><i class="fas fa-info-circle me-2"></i>No cooking tips yet. Be the first to share a tip!</div></div>';
            } else {
                // Fetch all cooking tips with user information
                // Use LEFT JOIN since user_id can be NULL (ON DELETE SET NULL)
                $tipsStmt = $pdo->prepare("
                    SELECT t.id, t.tips, t.user_id,
                           u.user_id as u_user_id, u.firstname, u.lastname, u.image as user_image
                    FROM tips t
                    LEFT JOIN users u ON t.user_id = u.user_id
                    ORDER BY t.id DESC
                ");
                $tipsStmt->execute();
                $tips = $tipsStmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (empty($tips)) {
                    echo '<div class="tip-card" style="width: 100%;"><div class="alert alert-info text-center m-3"><i class="fas fa-info-circle me-2"></i>No cooking tips yet. Be the first to share a tip!</div></div>';
                } else {
                    foreach ($tips as $tip) {
                        $tipId = htmlspecialchars($tip['id']);
                        $tipText = htmlspecialchars($tip['tips']);
                        $userId = $tip['user_id'];
                        $firstName = htmlspecialchars($tip['firstname'] ?? '');
                        $lastName = htmlspecialchars($tip['lastname'] ?? '');
                        $userName = trim($firstName . ' ' . $lastName);
                        if (empty($userName)) {
                            $userName = 'Anonymous';
                        }
                        $userImage = $tip['user_image'] ?? '';
                        
                        // Since there's no created_at column, we'll just show "Shared" without time
                        $timeAgo = 'Shared';
                        
                        // Get user profile image URL
                        $avatarUrl = null;
                        if (!empty($userImage) && file_exists('image_upload_helper.php')) {
                            require_once 'image_upload_helper.php';
                            $avatarUrl = getProfileImageUrl($userImage);
                        }
                        
                        echo '<div class="tip-card">';
                        echo '<div class="tip-header">';
                        echo '<i class="fas fa-lightbulb fa-2x text-white"></i>';
                        echo '<span class="badge bg-white text-dark">Tip #' . $tipId . '</span>';
                        echo '</div>';
                        echo '<div class="tip-content">';
                        echo '<p class="tip-text">' . nl2br($tipText) . '</p>';
                        echo '</div>';
                        echo '<div class="tip-footer">';
                        echo '<div class="user-info">';
                        
                        // User avatar
                        
                        if (!empty($avatarUrl)) {
                            echo '<img src="' . htmlspecialchars($avatarUrl) . '" alt="' . $userName . '" class="user-avatar" ';
                            echo 'onerror="this.onerror=null; this.style.display=\'none\'; this.nextElementSibling.style.display=\'flex\';">';
                            echo '<div class="user-avatar d-none align-items-center justify-content-center" style="background: #e2e8f0; border: 2px solid #fff;">';
                            echo '<i class="fas fa-user text-muted"></i>';
                            echo '</div>';
                        } else {
                            echo '<div class="user-avatar d-flex align-items-center justify-content-center" style="background: #e2e8f0; border: 2px solid #fff;">';
                            echo '<i class="fas fa-user text-muted"></i>';
                            echo '</div>';
                        }
                        
                        echo '<div>';
                        echo '<div style="font-weight: 600; color: #333;">';
                        if ($userId && $tip['u_user_id']) {
                            echo '<a href="profile.php?id=' . htmlspecialchars($userId) . '" class="text-decoration-none" style="color: #333;">' . $userName . '</a>';
                        } else {
                            echo '<span style="color: #333;">' . $userName . '</span>';
                        }
                        echo '</div>';
                        echo '<small class="text-muted">' . $timeAgo . '</small>';
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                    }
                }
            }
        } catch (PDOException $e) {
            error_log("Error loading cooking tips: " . $e->getMessage());
            echo '<div class="tip-card" style="width: 100%;"><div class="alert alert-danger m-3">Error loading cooking tips. Please try again later.</div></div>';
        }
        ?>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
