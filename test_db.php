<?php
/**
 * Test Database Connection and Users Table
 * Run this file in your browser to verify database setup
 */
require 'pdo.php';

echo "<h2>Database Connection Test</h2>";

try {
    // Test 1: Check if users table exists
    echo "<h3>Test 1: Check if 'users' table exists</h3>";
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    $tableExists = $stmt->rowCount() > 0;
    
    if ($tableExists) {
        echo "<p style='color: green;'>✓ Users table exists</p>";
    } else {
        echo "<p style='color: red;'>✗ Users table does NOT exist</p>";
        echo "<p>Please create the table using the SQL provided.</p>";
        exit;
    }
    
    // Test 2: Check table structure
    echo "<h3>Test 2: Check table structure</h3>";
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($column['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Default'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($column['Extra']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Test 3: Try to insert a test record
    echo "<h3>Test 3: Test INSERT operation</h3>";
    $testEmail = 'test_' . time() . '@example.com';
    $testPassword = password_hash('testpassword123', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO users (firstname, lastname, email, password) VALUES (?, ?, ?, ?)");
    $result = $stmt->execute(['Test', 'User', $testEmail, $testPassword]);
    
    if ($result) {
        $userId = $pdo->lastInsertId();
        echo "<p style='color: green;'>✓ Test insert successful! User ID: " . $userId . "</p>";
        
        // Verify the insert
        $verifyStmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
        $verifyStmt->execute([$userId]);
        $user = $verifyStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            echo "<p style='color: green;'>✓ User verified in database:</p>";
            echo "<pre>" . print_r($user, true) . "</pre>";
            
            // Clean up test record
            $deleteStmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
            $deleteStmt->execute([$userId]);
            echo "<p style='color: blue;'>Test record cleaned up.</p>";
        } else {
            echo "<p style='color: red;'>✗ User was NOT found after insertion!</p>";
        }
    } else {
        $errorInfo = $stmt->errorInfo();
        echo "<p style='color: red;'>✗ Test insert failed: " . htmlspecialchars($errorInfo[2]) . "</p>";
    }
    
    // Test 4: Check current user count
    echo "<h3>Test 4: Current users in database</h3>";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Total users in database: <strong>" . $count['count'] . "</strong></p>";
    
    echo "<h3 style='color: green;'>All tests completed!</h3>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Database Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Error Code: " . $e->getCode() . "</p>";
}
?>

