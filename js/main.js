document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Show cookie consent banner if not already accepted
    if (!localStorage.getItem('cookieConsent')) {
        document.querySelector('.cookie-consent-banner').classList.remove('d-none');
    }

    // Handle cookie consent
    document.getElementById('cookieAccept').addEventListener('click', function() {
        localStorage.setItem('cookieConsent', 'accepted');
        document.querySelector('.cookie-consent-banner').classList.add('d-none');
    });

    document.getElementById('cookieDecline').addEventListener('click', function() {
        localStorage.setItem('cookieConsent', 'declined');
        document.querySelector('.cookie-consent-banner').classList.add('d-none');
    });

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 80,
                    behavior: 'smooth'
                });
            }
        });
    });

    // Add animation to elements when they come into view
    const animateOnScroll = function() {
        const elements = document.querySelectorAll('.animate-on-scroll');
        elements.forEach(element => {
            const elementTop = element.getBoundingClientRect().top;
            const windowHeight = window.innerHeight;
            
            if (elementTop < windowHeight - 100) {
                element.classList.add('animate-fade-in');
            }
        });
    };

    // Initial check
    animateOnScroll();
    
    // Check on scroll
    window.addEventListener('scroll', animateOnScroll);

    // Handle registration form submission
    const registerForm = document.getElementById('registerForm');
    const registerAlert = document.getElementById('registerAlert');
    const registerSubmitBtn = document.getElementById('registerSubmitBtn');
    const registerPassword = document.getElementById('registerPassword');
    
    // Real-time password validation feedback
    if (registerPassword) {
        registerPassword.addEventListener('input', function() {
            const password = this.value;
            
            // Check each requirement
            const hasLength = password.length >= 8;
            const hasUppercase = /[A-Z]/.test(password);
            const hasLowercase = /[a-z]/.test(password);
            const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(password);
            
            // Update requirement indicators
            updateRequirement('req-length', hasLength);
            updateRequirement('req-uppercase', hasUppercase);
            updateRequirement('req-lowercase', hasLowercase);
            updateRequirement('req-special', hasSpecialChar);
        });
    }
    
    // Helper function to update requirement indicator
    function updateRequirement(elementId, isMet) {
        const element = document.getElementById(elementId);
        if (element) {
            if (isMet) {
                element.classList.remove('text-muted');
                element.classList.add('text-success');
                element.querySelector('i').className = 'fas fa-check-circle';
            } else {
                element.classList.remove('text-success');
                element.classList.add('text-muted');
                element.querySelector('i').className = 'fas fa-circle';
                element.querySelector('i').style.fontSize = '0.5rem';
            }
        }
    }
    
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Hide previous alerts
            registerAlert.classList.add('d-none');
            
            // Get password fields
            const password = document.getElementById('registerPassword').value;
            const confirmPassword = document.getElementById('registerConfirmPassword').value;
            
            // Validate password complexity
            const hasUppercase = /[A-Z]/.test(password);
            const hasLowercase = /[a-z]/.test(password);
            const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(password);
            
            // Check password requirements
            if (!hasUppercase) {
                registerAlert.classList.remove('d-none');
                registerAlert.className = 'alert alert-danger';
                registerAlert.textContent = 'Password must contain at least one uppercase letter.';
                return;
            }
            
            if (!hasLowercase) {
                registerAlert.classList.remove('d-none');
                registerAlert.className = 'alert alert-danger';
                registerAlert.textContent = 'Password must contain at least one lowercase letter.';
                return;
            }
            
            if (!hasSpecialChar) {
                registerAlert.classList.remove('d-none');
                registerAlert.className = 'alert alert-danger';
                registerAlert.textContent = 'Password must contain at least one special character (!@#$%^&*(),.?":{}|<>).';
                return;
            }
            
            if (password.length < 8) {
                registerAlert.classList.remove('d-none');
                registerAlert.className = 'alert alert-danger';
                registerAlert.textContent = 'Password must be at least 8 characters long.';
                return;
            }
            
            // Check if passwords match
            if (password !== confirmPassword) {
                registerAlert.classList.remove('d-none');
                registerAlert.className = 'alert alert-danger';
                registerAlert.textContent = 'Passwords do not match.';
                return;
            }
            
            // Disable submit button and show loading state
            registerSubmitBtn.disabled = true;
            registerSubmitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Creating Account...';
            
            // Get form data
            const formData = new FormData(this);
            
            // Submit via AJAX
            fetch('register.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                // Reset button state
                registerSubmitBtn.disabled = false;
                registerSubmitBtn.innerHTML = 'Create Account';
                
                // Show alert message
                registerAlert.classList.remove('d-none');
                
                if (data.success) {
                    registerAlert.className = 'alert alert-success';
                    registerAlert.textContent = data.message;
                    
                    // Reset form
                    registerForm.reset();
                    
                    // Close modal after 2 seconds and reload page
                    setTimeout(() => {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('registerModal'));
                        modal.hide();
                        window.location.reload();
                    }, 2000);
                } else {
                    registerAlert.className = 'alert alert-danger';
                    // Show error message, including debug info if available
                    let errorMsg = data.message;
                    if (data.debug) {
                        errorMsg += ' (Debug: ' + data.debug + ')';
                    }
                    registerAlert.textContent = errorMsg;
                    console.error('Registration error:', data);
                }
            })
            .catch(error => {
                // Reset button state
                registerSubmitBtn.disabled = false;
                registerSubmitBtn.innerHTML = 'Create Account';
                
                // Show error
                registerAlert.classList.remove('d-none');
                registerAlert.className = 'alert alert-danger';
                registerAlert.textContent = 'An error occurred. Please try again later.';
                console.error('Registration error:', error);
            });
        });
    }

    // Handle login form submission
    const loginForm = document.getElementById('loginForm');
    const loginAlert = document.getElementById('loginAlert');
    const loginSubmitBtn = document.getElementById('loginSubmitBtn');
    
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Hide previous alerts
            loginAlert.classList.add('d-none');
            
            // Disable submit button and show loading state
            loginSubmitBtn.disabled = true;
            loginSubmitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Logging in...';
            
            // Get form data
            const formData = new FormData(this);
            
            // Submit via AJAX
            fetch('login.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                // Get response as text first to handle JSON parsing errors
                return response.text().then(text => {
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('JSON parse error:', text);
                        throw new Error('Invalid JSON response from server');
                    }
                });
            })
            .then(data => {
                // Reset button state
                loginSubmitBtn.disabled = false;
                loginSubmitBtn.innerHTML = 'Login';
                
                if (data.success) {
                    // Show success message briefly
                    loginAlert.classList.remove('d-none');
                    loginAlert.className = 'alert alert-success';
                    loginAlert.textContent = data.message;
                    
                    // Reset form
                    loginForm.reset();
                    
                    // Close modal immediately and redirect/reload
                    const modal = bootstrap.Modal.getInstance(document.getElementById('loginModal'));
                    if (modal) {
                        modal.hide();
                    }
                    
                    // Redirect to home page or reload immediately
                    window.location.href = 'index.php';
                } else {
                    // Show error message
                    loginAlert.classList.remove('d-none');
                    loginAlert.className = 'alert alert-danger';
                    // Show error message, including debug info if available
                    let errorMsg = data.message || 'Login failed. Please try again.';
                    if (data.debug) {
                        errorMsg += ' (Debug: ' + data.debug + ')';
                    }
                    loginAlert.textContent = errorMsg;
                    console.error('Login error:', data);
                }
            })
            .catch(error => {
                // Reset button state
                loginSubmitBtn.disabled = false;
                loginSubmitBtn.innerHTML = 'Login';
                
                // Show error
                loginAlert.classList.remove('d-none');
                loginAlert.className = 'alert alert-danger';
                loginAlert.textContent = 'An error occurred. Please try again later.';
                console.error('Login error:', error);
            });
        });
    }

    // Add animation to featured recipes on page load
    setTimeout(() => {
        const recipeCards = document.querySelectorAll('#featured .card');
        recipeCards.forEach((card, index) => {
            card.style.animationDelay = `${index * 0.1}s`;
            card.classList.add('animate-on-scroll');
        });
    }, 500);

    // Initialize any carousels
    const carousels = document.querySelectorAll('.carousel');
    if (carousels.length > 0) {
        carousels.forEach(carousel => {
            new bootstrap.Carousel(carousel, {
                interval: 5000,
                touch: true
            });
        });
    }

    // Handle newsletter subscription form submission
    const subscribeForm = document.getElementById('subscribeForm');
    const subscribeAlert = document.getElementById('subscribeAlert');
    const subscribeBtn = document.getElementById('subscribeBtn');
    const subscribeEmail = document.getElementById('subscribeEmail');

    if (subscribeForm) {
        subscribeForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Hide previous alerts
            if (subscribeAlert) {
                subscribeAlert.classList.add('d-none');
            }
            
            // Get email value
            const email = subscribeEmail ? subscribeEmail.value.trim() : '';
            
            if (!email) {
                if (subscribeAlert) {
                    subscribeAlert.classList.remove('d-none');
                    subscribeAlert.className = 'alert alert-danger';
                    subscribeAlert.textContent = 'Please enter your email address.';
                }
                return;
            }
            
            // Disable submit button and show loading state
            if (subscribeBtn) {
                subscribeBtn.disabled = true;
                const originalText = subscribeBtn.innerHTML;
                subscribeBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Subscribing...';
                
                // Get form data
                const formData = new FormData(this);
                
                // Submit via AJAX
                fetch('subscribe.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.text().then(text => {
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            console.error('JSON parse error:', text);
                            throw new Error('Invalid JSON response');
                        }
                    });
                })
                .then(data => {
                    // Reset button state
                    subscribeBtn.disabled = false;
                    subscribeBtn.innerHTML = originalText;
                    
                    // Show alert message
                    if (subscribeAlert) {
                        subscribeAlert.classList.remove('d-none');
                        
                        if (data.success) {
                            subscribeAlert.className = 'alert alert-success';
                            subscribeAlert.textContent = data.message;
                            
                            // Reset form
                            subscribeForm.reset();
                        } else {
                            subscribeAlert.className = 'alert alert-danger';
                            let errorMsg = data.message || 'An error occurred. Please try again.';
                            if (data.error && console) {
                                console.error('Subscription error details:', data.error);
                            }
                            subscribeAlert.textContent = errorMsg;
                        }
                    }
                })
                .catch(error => {
                    // Reset button state
                    subscribeBtn.disabled = false;
                    subscribeBtn.innerHTML = originalText;
                    
                    // Show error
                    if (subscribeAlert) {
                        subscribeAlert.classList.remove('d-none');
                        subscribeAlert.className = 'alert alert-danger';
                        subscribeAlert.textContent = 'An error occurred. Please try again later.';
                    }
                    console.error('Subscription error:', error);
                });
            }
        });
    }
});

// Add active class to current nav link
const navLinks = document.querySelectorAll('.nav-link');
const currentPage = location.pathname.split('/').pop() || 'index.html';

navLinks.forEach(link => {
    if (link.getAttribute('href') === currentPage) {
        link.classList.add('active');
        link.setAttribute('aria-current', 'page');
    }
});

// Handle search form submission
// Note: Main page search is handled by inline JavaScript in index.php
// This handler is for other pages if needed
const searchForm = document.querySelector('.search-section form');
if (searchForm && !document.getElementById('mainSearchForm')) {
    searchForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const searchQuery = this.querySelector('input[type="search"]').value.trim();
        if (searchQuery) {
            // Redirect to recipes page with search query
            window.location.href = `recipes.php?search=${encodeURIComponent(searchQuery)}`;
        } else {
            // If no search query, just go to recipes page
            window.location.href = 'recipes.php';
        }
    });
}

// Add animation to elements when they come into view (for older browsers)
if (!('IntersectionObserver' in window)) {
    const animateOnScrollLegacy = function() {
        const elements = document.querySelectorAll('.animate-on-scroll');
        elements.forEach(element => {
            const elementTop = element.getBoundingClientRect().top;
            const windowHeight = window.innerHeight;
            
            if (elementTop < windowHeight - 100) {
                element.classList.add('animate-fade-in');
            }
        });
    };

    window.addEventListener('scroll', animateOnScrollLegacy);
    animateOnScrollLegacy();
}
