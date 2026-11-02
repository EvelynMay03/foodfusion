<?php
  require 'auth_helper.php';
  include 'header.php';
  require 'pdo.php';
  
  // Get current user ID if logged in
  $currentUserId = isLoggedIn() ? getUserId() : null;
  ?>
<head>

    <style>
        .contact-hero {
            background: linear-gradient(135deg, rgba(167, 199, 231, 0.9), rgba(173, 216, 229, 0.9)), 
                        url('https://images.unsplash.com/photo-1504674900247-087703934569?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            padding: 80px 0;
            color: #0A1128;
            text-align: center;
        }
        
        .contact-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.05);
            height: 100%;
            border-top: 4px solid var(--accent-yellow);
            transition: transform 0.3s ease;
        }
        
        .contact-card:hover {
            transform: translateY(-5px);
        }
        
        .contact-icon {
            font-size: 2.5rem;
            color: var(--accent-yellow);
            margin-bottom: 20px;
        }
        
        .form-control, .form-select {
            padding: 12px 15px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--accent-yellow);
            box-shadow: 0 0 0 0.25rem rgba(255, 211, 1, 0.25);
        }
        
        .btn-send {
            background-color: var(--accent-yellow);
            color: var(--dark-color);
            font-weight: 600;
            padding: 12px 30px;
            border-radius: 8px;
            border: none;
            transition: all 0.3s ease;
        }
        
        .btn-send:hover {
            background-color: #e6b800;
            transform: translateY(-2px);
        }
        
        .map-container {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
            height: 100%;
            min-height: 300px;
        }
        
        .faq-item {
            margin-bottom: 15px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .faq-question {
            background: white;
            padding: 15px 20px;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .faq-answer {
            background: #f9f9f9;
            padding: 20px;
            display: none;
        }
        
        .faq-item.active .faq-answer {
            display: block;
        }
    </style>
    <!-- Hero Section -->
    <section class="contact-hero">
        <div class="container">
            <h1 class="display-4 fw-bold mb-4">Get In Touch</h1>
            <p class="lead mb-0">We're here to help and answer any questions you might have</p>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="py-5">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-6">
                    <div class="contact-card">
                        <h2 class="mb-4">Send Us a Message</h2>
                        <div id="contactAlert" class="alert d-none" role="alert"></div>
                        <form id="contactForm" method="POST" action="process_contact.php">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input type="text" class="form-control" id="name" name="name" placeholder="Your Name" 
                                               value="<?php echo $currentUserId ? htmlspecialchars($_SESSION['user_firstname'] ?? '') . ' ' . htmlspecialchars($_SESSION['user_lastname'] ?? '') : ''; ?>" 
                                               required maxlength="50">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input type="email" class="form-control" id="email" name="email" placeholder="Your Email" 
                                               value="<?php echo $currentUserId ? htmlspecialchars($_SESSION['user_email'] ?? '') : ''; ?>" 
                                               required maxlength="100">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <input type="text" class="form-control" id="subject" name="subject" placeholder="Subject" required maxlength="255">
                            </div>
                            <div class="form-group">
                                <textarea class="form-control" id="message" name="message" rows="5" placeholder="Your Message" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-send" id="submitBtn">Send Message</button>
                        </form>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="contact-card h-100">
                        <h2 class="mb-4">Contact Information</h2>
                        <div class="d-flex mb-4">
                            <div class="me-4">
                                <i class="fas fa-map-marker-alt contact-icon"></i>
                            </div>
                            <div>
                                <h5>Our Location</h5>
                                <p class="text-muted">123 Foodie Street, Culinary District<br>Mandalay, Myanmar</p>
                            </div>
                        </div>
                        <div class="d-flex mb-4">
                            <div class="me-4">
                                <i class="fas fa-envelope contact-icon"></i>
                            </div>
                            <div>
                                <h5>Email Us</h5>
                                <p class="text-muted mb-1">info@foodfusion.com</p>
                                <p class="text-muted">support@foodfusion.com</p>
                            </div>
                        </div>
                        <div class="d-flex mb-4">
                            <div class="me-4">
                                <i class="fas fa-phone-alt contact-icon"></i>
                            </div>
                            <div>
                                <h5>Call Us</h5>
                                <p class="text-muted mb-1">+95 9 123 456 789</p>
                                <p class="text-muted">+95 9 987 654 321</p>
                            </div>
                        </div>
                       
                    </div>
                </div>
            </div>
            
            <div class="row mt-5">
                <div class="col-12">
                    <div class="map-container">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3819.669853464012!2d96.15636831543016!3d16.79859598843204!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x30c1ecb8b2a8b3b5%3A0x7f1b1b1b1b1b1b1b1!2sYangon%2C%20Myanmar!5e0!3m2!1sen!2smm!4v1620000000000!5m2!1sen!2smm" allowfullscreen="" loading="lazy"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5">Frequently Asked Questions</h2>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="faq-item">
                        <div class="faq-question">
                            How can I submit my own recipe?
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            You can submit your recipe by creating an account and clicking on the "Submit Recipe" button in your dashboard. Our team will review your submission before it's published on the platform.
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            How do I reset my password?
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            Click on the "Forgot Password" link on the login page and enter your email address. You'll receive an email with instructions to reset your password.
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            Are the recipes on FoodFusion free to access?
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            Yes, all recipes on FoodFusion are completely free to access. You can browse, save, and try any recipe without any cost.
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            How can I report an issue with a recipe?
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            If you encounter any issues with a recipe, please use the "Report an Issue" button on the recipe page or contact our support team with the recipe details.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5 text-center">
        <div class="container">
            <h2 class="mb-4">Have More Questions?</h2>
            <p class="lead mb-4">Our support team is here to help you with any questions or concerns.</p>
            <a href="#" class="btn btn-success btn-lg px-5"><i class="fas fa-headset me-2"></i>Chat with Support</a>
        </div>
    </section>

    <?php include 'footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script>
        // Form submission handling
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const form = this;
            const alertDiv = document.getElementById('contactAlert');
            const submitBtn = document.getElementById('submitBtn');
            const originalBtnText = submitBtn.innerHTML;
            
            // Hide previous alerts
            alertDiv.classList.add('d-none');
            
            // Disable submit button
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';
            
            // Create FormData
            const formData = new FormData(form);
            
            // Submit via AJAX
            fetch('process_contact.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
                
                alertDiv.classList.remove('d-none');
                
                if (data.success) {
                    alertDiv.className = 'alert alert-success';
                    alertDiv.textContent = data.message || 'Thank you for your message! We will get back to you soon.';
                    form.reset();
                    
                    // Reset form fields if user was logged in
                    <?php if ($currentUserId): ?>
                    document.getElementById('name').value = '<?php echo htmlspecialchars(($_SESSION['user_firstname'] ?? '') . ' ' . ($_SESSION['user_lastname'] ?? '')); ?>';
                    document.getElementById('email').value = '<?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?>';
                    <?php endif; ?>
                } else {
                    alertDiv.className = 'alert alert-danger';
                    alertDiv.textContent = data.message || 'An error occurred. Please try again.';
                }
            })
            .catch(error => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
                
                alertDiv.classList.remove('d-none');
                alertDiv.className = 'alert alert-danger';
                alertDiv.textContent = 'An error occurred. Please try again later.';
                console.error('Contact form error:', error);
            });
        });
        
        // FAQ Toggle
        document.querySelectorAll('.faq-question').forEach(question => {
            question.addEventListener('click', () => {
                const item = question.parentElement;
                item.classList.toggle('active');
                
                // Toggle icon
                const icon = question.querySelector('i');
                icon.className = item.classList.contains('active') ? 'fas fa-chevron-up' : 'fas fa-chevron-down';
            });
        });
    </script>
</body>
</html>
