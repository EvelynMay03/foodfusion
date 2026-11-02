<?php
require 'auth_helper.php';
include 'header.php';
require 'pdo.php';
?>

<style>
    .resource-page-hero {
        background: linear-gradient(135deg, rgba(167, 199, 231, 0.9), rgba(173, 216, 229, 0.9));
        padding: 80px 0 60px;
        color: #0A1128;
        text-align: center;
        margin-bottom: 50px;
    }
    
    .resource-content {
        padding: 40px 0;
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
    
    .video-placeholder {
        width: 100%;
        height: 200px;
        background: linear-gradient(135deg, #2c3e50, #34495e);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        position: relative;
    }
    
    .video-play-button {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: rgba(255,255,255,0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--dark-color);
        font-size: 2rem;
        cursor: pointer;
        transition: transform 0.3s ease;
    }
    
    .video-play-button:hover {
        transform: scale(1.1);
    }
    
    .breadcrumb-custom {
        background: rgba(255,255,255,0.2);
        padding: 10px 15px;
        border-radius: 5px;
        margin-bottom: 20px;
    }
</style>

    <!-- Hero Section -->
    <section class="resource-page-hero">
        <div class="container">
            <nav aria-label="breadcrumb" class="breadcrumb-custom">
                <ol class="breadcrumb mb-0 justify-content-center">
                    <li class="breadcrumb-item"><a href="index.php" class="text-dark">Home</a></li>
                    <li class="breadcrumb-item"><a href="resources.php" class="text-dark">Resources</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Educational Videos</li>
                </ol>
            </nav>
            <h1 class="display-4 fw-bold mb-3">Educational Videos</h1>
            <p class="lead mb-0">Watch informative videos on renewable energy technologies and sustainability</p>
        </div>
    </section>

    <!-- Content Section -->
    <section class="resource-content">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="video-card">
                        <div class="video-placeholder">
                            <div class="video-play-button">
                                <i class="fas fa-play"></i>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <h5 class="card-title mb-2">Introduction to Solar Energy</h5>
                            <p class="card-text text-muted small mb-2">Duration: 15 min</p>
                            <p class="card-text text-muted small mb-3">Learn the basics of solar power and how photovoltaic systems work</p>
                            <a href="#" class="btn btn-warning w-100">
                                <i class="fas fa-play me-2"></i>Watch Video
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="video-card">
                        <div class="video-placeholder">
                            <div class="video-play-button">
                                <i class="fas fa-play"></i>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <h5 class="card-title mb-2">Wind Turbine Technology</h5>
                            <p class="card-text text-muted small mb-2">Duration: 20 min</p>
                            <p class="card-text text-muted small mb-3">Understanding how wind turbines generate electricity</p>
                            <a href="#" class="btn btn-warning w-100">
                                <i class="fas fa-play me-2"></i>Watch Video
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="video-card">
                        <div class="video-placeholder">
                            <div class="video-play-button">
                                <i class="fas fa-play"></i>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <h5 class="card-title mb-2">Hydroelectric Power Explained</h5>
                            <p class="card-text text-muted small mb-2">Duration: 18 min</p>
                            <p class="card-text text-muted small mb-3">How hydroelectric dams generate renewable energy</p>
                            <a href="#" class="btn btn-warning w-100">
                                <i class="fas fa-play me-2"></i>Watch Video
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="video-card">
                        <div class="video-placeholder">
                            <div class="video-play-button">
                                <i class="fas fa-play"></i>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <h5 class="card-title mb-2">Energy Storage Solutions</h5>
                            <p class="card-text text-muted small mb-2">Duration: 22 min</p>
                            <p class="card-text text-muted small mb-3">Battery technology and energy storage systems</p>
                            <a href="#" class="btn btn-warning w-100">
                                <i class="fas fa-play me-2"></i>Watch Video
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="video-card">
                        <div class="video-placeholder">
                            <div class="video-play-button">
                                <i class="fas fa-play"></i>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <h5 class="card-title mb-2">Sustainable Living Tips</h5>
                            <p class="card-text text-muted small mb-2">Duration: 12 min</p>
                            <p class="card-text text-muted small mb-3">Practical ways to reduce your environmental impact</p>
                            <a href="#" class="btn btn-warning w-100">
                                <i class="fas fa-play me-2"></i>Watch Video
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="video-card">
                        <div class="video-placeholder">
                            <div class="video-play-button">
                                <i class="fas fa-play"></i>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <h5 class="card-title mb-2">Future of Renewable Energy</h5>
                            <p class="card-text text-muted small mb-2">Duration: 25 min</p>
                            <p class="card-text text-muted small mb-3">Emerging technologies and trends in renewable energy</p>
                            <a href="#" class="btn btn-warning w-100">
                                <i class="fas fa-play me-2"></i>Watch Video
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php include 'footer.php'; ?>
