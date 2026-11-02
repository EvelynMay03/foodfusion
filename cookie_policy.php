<?php
    include 'header.php';
    require 'pdo.php';
    session_start();
?>
<head>
    <style>
        .policy-hero {
            background: linear-gradient(135deg, rgba(167, 199, 231, 0.9), rgba(173, 216, 229, 0.9)), 
                        url('https://images.unsplash.com/photo-1504674900247-087703934569?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            padding: 100px 0;
            color: #0A1128;
            text-align: center;
        }
        
        .policy-content {
            background: white;
            border-radius: 15px;
            padding: 50px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        
        .policy-section {
            margin-bottom: 40px;
        }
        
        .policy-section h2 {
            color: var(--dark-color);
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid var(--accent-yellow);
        }
        
        .policy-section h3 {
            color: var(--dark-color);
            font-size: 1.5rem;
            font-weight: 600;
            margin-top: 30px;
            margin-bottom: 15px;
        }
        
        .policy-section p {
            color: #555;
            line-height: 1.8;
            margin-bottom: 15px;
            font-size: 1.05rem;
        }
        
        .policy-section ul {
            margin-left: 20px;
            margin-bottom: 20px;
        }
        
        .policy-section ul li {
            color: #555;
            line-height: 1.8;
            margin-bottom: 10px;
            font-size: 1.05rem;
        }
        
        .cookie-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .cookie-table th {
            background-color: var(--accent-yellow);
            color: var(--dark-color);
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        
        .cookie-table td {
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .cookie-table tr:last-child td {
            border-bottom: none;
        }
        
        .cookie-table tr:hover {
            background-color: #f9f9f9;
        }
        
        .highlight-box {
            background: #fff9e6;
            border-left: 4px solid var(--accent-yellow);
            padding: 20px;
            margin: 25px 0;
            border-radius: 5px;
        }
        
        .highlight-box p {
            margin-bottom: 0;
            color: #666;
        }
        
        .last-updated {
            text-align: center;
            color: #888;
            font-style: italic;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }
    </style>
    <!-- Hero Section -->
    <section class="policy-hero">
        <div class="container">
            <h1 class="display-4 fw-bold mb-4">Cookie Policy</h1>
            <p class="lead mb-0">Understanding how we use cookies to enhance your experience</p>
        </div>
    </section>

    <!-- Policy Content -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="policy-content">
                        <!-- Introduction -->
                        <div class="policy-section">
                            <h2>1. Introduction</h2>
                            <p>Welcome to FoodFusion's Cookie Policy. This policy explains what cookies are, how we use them on our website, and what choices you have regarding their use.</p>
                            <p>By using FoodFusion, you consent to the use of cookies in accordance with this policy. If you do not agree to our use of cookies, you should set your browser settings accordingly or refrain from using our website.</p>
                        </div>

                        <!-- What are Cookies -->
                        <div class="policy-section">
                            <h2>2. What Are Cookies?</h2>
                            <p>Cookies are small text files that are placed on your computer or mobile device when you visit a website. They are widely used to make websites work more efficiently and provide information to the website owners.</p>
                            <p>Cookies can be:</p>
                            <ul>
                                <li><strong>Session cookies:</strong> Temporary cookies that are deleted when you close your browser</li>
                                <li><strong>Persistent cookies:</strong> Cookies that remain on your device for a set period or until you delete them</li>
                                <li><strong>First-party cookies:</strong> Set by the website you are visiting</li>
                                <li><strong>Third-party cookies:</strong> Set by a domain other than the one you are visiting</li>
                            </ul>
                        </div>

                        <!-- How We Use Cookies -->
                        <div class="policy-section">
                            <h2>3. How We Use Cookies</h2>
                            <p>FoodFusion uses cookies for various purposes to improve your experience on our platform:</p>
                            
                            <h3>3.1 Essential Cookies</h3>
                            <p>These cookies are necessary for the website to function properly. They enable core functionality such as security, network management, and accessibility.</p>
                            
                            <h3>3.2 Functionality Cookies</h3>
                            <p>These cookies allow the website to remember choices you make (such as your username, language preference, or region) and provide enhanced, personalized features.</p>
                            
                            <h3>3.3 Analytics Cookies</h3>
                            <p>These cookies help us understand how visitors interact with our website by collecting and reporting information anonymously. This helps us improve the way our website works.</p>
                            
                            <h3>3.4 Marketing Cookies</h3>
                            <p>These cookies are used to track visitors across websites to display relevant advertisements and measure the effectiveness of our marketing campaigns.</p>
                        </div>

                        <!-- Types of Cookies We Use -->
                        <div class="policy-section">
                            <h2>4. Types of Cookies We Use</h2>
                            <p>The following table details the specific cookies we use on FoodFusion:</p>
                            
                            <table class="cookie-table">
                                <thead>
                                    <tr>
                                        <th>Cookie Name</th>
                                        <th>Purpose</th>
                                        <th>Type</th>
                                        <th>Duration</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>PHPSESSID</strong></td>
                                        <td>Maintains your session state and login status</td>
                                        <td>Essential</td>
                                        <td>Session</td>
                                    </tr>
                                    <tr>
                                        <td><strong>remember_me</strong></td>
                                        <td>Remembers your login preference when you select "Remember Me"</td>
                                        <td>Functionality</td>
                                        <td>30 days</td>
                                    </tr>
                                    <tr>
                                        <td><strong>user_preferences</strong></td>
                                        <td>Stores your display preferences (theme, language, etc.)</td>
                                        <td>Functionality</td>
                                        <td>1 year</td>
                                    </tr>
                                    <tr>
                                        <td><strong>recipe_favorites</strong></td>
                                        <td>Stores your favorite recipes list</td>
                                        <td>Functionality</td>
                                        <td>1 year</td>
                                    </tr>
                                    <tr>
                                        <td><strong>cookbook_items</strong></td>
                                        <td>Stores items in your personal cookbook</td>
                                        <td>Functionality</td>
                                        <td>1 year</td>
                                    </tr>
                                    <tr>
                                        <td><strong>analytics_id</strong></td>
                                        <td>Helps us understand how you use our website</td>
                                        <td>Analytics</td>
                                        <td>2 years</td>
                                    </tr>
                                    <tr>
                                        <td><strong>cookie_consent</strong></td>
                                        <td>Remembers your cookie preferences</td>
                                        <td>Essential</td>
                                        <td>1 year</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Third-Party Cookies -->
                        <div class="policy-section">
                            <h2>5. Third-Party Cookies</h2>
                            <p>In addition to our own cookies, we may also use various third-party cookies to report usage statistics of the website, deliver advertisements, and so on. These third-party services may include:</p>
                            <ul>
                                <li><strong>Google Analytics:</strong> To analyze website traffic and user behavior</li>
                                <li><strong>Social Media Platforms:</strong> To enable social sharing features (Facebook, Twitter, Instagram, Pinterest)</li>
                                <li><strong>Advertising Networks:</strong> To deliver relevant advertisements</li>
                                <li><strong>Content Delivery Networks:</strong> To optimize website performance</li>
                            </ul>
                            <p>These third parties may use cookies, web beacons, and similar technologies to collect or receive information from FoodFusion and elsewhere on the internet and use that information to provide measurement services and target ads.</p>
                        </div>

                        <!-- Managing Cookies -->
                        <div class="policy-section">
                            <h2>6. Managing Your Cookie Preferences</h2>
                            <p>You have the right to accept or reject cookies. Most web browsers automatically accept cookies, but you can usually modify your browser settings to decline cookies if you prefer.</p>
                            
                            <h3>6.1 Browser Settings</h3>
                            <p>You can control and manage cookies in various ways. Please keep in mind that removing or blocking cookies can impact your user experience and parts of our website may no longer be fully accessible.</p>
                            <p>To manage cookies in your browser:</p>
                            <ul>
                                <li><strong>Chrome:</strong> Settings → Privacy and Security → Cookies and other site data</li>
                                <li><strong>Firefox:</strong> Options → Privacy & Security → Cookies and Site Data</li>
                                <li><strong>Safari:</strong> Preferences → Privacy → Cookies and website data</li>
                                <li><strong>Edge:</strong> Settings → Privacy, search, and services → Cookies and site permissions</li>
                            </ul>
                            
                            <h3>6.2 Cookie Consent</h3>
                            <p>When you first visit FoodFusion, you will be asked to consent to our use of cookies. You can change your cookie preferences at any time by accessing the cookie settings in your account preferences or by clearing your browser cookies.</p>
                            
                            <div class="highlight-box">
                                <p><strong>Note:</strong> If you choose to disable cookies, some features of FoodFusion may not function properly. Essential cookies cannot be disabled as they are necessary for the website to function.</p>
                            </div>
                        </div>

                        <!-- Do Not Track -->
                        <div class="policy-section">
                            <h2>7. Do Not Track Signals</h2>
                            <p>Some browsers include a "Do Not Track" (DNT) feature that signals to websites you visit that you do not want to have your online activity tracked. Currently, there is no standard for how DNT signals should be interpreted. As a result, FoodFusion does not currently respond to DNT browser signals or mechanisms.</p>
                        </div>

                        <!-- Updates to Policy -->
                        <div class="policy-section">
                            <h2>8. Updates to This Cookie Policy</h2>
                            <p>We may update this Cookie Policy from time to time to reflect changes in technology, legislation, or our business operations. We will notify you of any material changes by posting the new Cookie Policy on this page and updating the "Last Updated" date.</p>
                            <p>We encourage you to review this Cookie Policy periodically to stay informed about how we use cookies.</p>
                        </div>

                        <!-- Contact Information -->
                        <div class="policy-section">
                            <h2>9. Contact Us</h2>
                            <p>If you have any questions about our use of cookies or this Cookie Policy, please contact us:</p>
                            <ul>
                                <li><strong>Email:</strong> privacy@foodfusion.com</li>
                                <li><strong>Address:</strong> 123 Foodie Street, Culinary District, Mandalay, Myanmar</li>
                                <li><strong>Phone:</strong> +95 9 123 456 789</li>
                            </ul>
                        </div>

                        <!-- Last Updated -->
                        <div class="last-updated">
                            <p><strong>Last Updated:</strong> <?php echo date('F j, Y'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Additional Resources Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h2 class="mb-4">Related Policies</h2>
                    <p class="lead mb-4">Learn more about how we protect your privacy and handle your data.</p>
                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <a href="privacy_policy.php" class="btn btn-outline-dark btn-lg">Privacy Policy</a>
                        <a href="terms_of_service.php" class="btn btn-outline-dark btn-lg">Terms of Service</a>
                        <a href="about.php" class="btn btn-outline-dark btn-lg">About Us</a>
                        <a href="contact.php" class="btn btn-outline-dark btn-lg">Contact Us</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

   <?php include 'footer.php'; ?>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="js/main.js"></script>
</body>
</html>
