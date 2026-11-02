<?php
    include 'header.php';
    require 'pdo.php';
    session_start();
?>
<head>
    <style>
        .about-hero {
            background: linear-gradient(135deg, rgba(167, 199, 231, 0.9), rgba(173, 216, 229, 0.9)), 
                        url('https://images.unsplash.com/photo-1504674900247-087703934569?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            padding: 100px 0;
            color: #0A1128;
            text-align: center;
        }
        
        .team-member {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .team-member img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid var(--accent-yellow);
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .mission-box {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            transition: transform 0.3s ease;
        }
        
        .mission-box:hover {
            transform: translateY(-5px);
        }
        
        .mission-icon {
            font-size: 2.5rem;
            color: var(--accent-yellow);
            margin-bottom: 20px;
        }
        
        .stats-section {
            background-color: var(--light-color);
            padding: 60px 0;
        }
        
        .stat-item {
            text-align: center;
            padding: 20px;
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            color: var(--dark-color);
            margin-bottom: 10px;
        }
        
        .stat-label {
            font-size: 1.1rem;
            color: var(--text-muted);
            font-weight: 500;
        }
    </style>
    <!-- Hero Section -->
    <section class="about-hero">
        <div class="container">
            <h1 class="display-4 fw-bold mb-4">Our Story</h1>
            <p class="lead mb-0">Bringing food lovers together, one recipe at a time.</p>
        </div>
    </section>

    <!-- Mission Section -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center mb-5">
                    <h2>Our Mission</h2>
                    <p class="lead">At FoodFusion, we believe that cooking should be fun, accessible, and rewarding for everyone. Our mission is to create a global community where food enthusiasts can share their passion, discover new flavors, and connect with like-minded individuals.</p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="mission-box">
                        <div class="mission-icon">
                            <i class="fas fa-utensils"></i>
                        </div>
                        <h4>Discover Recipes</h4>
                        <p>Explore thousands of recipes from around the world, carefully curated and tested by our community of food lovers.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mission-box">
                        <div class="mission-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h4>Join Our Community</h4>
                        <p>Connect with fellow food enthusiasts, share your culinary creations, and get inspired by others.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mission-box">
                        <div class="mission-icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <h4>Learn & Grow</h4>
                        <p>Access cooking tips, techniques, and resources to enhance your culinary skills and knowledge.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row g-4">
                <div class="col-6 col-md-3">
                    <div class="stat-item">
                        <div class="stat-number">10,000+</div>
                        <div class="stat-label">Recipes</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-item">
                        <div class="stat-number">500K+</div>
                        <div class="stat-label">Community Members</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-item">
                        <div class="stat-number">50+</div>
                        <div class="stat-label">Cuisines</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-item">
                        <div class="stat-number">24/7</div>
                        <div class="stat-label">Support</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">Meet Our Team</h2>
            <div class="row">
                <?php
                // Team members data
                $teamMembers = [
                    [
                        'name' => 'May Myint Myat',
                        'role' => 'Founder & Head Chef',
                        'image' => 'uploads/profiles/MMM.jpg'
                    ],
                    [
                        'name' => 'Htun Tint Naing',
                        'role' => 'Executive Chef',
                        'image' => 'uploads/profiles/HtunTintNaing.jpg'
                    ],
                    [
                        'name' => 'Nang Si Phwaye',
                        'role' => 'Community Manager',
                        'image' => 'uploads/profiles/Siphwaye.jpeg'
                    ]
                ];
                
                foreach ($teamMembers as $member):
                    $imagePath = $member['image'];
                    $fullPath = __DIR__ . '/' . $imagePath;
                    $imageExists = file_exists($fullPath) && is_readable($fullPath);
                    
                    // Properly encode the image URL
                    if ($imageExists) {
                        $urlParts = explode('/', $imagePath);
                        $encodedParts = array_map('rawurlencode', $urlParts);
                        $imageUrl = implode('/', $encodedParts);
                    } else {
                        $imageUrl = '';
                        error_log("Team member image not found or not readable: " . $fullPath);
                    }
                ?>
                <div class="col-md-4">
                    <div class="team-member">
                        <?php if ($imageExists && !empty($imageUrl)): ?>
                            <img src="<?php echo htmlspecialchars($imageUrl); ?>" 
                                 alt="<?php echo htmlspecialchars($member['name']); ?>" 
                                 style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 5px solid var(--accent-yellow); margin-bottom: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);"
                                 onerror="this.onerror=null; this.style.display='none'; var placeholder = this.nextElementSibling; if (placeholder) placeholder.style.display='flex';">
                            <div class="team-member-placeholder" style="display: none; width: 150px; height: 150px; border-radius: 50%; background: #f0f0f0; border: 5px solid var(--accent-yellow); margin: 0 auto 20px; align-items: center; justify-content: center; color: #999; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                                <i class="fas fa-user fa-3x"></i>
                        </div>
                        <?php else: ?>
                            <div class="team-member-placeholder" style="width: 150px; height: 150px; border-radius: 50%; background: #f0f0f0; border: 5px solid var(--accent-yellow); margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; color: #999; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                                <i class="fas fa-user fa-3x"></i>
                    </div>
                        <?php endif; ?>
                        <h4><?php echo htmlspecialchars($member['name']); ?></h4>
                        <p class="text-muted"><?php echo htmlspecialchars($member['role']); ?></p>
                        <div class="social-links">
                            <a href="#" class="text-dark me-2"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="text-dark me-2"><i class="fab fa-linkedin"></i></a>
                            <a href="#" class="text-dark"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5 bg-light">
        <div class="container text-center">
            <h2 class="mb-4">Ready to Join Our Community?</h2>
            <p class="lead mb-4">Sign up today and start exploring amazing recipes from around the world.</p>
            <a href="#" class="btn btn-success btn-lg px-5" data-bs-toggle="modal" data-bs-target="#registerModal">Get Started</a>
        </div>
    </section>

   <?php include 'footer.php'; ?>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="js/main.js"></script>
</body>
</html>
