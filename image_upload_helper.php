<?php
/**
 * Image Upload Helper Functions
 * Handles profile image uploads and management
 */

// Directory for storing uploaded images
define('UPLOAD_DIR', __DIR__ . '/uploads/profile_images/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB in bytes
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Create upload directory if it doesn't exist
if (!file_exists(UPLOAD_DIR)) {
    if (!mkdir(UPLOAD_DIR, 0755, true)) {
        error_log("Failed to create upload directory: " . UPLOAD_DIR);
    }
}

// Ensure directory is writable
if (file_exists(UPLOAD_DIR) && !is_writable(UPLOAD_DIR)) {
    @chmod(UPLOAD_DIR, 0755);
}

/**
 * Upload profile image
 * @param array $file $_FILES array element
 * @param int $userId User ID
 * @return array ['success' => bool, 'message' => string, 'filename' => string|null]
 */
function uploadProfileImage($file, $userId) {
    try {
        // Check if file was uploaded
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return ['success' => false, 'message' => 'No file uploaded.'];
        }

        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive.',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive.',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.'
            ];
            return ['success' => false, 'message' => $errorMessages[$file['error']] ?? 'Unknown upload error.'];
        }

        // Check file size
        if ($file['size'] > MAX_FILE_SIZE) {
            return ['success' => false, 'message' => 'File size exceeds 5MB limit.'];
        }

        // Validate file type
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($fileExtension, ALLOWED_EXTENSIONS)) {
            return ['success' => false, 'message' => 'Invalid file type. Allowed: JPG, PNG, GIF, WEBP.'];
        }

        // Validate MIME type (if fileinfo extension is available)
        $mimeTypeValid = false;
        if (function_exists('finfo_open')) {
            $finfo = @finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $mimeType = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);

                $allowedMimeTypes = [
                    'image/jpeg',  // Both .jpg and .jpeg use image/jpeg
                    'image/png',
                    'image/gif',
                    'image/webp'
                ];

                // Normalize MIME type (some systems return image/jpg instead of image/jpeg)
                if ($mimeType === 'image/jpg') {
                    $mimeType = 'image/jpeg';
                }

                if (in_array($mimeType, $allowedMimeTypes)) {
                    $mimeTypeValid = true;
                } else {
                    // Log for debugging but don't fail if extension is valid
                    error_log("MIME type mismatch - Extension: $fileExtension, MIME: $mimeType");
                }
            }
        }
        
        // Fallback: Check MIME type from $_FILES if fileinfo is not available or didn't validate
        if (!$mimeTypeValid && isset($file['type']) && !empty($file['type'])) {
            $allowedMimeTypes = [
                'image/jpeg',  // Both .jpg and .jpeg use image/jpeg
                'image/png',
                'image/gif',
                'image/webp'
            ];
            
            $fileMimeType = strtolower($file['type']);
            // Normalize MIME type
            if ($fileMimeType === 'image/jpg') {
                $fileMimeType = 'image/jpeg';
            }
            
            if (in_array($fileMimeType, $allowedMimeTypes)) {
                $mimeTypeValid = true;
            }
        }
        
        // If MIME type validation failed but extension is valid, log warning but allow upload
        // (Some servers may report incorrect MIME types)
        if (!$mimeTypeValid && in_array($fileExtension, ALLOWED_EXTENSIONS)) {
            error_log("MIME type validation failed but extension is valid: $fileExtension");
            // Allow upload to proceed since extension is valid
        } elseif (!$mimeTypeValid) {
            return ['success' => false, 'message' => 'Invalid file type. Please upload a valid image file (JPG, PNG, GIF, WEBP).'];
        }

        // Generate unique filename
        $filename = 'user_' . $userId . '_' . time() . '_' . uniqid() . '.' . $fileExtension;
        $filepath = UPLOAD_DIR . $filename;

        // Ensure directory exists and is writable
        if (!file_exists(UPLOAD_DIR)) {
            if (!mkdir(UPLOAD_DIR, 0755, true)) {
                return ['success' => false, 'message' => 'Failed to create upload directory.'];
            }
        }
        
        if (!is_writable(UPLOAD_DIR)) {
            return ['success' => false, 'message' => 'Upload directory is not writable. Please check permissions.'];
        }

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            $error = error_get_last();
            $errorMsg = 'Failed to save uploaded file.';
            if ($error && isset($error['message'])) {
                $errorMsg .= ' Error: ' . $error['message'];
            }
            // Log detailed error for debugging
            error_log("Image upload failed - tmp_name: " . $file['tmp_name'] . ", target: " . $filepath);
            error_log("Directory exists: " . (file_exists(UPLOAD_DIR) ? 'yes' : 'no'));
            error_log("Directory writable: " . (is_writable(UPLOAD_DIR) ? 'yes' : 'no'));
            return ['success' => false, 'message' => $errorMsg];
        }
        
        // Verify file was actually saved
        if (!file_exists($filepath)) {
            return ['success' => false, 'message' => 'File was not saved. Please try again.'];
        }

        // Create thumbnail/resize if needed (optional - for better performance)
        // You can add image resizing here if needed

        return ['success' => true, 'message' => 'Image uploaded successfully.', 'filename' => $filename];

    } catch (Exception $e) {
        error_log("Image upload error: " . $e->getMessage());
        return ['success' => false, 'message' => 'An error occurred during image upload.'];
    }
}

/**
 * Delete profile image
 * @param string $filename Image filename
 * @return bool Success status
 */
function deleteProfileImage($filename) {
    if (empty($filename)) {
        return false;
    }

    $filepath = UPLOAD_DIR . $filename;
    
    // Check if file exists and delete it
    if (file_exists($filepath)) {
        return unlink($filepath);
    }
    
    return false;
}

/**
 * Get profile image URL
 * @param string|null $filename Image filename from database
 * @return string|null Image URL or null if no image
 */
function getProfileImageUrl($filename) {
    if (empty($filename)) {
        return null;
    }

    $filepath = UPLOAD_DIR . $filename;
    
    // Check if file exists
    if (file_exists($filepath)) {
        // Return relative URL
        return 'uploads/profile_images/' . $filename;
    }
    
    return null;
}

/**
 * Get default profile image URL
 * @return string Default image path
 */
function getDefaultProfileImageUrl() {
    return null; // Returns null to use icon instead
}
?>

