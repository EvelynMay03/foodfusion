<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodFusion - Discover & Share Amazing Recipes.</title>
    <!-- Google Fonts - Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>

    <!-- Header & Navigation -->
    <header class="sticky-top">
        <nav class="navbar navbar-expand-lg navbar-dark bg-success">
            <div class="container">
                <a class="navbar-brand fw-bold" href="#">
                    <i class="fas fa-utensils me-2"></i>FoodFusion
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link active" href="index.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="recipes.php">Community Recipes</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="cookbook.php">My Cookbook</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="resources.php">Culinary Resources</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="about.php">About</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="contact.php">Contact Us</a>
                        </li>
                    </ul>
                    <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
                        <!-- User is logged in -->
                        <div class="d-flex align-items-center">
                            <div class="dropdown">
                                <?php
                                $userImageUrl = null;
                                if (file_exists('image_upload_helper.php') && isset($_SESSION['user_image'])) {
                                    require 'image_upload_helper.php';
                                    $userImageUrl = getProfileImageUrl($_SESSION['user_image']);
                                }
                                ?>
                                <button class="btn btn-outline-light dropdown-toggle d-flex align-items-center" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <?php if ($userImageUrl): ?>
                                        <img src="<?php echo htmlspecialchars($userImageUrl); ?>" 
                                             alt="Profile" 
                                             class="rounded-circle me-2" 
                                             style="width: 30px; height: 30px; object-fit: cover;">
                                    <?php else: ?>
                                        <i class="fas fa-user me-2"></i>
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($_SESSION['user_firstname'] ?? 'User'); ?>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                    <li><h6 class="dropdown-header"><?php echo htmlspecialchars(($_SESSION['user_firstname'] ?? '') . ' ' . ($_SESSION['user_lastname'] ?? '')); ?></h6></li>
                                    <li><h6 class="dropdown-header text-muted small"><?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?></h6></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                                </ul>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- User is not logged in -->
                        <div class="d-flex">
                            <button class="btn btn-outline-light me-2" data-bs-toggle="modal" data-bs-target="#loginModal">Log In</button>
                            <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#registerModal">Sign Up</button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

    <!-- Login Modal -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="loginModalLabel">Login to Your Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="loginAlert" class="alert d-none" role="alert"></div>
                    <form id="loginForm" method="POST" action="login.php">
                        <div class="mb-3">
                            <label class="form-label">Email address</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="rememberMe" name="rememberMe">
                                <label class="form-check-label" for="rememberMe">Remember me</label>
                            </div>
                            <a href="#" class="text-decoration-none">Forgot password?</a>
                        </div>
                        <button type="submit" class="btn btn-warning w-100" id="loginSubmitBtn" style="background: linear-gradient(135deg, #ffc107, #ffd54f); border: none; color: #1a202c; font-weight: 600; box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3);">Login</button>
                    </form>
                    <div class="text-center mt-3">
                        <p class="mb-0">Don't have an account? <a href="#" data-bs-toggle="modal" data-bs-target="#registerModal" data-bs-dismiss="modal" class="text-decoration-none" style="color: #ffc107; font-weight: 600;">Sign up</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Register Modal -->
    <div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="registerModalLabel">Create an Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="registerAlert" class="alert d-none" role="alert"></div>
                    <form id="registerForm" method="POST" action="register.php">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">First Name</label>
                                <input type="text" name="firstname" class="form-control" maxlength="20" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="lastname" class="form-control" maxlength="20" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email address</label>
                            <input type="email" name="email" class="form-control" maxlength="150" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" id="registerPassword" class="form-control" minlength="8" 
                                   pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*(),.?&quot;:{}|&lt;&gt;]).{8,}" 
                                   title="Password must contain at least one uppercase letter, one lowercase letter, one special character, and be at least 8 characters long" required>
                            <div id="passwordRequirements" class="mt-2" style="font-size: 0.875rem;">
                                <div id="req-length" class="text-muted"><i class="fas fa-circle" style="font-size: 0.5rem;"></i> At least 8 characters</div>
                                <div id="req-uppercase" class="text-muted"><i class="fas fa-circle" style="font-size: 0.5rem;"></i> One uppercase letter</div>
                                <div id="req-lowercase" class="text-muted"><i class="fas fa-circle" style="font-size: 0.5rem;"></i> One lowercase letter</div>
                                <div id="req-special" class="text-muted"><i class="fas fa-circle" style="font-size: 0.5rem;"></i> One special character</div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="confirm_password" id="registerConfirmPassword" class="form-control" minlength="8" required>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                            <label class="form-check-label" for="terms">
                                I agree to the <a href="#" class="text-decoration-none">Terms of Service</a> and <a href="#" class="text-decoration-none">Privacy Policy</a>
                            </label>
                        </div>
                        <button type="submit" class="btn btn-warning w-100" id="registerSubmitBtn" style="background: linear-gradient(135deg, #ffc107, #ffd54f); border: none; color: #1a202c; font-weight: 600; box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3);">Create Account</button>
                    </form>
                    <div class="text-center mt-3">
                        <p class="mb-0">Already have an account? <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="modal" class="text-decoration-none" style="color: #ffc107; font-weight: 600;">Login</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
