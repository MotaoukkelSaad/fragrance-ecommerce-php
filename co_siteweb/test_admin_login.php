<?php
require 'config.php';

// Test 1: Check if admin exists
echo "<h2>Test 1: Check if admin exists</h2>";
$result = $conn->query("SELECT id, email, password, role FROM users WHERE email = 'admin@boutique.com'");
$user = $result->fetch_assoc();

if ($user) {
    echo "✅ Admin found!<br>";
    echo "Email: " . $user['email'] . "<br>";
    echo "Role: " . $user['role'] . "<br>";
    echo "Password Hash: " . $user['password'] . "<br>";
    
    // Test 2: Check password verification
    echo "<h2>Test 2: Password Verification</h2>";
    $password = "admin123";
    $hash = $user['password'];
    
    if (password_verify($password, $hash)) {
        echo "✅ Password verification PASSED!<br>";
    } else {
        echo "❌ Password verification FAILED!<br>";
        echo "Entered password: " . $password . "<br>";
        echo "Stored hash: " . $hash . "<br>";
    }
} else {
    echo "❌ Admin not found in database!<br>";
}

// Test 3: Generate new hash
echo "<h2>Test 3: Generate New Hash</h2>";
$new_hash = password_hash("admin123", PASSWORD_BCRYPT, ['cost' => 10]);
echo "New hash: " . $new_hash . "<br>";
echo "Verify new hash: " . (password_verify("admin123", $new_hash) ? "✅ WORKS" : "❌ FAILED") . "<br>";
?>