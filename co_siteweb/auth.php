<?php
// =====================================================
// AUTHENTICATION CLASS
// =====================================================

class Auth {
    private $conn;

    public function __construct($db) {
        if ($db === null) {
            die("❌ Database connection is null!");
        }
        $this->conn = $db;
    }

    // Register new user
    public function register($name, $email, $password) {
        // Validate inputs
        if (empty($name) || empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'All fields are required'];
        }

        // Check if email exists
        $check = $this->conn->prepare("SELECT id FROM users WHERE email = ?");
        if (!$check) {
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $check->bind_param("s", $email);
        $check->execute();
        
        if ($check->get_result()->num_rows > 0) {
            return ['success' => false, 'message' => 'Email already registered'];
        }

        // Hash password with Argon2id (PHP 7.2+)
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        
        // Insert user
        $stmt = $this->conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("sss", $name, $email, $hashed);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Registration successful! Please login.'];
        } else {
            return ['success' => false, 'message' => 'Registration failed'];
        }
    }

    // Login user
    public function login($email, $password) {
        $stmt = $this->conn->prepare("SELECT id, name, email, role, password FROM users WHERE email = ?");
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            return ['success' => false, 'message' => 'Email not found'];
        }

        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            return ['success' => true, 'message' => 'Login successful'];
        } else {
            return ['success' => false, 'message' => 'Incorrect password'];
        }
    }

    // Logout
    public function logout() {
        session_destroy();
        return true;
    }

    // Static methods
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public static function isAdmin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }

    public static function getCurrentUser() {
        if (self::isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'name' => $_SESSION['name'],
                'email' => $_SESSION['email'],
                'role' => $_SESSION['role']
            ];
        }
        return null;
    }
}

?>