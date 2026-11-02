<?php
require 'auth_helper.php';
require 'pdo.php';
requireLogin();

// Get current user data from database
$userId = getUserId();
// Try to get image column, handle gracefully if it doesn't exist
try {
    $stmt = $pdo->prepare("SELECT user_id, firstname, lastname, email, image, created_at FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    // Ensure image is set even if null
    if (!isset($user['image'])) {
        $user['image'] = null;
    }
} catch (PDOException $e) {
    // If image column doesn't exist, select without it
    $stmt = $pdo->prepare("SELECT user_id, firstname, lastname, email, created_at FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $user['image'] = null;
}

if (!$user) {
    header('Location: logout.php');
    exit;
}

include 'header.php';
?>

    <!-- Profile Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <!-- Profile Card -->
                    <div class="card shadow-sm">
                        <div class="card-body text-center">
                            <div class="mb-3" id="profileImageContainer">
                                <?php
                                $imageUrl = null;
                                if (file_exists('image_upload_helper.php')) {
                                    require_once 'image_upload_helper.php';
                                    if (!empty($user['image'])) {
                                        $imageUrl = getProfileImageUrl($user['image']);
                                        // Check if file actually exists
                                        if ($imageUrl && !file_exists(__DIR__ . '/' . $imageUrl)) {
                                            $imageUrl = null;
                                        }
                                    }
                                }
                                if ($imageUrl):
                                ?>
                                    <img src="<?php echo htmlspecialchars($imageUrl); ?>?v=<?php echo time(); ?>" 
                                         alt="Profile Picture" 
                                         class="rounded-circle" 
                                         style="width: 100px; height: 100px; object-fit: cover; border: 3px solid #28a745;"
                                         onerror="this.style.display='none'; var icon = document.createElement('div'); icon.className = 'bg-warning text-white rounded-circle d-inline-flex align-items-center justify-content-center'; icon.style.cssText = 'width: 100px; height: 100px; font-size: 2.5rem;'; icon.innerHTML = '<i class=\'fas fa-user\'></i>'; this.parentElement.appendChild(icon);">
                                <?php else: ?>
                                    <div class="bg-warning text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 100px; height: 100px; font-size: 2.5rem;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <h4 class="card-title"><?php echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?></h4>
                            <p class="text-muted mb-3"><?php echo htmlspecialchars($user['email']); ?></p>
                            <hr>
                            <div class="text-start">
                                <p class="mb-2"><strong>Member since:</strong></p>
                                <p class="text-muted"><?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <!-- Profile Information Form -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-warning text-white">
                            <h5 class="mb-0"><i class="fas fa-user-edit me-2"></i>Profile Information</h5>
                        </div>
                        <div class="card-body">
                            <div id="profileAlert" class="alert d-none" role="alert"></div>
                            <form id="profileForm" method="POST" action="update_profile.php" enctype="multipart/form-data">
                                <?php if (file_exists('image_upload_helper.php')): ?>
                                <div class="mb-3">
                                    <label for="image" class="form-label">Profile Picture</label>
                                    <input type="file" class="form-control" id="image" name="image" 
                                           accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                                    <small class="form-text text-muted">Max file size: 5MB. Allowed formats: JPG, PNG, GIF, WEBP</small>
                                    <?php if ($imageUrl): ?>
                                        <div class="mt-2">
                                            <small class="text-muted">Current image will be replaced if you upload a new one.</small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="firstname" class="form-label">First Name</label>
                                        <input type="text" class="form-control" id="firstname" name="firstname" 
                                               value="<?php echo htmlspecialchars($user['firstname']); ?>" 
                                               maxlength="20" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="lastname" class="form-label">Last Name</label>
                                        <input type="text" class="form-control" id="lastname" name="lastname" 
                                               value="<?php echo htmlspecialchars($user['lastname']); ?>" 
                                               maxlength="20" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($user['email']); ?>" 
                                           maxlength="150" required>
                                </div>
                                <button type="submit" class="btn btn-success" id="profileSubmitBtn">
                                    <i class="fas fa-save me-2"></i>Update Profile
                                </button>
                            </form>
                            
                            <hr class="my-4">
                            
                            <!-- Change Password Section -->
                            <h6 class="mb-3"><i class="fas fa-key me-2"></i>Change Password</h6>
                            <div id="passwordAlert" class="alert d-none" role="alert"></div>
                            <form id="passwordForm" method="POST" action="update_password.php">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="currentPassword" class="form-label">Current Password</label>
                                        <input type="password" class="form-control" id="currentPassword" 
                                               name="current_password" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="newPassword" class="form-label">New Password</label>
                                        <input type="password" class="form-control" id="newPassword" 
                                               name="new_password" minlength="8" required>
                                        <small class="form-text text-muted">Min. 8 characters</small>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="confirmNewPassword" class="form-label">Confirm Password</label>
                                        <input type="password" class="form-control" id="confirmNewPassword" 
                                               name="confirm_new_password" minlength="8" required>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-warning" id="passwordSubmitBtn">
                                    <i class="fas fa-key me-2"></i>Change Password
                                </button>
                            </form>
                            
                            <hr class="my-4">
                            
                            <!-- Delete Account Section -->
                            <h6 class="mb-3 text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Delete Account</h6>
                            <div id="deleteAlert" class="alert d-none" role="alert"></div>
                            <p class="text-muted small mb-3">
                                <strong>Warning:</strong> Once you delete your account, there is no going back. 
                                This will permanently delete your account and all associated data.
                            </p>
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                                <i class="fas fa-trash-alt me-2"></i>Delete My Account
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Delete Account Confirmation Modal -->
    <div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteAccountModalLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>Delete Account
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="deleteAccountAlert" class="alert d-none" role="alert"></div>
                    <p class="fw-bold">Are you absolutely sure?</p>
                    <p>This action cannot be undone. This will permanently delete your account and remove all your data from our servers.</p>
                    <form id="deleteAccountForm" method="POST" action="delete_account.php">
                        <div class="mb-3">
                            <label for="deletePassword" class="form-label">Enter your password to confirm:</label>
                            <input type="password" class="form-control" id="deletePassword" name="password" required>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger" id="deleteAccountSubmitBtn">
                                <i class="fas fa-trash-alt me-2"></i>Delete My Account
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="js/main.js"></script>
    <script>
        // Handle profile form submission
        const profileForm = document.getElementById('profileForm');
        const profileAlert = document.getElementById('profileAlert');
        const profileSubmitBtn = document.getElementById('profileSubmitBtn');

        if (profileForm) {
            profileForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                profileAlert.classList.add('d-none');
                
                // Validate image file size if provided
                const imageInput = document.getElementById('image');
                if (imageInput && imageInput.files.length > 0) {
                    const file = imageInput.files[0];
                    const maxSize = 5 * 1024 * 1024; // 5MB
                    if (file.size > maxSize) {
                        profileAlert.classList.remove('d-none');
                        profileAlert.className = 'alert alert-danger';
                        profileAlert.textContent = 'Image file size exceeds 5MB limit.';
                        return;
                    }
                }
                
                profileSubmitBtn.disabled = true;
                profileSubmitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Updating...';
                
                const formData = new FormData(this);
                
                fetch('update_profile.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    profileSubmitBtn.disabled = false;
                    profileSubmitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Update Profile';
                    
                    profileAlert.classList.remove('d-none');
                    
                    if (data.success) {
                        profileAlert.className = 'alert alert-success';
                        profileAlert.textContent = data.message;
                        // Reload page after 1.5 seconds to update session and show new image
                        setTimeout(() => {
                            window.location.href = 'profile.php';
                        }, 1500);
                    } else {
                        profileAlert.className = 'alert alert-danger';
                        profileAlert.textContent = data.message;
                    }
                })
                .catch(error => {
                    profileSubmitBtn.disabled = false;
                    profileSubmitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Update Profile';
                    profileAlert.classList.remove('d-none');
                    profileAlert.className = 'alert alert-danger';
                    profileAlert.textContent = 'An error occurred. Please try again later.';
                    console.error('Profile update error:', error);
                });
            });
        }

        // Handle password form submission
        const passwordForm = document.getElementById('passwordForm');
        const passwordAlert = document.getElementById('passwordAlert');
        const passwordSubmitBtn = document.getElementById('passwordSubmitBtn');

        if (passwordForm) {
            passwordForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Validate password match
                const newPassword = document.getElementById('newPassword').value;
                const confirmPassword = document.getElementById('confirmNewPassword').value;
                
                if (newPassword !== confirmPassword) {
                    passwordAlert.classList.remove('d-none');
                    passwordAlert.className = 'alert alert-danger';
                    passwordAlert.textContent = 'New passwords do not match.';
                    return;
                }
                
                passwordAlert.classList.add('d-none');
                passwordSubmitBtn.disabled = true;
                passwordSubmitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Changing...';
                
                const formData = new FormData(this);
                
                fetch('update_password.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    passwordSubmitBtn.disabled = false;
                    passwordSubmitBtn.innerHTML = '<i class="fas fa-key me-2"></i>Change Password';
                    
                    passwordAlert.classList.remove('d-none');
                    
                    if (data.success) {
                        passwordAlert.className = 'alert alert-success';
                        passwordAlert.textContent = data.message;
                        passwordForm.reset();
                    } else {
                        passwordAlert.className = 'alert alert-danger';
                        passwordAlert.textContent = data.message;
                    }
                })
                .catch(error => {
                    passwordSubmitBtn.disabled = false;
                    passwordSubmitBtn.innerHTML = '<i class="fas fa-key me-2"></i>Change Password';
                    passwordAlert.classList.remove('d-none');
                    passwordAlert.className = 'alert alert-danger';
                    passwordAlert.textContent = 'An error occurred. Please try again later.';
                    console.error('Password change error:', error);
                });
            });
        }

        // Handle delete account form submission
        const deleteAccountForm = document.getElementById('deleteAccountForm');
        const deleteAccountAlert = document.getElementById('deleteAccountAlert');
        const deleteAccountSubmitBtn = document.getElementById('deleteAccountSubmitBtn');
        const deleteAccountModal = document.getElementById('deleteAccountModal');

        if (deleteAccountForm) {
            deleteAccountForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                deleteAccountAlert.classList.add('d-none');
                deleteAccountSubmitBtn.disabled = true;
                deleteAccountSubmitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Deleting...';
                
                const formData = new FormData(this);
                
                fetch('delete_account.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    deleteAccountSubmitBtn.disabled = false;
                    deleteAccountSubmitBtn.innerHTML = '<i class="fas fa-trash-alt me-2"></i>Delete My Account';
                    
                    deleteAccountAlert.classList.remove('d-none');
                    
                    if (data.success) {
                        deleteAccountAlert.className = 'alert alert-success';
                        deleteAccountAlert.textContent = data.message;
                        // Redirect to home page after 2 seconds
                        setTimeout(() => {
                            window.location.href = 'index.php';
                        }, 2000);
                    } else {
                        deleteAccountAlert.className = 'alert alert-danger';
                        deleteAccountAlert.textContent = data.message;
                    }
                })
                .catch(error => {
                    deleteAccountSubmitBtn.disabled = false;
                    deleteAccountSubmitBtn.innerHTML = '<i class="fas fa-trash-alt me-2"></i>Delete My Account';
                    deleteAccountAlert.classList.remove('d-none');
                    deleteAccountAlert.className = 'alert alert-danger';
                    deleteAccountAlert.textContent = 'An error occurred. Please try again later.';
                    console.error('Account deletion error:', error);
                });
            });
        }
    </script>
</body>
</html>

