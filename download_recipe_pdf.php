<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Clean any existing output buffers completely
while (ob_get_level()) {
    ob_end_clean();
}

// Get recipe ID from URL
$recipeId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($recipeId <= 0) {
    header('Location: recipes.php');
    exit;
}

// Check if fpdf.php exists and is readable
$fpdfPath = __DIR__ . '/fpdf.php';
if (!file_exists($fpdfPath)) {
    header('Content-Type: text/html; charset=UTF-8');
    die('FPDF library not found. Please ensure fpdf.php is in the same directory.');
}
if (!is_readable($fpdfPath)) {
    header('Content-Type: text/html; charset=UTF-8');
    die('FPDF library file is not readable. Please check file permissions.');
}

// Check if pdo.php exists and is readable
$pdoPath = __DIR__ . '/pdo.php';
if (!file_exists($pdoPath)) {
    header('Content-Type: text/html; charset=UTF-8');
    die('Database connection file not found.');
}
if (!is_readable($pdoPath)) {
    header('Content-Type: text/html; charset=UTF-8');
    die('Database connection file is not readable. Please check file permissions.');
}

// Include files - capture any potential output
ob_start();
require_once $fpdfPath;
require_once $pdoPath;
$includedOutput = ob_get_clean();

// Clean any whitespace or BOM from includes
$includedOutput = preg_replace('/^(\xEF\xBB\xBF)?\s*/', '', $includedOutput);

// If there's any non-whitespace output from includes, it will cause FPDF to fail
if (!empty($includedOutput)) {
    error_log("Unexpected output from includes (length: " . strlen($includedOutput) . "): " . substr($includedOutput, 0, 100));
    // Clean it anyway
    while (ob_get_level()) {
        ob_end_clean();
    }
}

// Function to convert UTF-8 to ISO-8859-1 for FPDF compatibility
function convertToPDFText($text) {
    if (empty($text)) {
        return '';
    }
    // Convert UTF-8 to ISO-8859-1 for FPDF
    if (function_exists('iconv')) {
        $result = @iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $text);
        return $result !== false ? $result : utf8_decode($text);
    }
    return utf8_decode($text);
}

try {
    // Fetch recipe details
    $recipeStmt = $pdo->prepare("
        SELECT r.recipe_id, r.title, r.description, r.cook_time, r.difficulty, r.image_url, 
               r.created_at, r.category_id, r.did,
               c.name as category_name, d.name as dietary_name,
               u.firstname, u.lastname
        FROM recipes r
        LEFT JOIN categories c ON r.category_id = c.category_id
        LEFT JOIN dietary d ON r.did = d.did
        LEFT JOIN users u ON r.user_id = u.user_id
        WHERE r.recipe_id = ?
    ");
    $recipeStmt->execute([$recipeId]);
    $recipe = $recipeStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$recipe) {
        header('Location: recipes.php');
        exit;
    }
    
    // Fetch ingredients
    $ingredientsStmt = $pdo->prepare("
        SELECT i.name as ingredient_name, ri.quantity
        FROM recipe_ingredients ri
        INNER JOIN ingredients i ON ri.ingredient_id = i.ingredient_id
        WHERE ri.recipe_id = ?
        ORDER BY ri.recipeIngre_id ASC
    ");
    $ingredientsStmt->execute([$recipeId]);
    $ingredients = $ingredientsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fetch instructions
    $instructionsStmt = $pdo->prepare("
        SELECT instruction, instruction_id
        FROM instructions
        WHERE recipe_id = ?
        ORDER BY instruction_id ASC
    ");
    $instructionsStmt->execute([$recipeId]);
    $instructions = $instructionsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Prepare data - convert to ISO-8859-1 for FPDF compatibility
    $title = convertToPDFText($recipe['title'] ?? 'Untitled Recipe');
    $description = convertToPDFText($recipe['description'] ?? 'No description available.');
    $cookTime = $recipe['cook_time'] ?? 0;
    $difficulty = convertToPDFText($recipe['difficulty'] ?? 'Medium');
    $categoryName = convertToPDFText($recipe['category_name'] ?? 'Uncategorized');
    $dietaryName = convertToPDFText($recipe['dietary_name'] ?? '');
    $authorName = trim(($recipe['firstname'] ?? '') . ' ' . ($recipe['lastname'] ?? ''));
    $authorName = convertToPDFText($authorName);
    if (empty($authorName) || trim($authorName) === '') {
        $authorName = 'FoodFusion Community';
    }
    
    // Validate essential data
    if (empty($title)) {
        throw new Exception('Recipe title is missing.');
    }
    
    // Create PDF
    $pdf = new FPDF('P', 'mm', 'A4');
    $pdf->SetAutoPageBreak(true, 15);
    $pdf->AddPage();
    
    // Set colors
    $pdf->SetFillColor(255, 193, 7); // Yellow
    $pdf->SetTextColor(10, 17, 40);  // Dark blue
    
    // Header
    $pdf->SetFont('Helvetica', 'B', 24);
    $pdf->SetFillColor(255, 193, 7);
    $pdf->Rect(0, 0, 210, 30, 'F');
    $pdf->SetTextColor(10, 17, 40);
    $pdf->SetXY(10, 10);
    $pdf->Cell(190, 10, 'FoodFusion Recipe Card', 0, 1, 'C');
    
    // Recipe Image (if available)
    $imageUrl = $recipe['image_url'] ?? '';
    $yPos = 35;
    if (!empty($imageUrl)) {
        // Convert relative path to absolute path
        $imagePath = $imageUrl;
        
        // If path doesn't start with /, it's relative to the script directory
        if (strpos($imagePath, '/') !== 0) {
            $imagePath = __DIR__ . '/' . $imageUrl;
        }
        
        // Normalize path separators
        $imagePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $imagePath);
        
        // Check if image file exists and is readable
        if (file_exists($imagePath) && is_readable($imagePath)) {
            try {
                // Get image dimensions
                $imageInfo = @getimagesize($imagePath);
                if ($imageInfo !== false) {
                    $imgWidth = $imageInfo[0];
                    $imgHeight = $imageInfo[1];
                    
                    // Calculate size to fit in PDF (max width 190mm, max height 60mm, maintain aspect ratio)
                    // Convert pixels to mm (1 inch = 25.4mm, 72 DPI = 1px = 0.352778mm, but we'll use getimagesize which gives pixels)
                    // For 96 DPI: 1px = 0.264583mm
                    $maxWidth = 190;  // mm
                    $maxHeight = 60;   // mm
                    
                    // Calculate ratio to fit within max dimensions
                    $widthRatio = $maxWidth / ($imgWidth * 0.264583);
                    $heightRatio = $maxHeight / ($imgHeight * 0.264583);
                    $ratio = min($widthRatio, $heightRatio, 1.0); // Don't enlarge, only shrink
                    
                    $displayWidth = $imgWidth * 0.264583 * $ratio;
                    $displayHeight = $imgHeight * 0.264583 * $ratio;
                    
                    // Center the image horizontally
                    $xPos = (210 - $displayWidth) / 2;
                    
                    // Add image to PDF
                    $pdf->Image($imagePath, $xPos, $yPos, $displayWidth, $displayHeight);
                    
                    // Update Y position after image (images don't automatically advance Y position)
                    $yPos = $yPos + $displayHeight + 10;
                }
            } catch (Exception $e) {
                error_log("Error adding image to PDF: " . $e->getMessage());
                // Continue without image if there's an error
            }
        } else {
            error_log("Recipe image not found or not readable: " . $imagePath);
        }
    }
    
    // Recipe Title
    $pdf->SetFont('Helvetica', 'B', 20);
    $pdf->SetTextColor(10, 17, 40);
    $pdf->SetXY(10, $yPos);
    $pdf->MultiCell(190, 10, $title, 0, 'C');
    
    // Description
    $pdf->SetFont('Helvetica', '', 11);
    $pdf->SetTextColor(100, 100, 100);
    $yPos = $pdf->GetY() + 5;
    $pdf->SetXY(10, $yPos);
    $pdf->MultiCell(190, 6, $description, 0, 'C');
    
    // Recipe Info Box
    $yPos = $pdf->GetY() + 10;
    $pdf->SetXY(10, $yPos);
    $pdf->SetFillColor(248, 249, 250);
    $pdf->Rect(10, $yPos, 190, 25, 'F');
    
    // Row 1: Cooking Time and Difficulty
    $row1Y = $yPos + 5;
    
    // Cooking Time
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->SetTextColor(10, 17, 40);
    $pdf->SetXY(15, $row1Y);
    $pdf->Cell(45, 5, 'Cooking Time:', 0, 0, 'L');
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->SetXY(60, $row1Y);
    $pdf->Cell(50, 5, $cookTime . ' minutes', 0, 0, 'L');
    
    // Difficulty
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->SetTextColor(10, 17, 40);
    $pdf->SetXY(120, $row1Y);
    $pdf->Cell(40, 5, 'Difficulty:', 0, 0, 'L');
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->SetXY(160, $row1Y);
    $pdf->Cell(40, 5, $difficulty, 0, 0, 'L');
    
    // Row 2: Category and Dietary
    $row2Y = $yPos + 13;
    
    // Category
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->SetTextColor(10, 17, 40);
    $pdf->SetXY(15, $row2Y);
    $pdf->Cell(45, 5, 'Category:', 0, 0, 'L');
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->SetXY(60, $row2Y);
    $pdf->Cell(50, 5, $categoryName, 0, 0, 'L');
    
    // Dietary (if available)
    if (!empty($dietaryName)) {
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->SetTextColor(10, 17, 40);
        $pdf->SetXY(120, $row2Y);
        $pdf->Cell(40, 5, 'Dietary:', 0, 0, 'L');
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetTextColor(40, 167, 69);
        $pdf->SetXY(160, $row2Y);
        $pdf->Cell(40, 5, $dietaryName, 0, 0, 'L');
    }
    
    // Ingredients Section
    $yPos = $pdf->GetY() + 15;
    $pdf->SetXY(10, $yPos);
    $pdf->SetFillColor(255, 193, 7);
    $pdf->SetTextColor(10, 17, 40);
    $pdf->SetFont('Helvetica', 'B', 16);
    $pdf->Cell(190, 8, 'Ingredients', 0, 1, 'L', true);
    
    $yPos = $pdf->GetY() + 3;
    $pdf->SetFont('Helvetica', '', 11);
    $pdf->SetTextColor(10, 17, 40);
    
    foreach ($ingredients as $ingredient) {
        $pdf->SetXY(15, $yPos);
        $pdf->SetFillColor(248, 249, 250);
        $pdf->Rect(15, $yPos - 2, 180, 8, 'F');
        
        // Quantity in bold
        $pdf->SetFont('Helvetica', 'B', 11);
        $pdf->SetTextColor(10, 17, 40);
        $pdf->Cell(60, 6, convertToPDFText($ingredient['quantity']), 0, 0, 'L');
        
        // Ingredient name
        $pdf->SetFont('Helvetica', '', 11);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(120, 6, convertToPDFText($ingredient['ingredient_name']), 0, 1, 'L');
        
        $yPos = $pdf->GetY() + 2;
        
        // Check if we need a new page
        if ($yPos > 250) {
            $pdf->AddPage();
            $yPos = 20;
        }
    }
    
    // Instructions Section
    $yPos = $pdf->GetY() + 10;
    $pdf->SetXY(10, $yPos);
    $pdf->SetFillColor(255, 193, 7);
    $pdf->SetTextColor(10, 17, 40);
    $pdf->SetFont('Helvetica', 'B', 16);
    $pdf->Cell(190, 8, 'Instructions', 0, 1, 'L', true);
    
    $yPos = $pdf->GetY() + 3;
    $pdf->SetFont('Helvetica', '', 11);
    $pdf->SetTextColor(10, 17, 40);
    
    $stepNumber = 1;
    foreach ($instructions as $instruction) {
        // Check if we need a new page
        if ($yPos > 250) {
            $pdf->AddPage();
            $yPos = 20;
        }
        
        // Step number circle
        $pdf->SetXY(15, $yPos);
        $pdf->SetFillColor(255, 193, 7);
        $pdf->SetTextColor(10, 17, 40);
        $pdf->SetFont('Helvetica', 'B', 12);
        $pdf->Rect(15, $yPos - 2, 8, 8, 'F');
        $pdf->SetXY(17, $yPos);
        $pdf->Cell(6, 6, $stepNumber, 0, 0, 'C');
        
        // Instruction text with background
        $startY = $yPos;
        $instructionText = convertToPDFText($instruction['instruction']);
        
        // Draw background rectangle first (estimate height)
        $pdf->SetFillColor(248, 249, 250);
        $estimatedHeight = max(8, (strlen($instructionText) / 60) * 6 + 4); // Rough estimate
        $pdf->Rect(28, $startY - 2, 167, $estimatedHeight, 'F');
        
        // Draw text
        $pdf->SetXY(28, $startY);
        $pdf->SetFont('Helvetica', '', 11);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->MultiCell(167, 6, $instructionText, 0, 'L');
        
        $yPos = $pdf->GetY() + 5;
        $stepNumber++;
    }
    
    // Footer
    $pdf->SetY(-20);
    $pdf->SetFont('Helvetica', 'I', 9);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell(190, 5, 'Recipe by: ' . $authorName, 0, 1, 'C');
    $pdf->Cell(190, 5, 'Generated from FoodFusion - Your Culinary Community', 0, 1, 'C');
    
    // Clean output buffer before PDF output
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    // Output PDF - force download
    $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $title) . '_recipe.pdf';
    
    // Validate filename
    if (empty($filename) || $filename === '_recipe.pdf') {
        $filename = 'recipe_' . $recipeId . '.pdf';
    }
    
    // Use FPDF's built-in download method
    // 'D' = send to browser and force download with name $filename
    // Output() automatically closes the document and sends headers
    try {
        $pdf->Output('D', $filename);
    } catch (Exception $e) {
        // If Output fails, try alternative method
        error_log("FPDF Output failed: " . $e->getMessage());
        throw new Exception('Failed to output PDF: ' . $e->getMessage());
    }
    
    // Exit to prevent any additional output
    exit;
    
} catch (PDOException $e) {
    // Clean any output
    if (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    // Log error
    error_log("Database Error in PDF generation: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Show user-friendly error message
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!DOCTYPE html><html><head><title>Error</title></head><body>';
    echo '<h1>Error Generating PDF</h1>';
    echo '<p>There was an error retrieving recipe data. Please try again later.</p>';
    echo '<p><strong>Error details:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p><a href="recipe_detail.php?id=' . $recipeId . '">Go back to recipe</a></p>';
    echo '</body></html>';
    exit;
} catch (Exception $e) {
    // Clean any output
    if (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    // Log error
    error_log("FPDF Error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Show user-friendly error message
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!DOCTYPE html><html><head><title>Error</title></head><body>';
    echo '<h1>Error Generating PDF</h1>';
    echo '<p>There was an error generating the PDF file. Please try again later.</p>';
    echo '<p><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p><a href="recipe_detail.php?id=' . $recipeId . '">Go back to recipe</a></p>';
    echo '</body></html>';
    exit;
} catch (Error $e) {
    // Clean any output
    if (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    // Catch PHP 7+ errors
    error_log("PHP Error in PDF generation: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Show user-friendly error message
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!DOCTYPE html><html><head><title>Error</title></head><body>';
    echo '<h1>Error Generating PDF</h1>';
    echo '<p>There was an error generating the PDF file. Please try again later.</p>';
    echo '<p><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p><a href="recipe_detail.php?id=' . $recipeId . '">Go back to recipe</a></p>';
    echo '</body></html>';
    exit;
}
?>
