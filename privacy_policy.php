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
        
        .policy-section ol {
            margin-left: 20px;
            margin-bottom: 20px;
        }
        
        .policy-section ol li {
            color: #555;
            line-height: 1.8;
            margin-bottom: 10px;
            font-size: 1.05rem;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .data-table th {
            background-color: var(--accent-yellow);
            color: var(--dark-color);
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        
        .data-table td {
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .data-table tr:last-child td {
            border-bottom: none;
        }
        
        .data-table tr:hover {
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
        
        .warning-box {
            background: #ffe6e6;
            border-left: 4px solid #ff4444;
            padding: 20px;
            margin: 25px 0;
            border-radius: 5px;
        }
        
        .warning-box p {
            margin-bottom: 0;
            color: #666;
        }
        
        .info-box {
            background: #e6f3ff;
            border-left: 4px solid #0066cc;
            padding: 20px;
            margin: 25px 0;
            border-radius: 5px;
        }
        
        .info-box p {
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
            <h1 class="display-4 fw-bold mb-4">Privacy Policy</h1>
            <p class="lead mb-0">Your privacy is important to us. Learn how we protect and handle your personal information.</p>
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
                            <p>Welcome to FoodFusion ("we," "our," or "us"). We are committed to protecting your privacy and ensuring you have a positive experience on our website and in using our products and services.</p>
                            <p>This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website <strong>FoodFusion</strong>, use our mobile application, or engage with our services. Please read this privacy policy carefully. If you do not agree with the terms of this privacy policy, please do not access the site.</p>
                            <p><strong>Effective Date:</strong> This Privacy Policy is effective as of <?php echo date('F j, Y'); ?> and will remain in effect except with respect to any changes in its provisions in the future, which will be in effect immediately after being posted on this page.</p>
                        </div>

                        <!-- Information We Collect -->
                        <div class="policy-section">
                            <h2>2. Information We Collect</h2>
                            <p>We collect information that you provide directly to us and information that is automatically collected when you use our services.</p>
                            
                            <h3>2.1 Information You Provide to Us</h3>
                            <p>We may collect information that you provide directly to us, including:</p>
                            <ul>
                                <li><strong>Account Information:</strong> When you create an account, we collect your first name, last name, email address, and password.</li>
                                <li><strong>Profile Information:</strong> You may choose to provide additional information such as a profile picture, bio, location, and dietary preferences.</li>
                                <li><strong>Content You Submit:</strong> Recipes, comments, ratings, reviews, photos, and other content you post on our platform.</li>
                                <li><strong>Cookbook and Favorites:</strong> Recipes you save to your cookbook or mark as favorites.</li>
                                <li><strong>Communication Data:</strong> Information you provide when you contact us, including support requests, feedback, or inquiries.</li>
                                <li><strong>Payment Information:</strong> If you make purchases through our platform, we collect payment card information and billing details (processed securely through third-party payment processors).</li>
                            </ul>
                            
                            <h3>2.2 Information Automatically Collected</h3>
                            <p>When you access or use our services, we automatically collect certain information, including:</p>
                            <ul>
                                <li><strong>Usage Data:</strong> Information about how you interact with our website, including pages visited, time spent on pages, links clicked, and search queries.</li>
                                <li><strong>Device Information:</strong> Your device type, operating system, browser type, IP address, device identifiers, and mobile network information.</li>
                                <li><strong>Location Data:</strong> General location information based on your IP address or device settings (if you grant permission).</li>
                                <li><strong>Cookies and Tracking Technologies:</strong> We use cookies, web beacons, and similar technologies to collect information about your browsing activities. For more details, please see our <a href="cookie_policy.php" style="color: var(--accent-yellow); text-decoration: underline;">Cookie Policy</a>.</li>
                                <li><strong>Log Data:</strong> Server logs that record information about your use of our services, including access times, pages viewed, and error messages.</li>
                            </ul>
                            
                            <h3>2.3 Information from Third Parties</h3>
                            <p>We may receive information about you from third-party services, including:</p>
                            <ul>
                                <li>Social media platforms when you connect your account or share content</li>
                                <li>Authentication services if you log in using third-party credentials</li>
                                <li>Analytics providers and advertising partners</li>
                                <li>Public databases and data providers</li>
                            </ul>
                        </div>

                        <!-- How We Use Information -->
                        <div class="policy-section">
                            <h2>3. How We Use Your Information</h2>
                            <p>We use the information we collect for various purposes, including:</p>
                            
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Purpose</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Service Provision</strong></td>
                                        <td>To provide, maintain, and improve our services, including recipe browsing, cookbook management, and community features.</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Account Management</strong></td>
                                        <td>To create and manage your account, authenticate your identity, and provide customer support.</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Personalization</strong></td>
                                        <td>To personalize your experience by showing relevant recipes, recommendations, and content based on your preferences and activity.</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Communication</strong></td>
                                        <td>To send you service-related notifications, updates, newsletters, and respond to your inquiries.</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Content Moderation</strong></td>
                                        <td>To monitor, review, and moderate user-generated content to ensure compliance with our terms of service.</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Analytics</strong></td>
                                        <td>To analyze usage patterns, improve our services, and develop new features.</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Marketing</strong></td>
                                        <td>To send promotional communications (with your consent) and display targeted advertisements.</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Legal Compliance</strong></td>
                                        <td>To comply with legal obligations, enforce our terms, and protect our rights and the rights of our users.</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Security</strong></td>
                                        <td>To detect, prevent, and address fraud, security issues, and technical problems.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Information Sharing -->
                        <div class="policy-section">
                            <h2>4. How We Share Your Information</h2>
                            <p>We do not sell your personal information. We may share your information in the following circumstances:</p>
                            
                            <h3>4.1 Public Information</h3>
                            <p>Some information you provide is public by default, including:</p>
                            <ul>
                                <li>Your username and profile information (if you choose to make it public)</li>
                                <li>Recipes you publish on the platform</li>
                                <li>Comments, reviews, and ratings you post</li>
                                <li>Content you share publicly</li>
                            </ul>
                            
                            <div class="warning-box">
                                <p><strong>Important:</strong> Please be mindful of the information you choose to make public, as it can be viewed and used by others.</p>
                            </div>
                            
                            <h3>4.2 Service Providers</h3>
                            <p>We may share your information with third-party service providers who perform services on our behalf, including:</p>
                            <ul>
                                <li>Hosting and cloud storage providers</li>
                                <li>Payment processors</li>
                                <li>Email service providers</li>
                                <li>Analytics and advertising partners</li>
                                <li>Customer support services</li>
                            </ul>
                            
                            <h3>4.3 Legal Requirements</h3>
                            <p>We may disclose your information if required to do so by law or in response to valid requests by public authorities (e.g., a court or government agency).</p>
                            
                            <h3>4.4 Business Transfers</h3>
                            <p>If we are involved in a merger, acquisition, or asset sale, your information may be transferred as part of that transaction.</p>
                            
                            <h3>4.5 With Your Consent</h3>
                            <p>We may share your information with third parties when you explicitly consent to such sharing.</p>
                        </div>

                        <!-- Data Security -->
                        <div class="policy-section">
                            <h2>5. Data Security</h2>
                            <p>We implement appropriate technical and organizational security measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction. These measures include:</p>
                            <ul>
                                <li>Encryption of data in transit using SSL/TLS protocols</li>
                                <li>Secure password hashing and storage</li>
                                <li>Regular security assessments and updates</li>
                                <li>Access controls and authentication mechanisms</li>
                                <li>Secure data centers and infrastructure</li>
                            </ul>
                            
                            <div class="info-box">
                                <p><strong>Note:</strong> While we strive to protect your personal information, no method of transmission over the Internet or electronic storage is 100% secure. We cannot guarantee absolute security, but we are committed to maintaining the highest standards of data protection.</p>
                            </div>
                        </div>

                        <!-- Your Rights -->
                        <div class="policy-section">
                            <h2>6. Your Privacy Rights</h2>
                            <p>Depending on your location, you may have certain rights regarding your personal information:</p>
                            
                            <h3>6.1 Access and Portability</h3>
                            <p>You have the right to access and receive a copy of your personal information that we hold.</p>
                            
                            <h3>6.2 Correction</h3>
                            <p>You can update or correct your personal information at any time through your account settings or by contacting us.</p>
                            
                            <h3>6.3 Deletion</h3>
                            <p>You may request deletion of your personal information. Note that some information may be retained for legal or legitimate business purposes.</p>
                            
                            <h3>6.4 Objection and Restriction</h3>
                            <p>You have the right to object to certain processing of your information or request restriction of processing.</p>
                            
                            <h3>6.5 Withdraw Consent</h3>
                            <p>Where we rely on your consent, you may withdraw it at any time. This will not affect the lawfulness of processing before withdrawal.</p>
                            
                            <h3>6.6 Opt-Out of Marketing</h3>
                            <p>You can opt-out of receiving marketing communications by using the unsubscribe link in our emails or updating your preferences in your account settings.</p>
                            
                            <div class="highlight-box">
                                <p><strong>To exercise your rights:</strong> Please contact us at <strong>privacy@foodfusion.com</strong> or use the contact information provided at the end of this policy. We will respond to your request within a reasonable timeframe.</p>
                            </div>
                        </div>

                        <!-- Data Retention -->
                        <div class="policy-section">
                            <h2>7. Data Retention</h2>
                            <p>We retain your personal information for as long as necessary to fulfill the purposes outlined in this Privacy Policy, unless a longer retention period is required or permitted by law. Factors we consider when determining retention periods include:</p>
                            <ul>
                                <li>The nature and sensitivity of the information</li>
                                <li>The purposes for which we process the information</li>
                                <li>Legal and regulatory requirements</li>
                                <li>Our legitimate business interests</li>
                            </ul>
                            <p>When you delete your account, we will delete or anonymize your personal information, except where we are required to retain it for legal purposes or legitimate business interests.</p>
                        </div>

                        <!-- Children's Privacy -->
                        <div class="policy-section">
                            <h2>8. Children's Privacy</h2>
                            <p>FoodFusion is not intended for children under the age of 13. We do not knowingly collect personal information from children under 13. If you are a parent or guardian and believe your child has provided us with personal information, please contact us immediately.</p>
                            <p>If we become aware that we have collected personal information from a child under 13 without parental consent, we will take steps to delete such information from our servers.</p>
                        </div>

                        <!-- International Data Transfers -->
                        <div class="policy-section">
                            <h2>9. International Data Transfers</h2>
                            <p>Your information may be transferred to and processed in countries other than your country of residence. These countries may have data protection laws that differ from those in your country.</p>
                            <p>When we transfer your information internationally, we ensure appropriate safeguards are in place to protect your information in accordance with this Privacy Policy and applicable data protection laws.</p>
                        </div>

                        <!-- Third-Party Links -->
                        <div class="policy-section">
                            <h2>10. Third-Party Links and Services</h2>
                            <p>Our website may contain links to third-party websites, services, or applications that are not operated by us. This Privacy Policy does not apply to third-party services.</p>
                            <p>We encourage you to review the privacy policies of any third-party services you access through our platform. We are not responsible for the privacy practices or content of third-party services.</p>
                        </div>

                        <!-- California Privacy Rights -->
                        <div class="policy-section">
                            <h2>11. California Privacy Rights</h2>
                            <p>If you are a California resident, you have additional rights under the California Consumer Privacy Act (CCPA), including:</p>
                            <ul>
                                <li>The right to know what personal information we collect, use, and disclose</li>
                                <li>The right to delete your personal information</li>
                                <li>The right to opt-out of the sale of personal information (we do not sell personal information)</li>
                                <li>The right to non-discrimination for exercising your privacy rights</li>
                            </ul>
                            <p>To exercise these rights, please contact us using the information provided in the "Contact Us" section below.</p>
                        </div>

                        <!-- GDPR Rights -->
                        <div class="policy-section">
                            <h2>12. GDPR Rights (European Users)</h2>
                            <p>If you are located in the European Economic Area (EEA), you have additional rights under the General Data Protection Regulation (GDPR), including:</p>
                            <ul>
                                <li>Right of access to your personal data</li>
                                <li>Right to rectification of inaccurate data</li>
                                <li>Right to erasure ("right to be forgotten")</li>
                                <li>Right to restrict processing</li>
                                <li>Right to data portability</li>
                                <li>Right to object to processing</li>
                                <li>Rights related to automated decision-making</li>
                            </ul>
                            <p>To exercise these rights, please contact us at <strong>privacy@foodfusion.com</strong>.</p>
                        </div>

                        <!-- Policy Updates -->
                        <div class="policy-section">
                            <h2>13. Changes to This Privacy Policy</h2>
                            <p>We may update this Privacy Policy from time to time to reflect changes in our practices, technology, legal requirements, or other factors. We will notify you of any material changes by:</p>
                            <ul>
                                <li>Posting the updated Privacy Policy on this page</li>
                                <li>Updating the "Last Updated" date</li>
                                <li>Sending you an email notification (for significant changes)</li>
                                <li>Displaying a prominent notice on our website</li>
                            </ul>
                            <p>We encourage you to review this Privacy Policy periodically to stay informed about how we protect your information.</p>
                        </div>

                        <!-- Contact Information -->
                        <div class="policy-section">
                            <h2>14. Contact Us</h2>
                            <p>If you have any questions, concerns, or requests regarding this Privacy Policy or our data practices, please contact us:</p>
                            <ul>
                                <li><strong>Email:</strong> privacy@foodfusion.com</li>
                                <li><strong>Address:</strong> 123 Foodie Street, Culinary District, Mandalay, Myanmar</li>
                                <li><strong>Phone:</strong> +95 9 123 456 789</li>
                                <li><strong>Data Protection Officer:</strong> dpo@foodfusion.com</li>
                            </ul>
                            <p>We will make every effort to respond to your inquiry within 30 days.</p>
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
                    <p class="lead mb-4">Learn more about our policies and how we protect your information.</p>
                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <a href="cookie_policy.php" class="btn btn-outline-dark btn-lg">Cookie Policy</a>
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
