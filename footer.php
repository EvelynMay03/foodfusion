<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodFusion - Discover & Share Amazing Recipes</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>
<!-- Footer -->
    <footer class="bg-dark text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5>FoodFusion</h5>
                    <p>Discover, create, and share amazing recipes with our global community of food lovers.</p>
                    <div class="social-links">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-pinterest"></i></a>
                    </div>
                </div>
                <div class="col-md-2 col-6 mb-4 mb-md-0">
                    <h5>Explore</h5>
                    <ul class="list-unstyled">
                        <li><a href="recipes.php" class="text-white-50 text-decoration-none">Recipes</a></li>
                        <li><a href="cookbook.php" class="text-white-50 text-decoration-none">Categories</a></li>
                        <li><a href="about.php" class="text-white-50 text-decoration-none">Community</a></li>
                        <li><a href="resources.php" class="text-white-50 text-decoration-none">Blog</a></li>
                    </ul>
                </div>
                <div class="col-md-2 col-6 mb-4 mb-md-0">
                    <h5>Company</h5>
                    <ul class="list-unstyled">
                        <li><a href="about.php" class="text-white-50 text-decoration-none">About Us</a></li>
                        <li><a href="contact.php" class="text-white-50 text-decoration-none">Contact</a></li>
                        <li><a href="#" class="text-white-50 text-decoration-none">Careers</a></li>
                        <li><a href="#" class="text-white-50 text-decoration-none">Press</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Newsletter</h5>
                    <p>Subscribe to get the latest recipes and cooking tips.</p>
                    <div id="subscribeAlert" class="alert d-none mb-3" role="alert"></div>
                    <form id="subscribeForm">
                        <div class="input-group mb-3">
                            <input type="email" id="subscribeEmail" name="email" class="form-control" placeholder="Your email" required>
                            <button class="btn btn-success" type="submit" id="subscribeBtn">Subscribe</button>
                        </div>
                    </form>
                </div>
            </div>
            <hr class="my-4">
            <div class="row">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0">&copy; 2025 FoodFusion. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <a href="privacy_policy.php" class="text-white-50 text-decoration-none me-3">Privacy Policy</a>
                    <a href="#" class="text-white-50 text-decoration-none me-3">Terms of Service</a>
                    <a href="cookie_policy.php" class="text-white-50 text-decoration-none me-3">Cookie Policy</a>
                </div>
            </div>
        </div>
    </footer>
