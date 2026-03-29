<?php
session_start();
require '../config.php';
require '../auth.php';

// Check if admin is logged in
if (!Auth::isLoggedIn() || !Auth::isAdmin()) {
    header('Location: ../login.php');
    exit();
}

$message = '';
$error = '';

// Create settings table if it doesn't exist
$conn->query("CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `setting_key` VARCHAR(100) UNIQUE NOT NULL,
    `setting_value` TEXT,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

// Get current settings
$settings = [];
$result = $conn->query("SELECT setting_key, setting_value FROM settings");
while ($row = $result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Set defaults if not exist
$defaults = [
    'store_name' => 'FragranceBoutique',
    'store_email' => 'info@fragrance.com',
    'store_phone' => '+212 5XX-XXXXXX',
    'store_address' => 'Rabat, Morocco',
    'shipping_free_threshold' => '500',
    'express_shipping_cost' => '35',
    'store_description' => 'Your premium fragrance destination',
    'currency' => 'MAD',
    'maintenance_mode' => 'off'
];

foreach ($defaults as $key => $value) {
    if (!isset($settings[$key])) {
        $settings[$key] = $value;
    }
}

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($defaults as $key => $value) {
        if (isset($_POST[$key])) {
            $setting_value = htmlspecialchars(trim($_POST[$key]));
            
            // Check if setting exists
            $check = $conn->query("SELECT id FROM settings WHERE setting_key = '$key'");
            
            if ($check->num_rows > 0) {
                // Update
                $conn->query("UPDATE settings SET setting_value = '$setting_value' WHERE setting_key = '$key'");
            } else {
                // Insert
                $conn->query("INSERT INTO settings (setting_key, setting_value) VALUES ('$key', '$setting_value')");
            }
        }
    }
    $message = "✅ Settings updated successfully!";
    
    // Reload settings
    $settings = [];
    $result = $conn->query("SELECT setting_key, setting_value FROM settings");
    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}

// Get database statistics
$stats = [];
$result = $conn->query("SELECT COUNT(*) as count FROM products");
$stats['total_products'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM users");
$stats['total_users'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM orders");
$stats['total_orders'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT SUM(total) as total FROM orders");
$revenue = $result->fetch_assoc()['total'];
$stats['total_revenue'] = $revenue ?? 0;

$user = Auth::getCurrentUser();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings — Admin</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        body { background: #f5f5f5; }
        .admin-wrapper { display: flex; min-height: 100vh; }
        .admin-sidebar { width: 250px; background: #1a1a2e; color: white; padding: 20px; position: fixed; height: 100vh; overflow-y: auto; }
        .admin-main { margin-left: 250px; flex: 1; padding: 30px; }
        .admin-header { background: white; padding: 20px; border-radius: 8px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .admin-header h1 { margin: 0; font-size: 24px; color: #1a1a2e; }
        .admin-user { text-align: right; }
        .admin-user .name { font-weight: 600; color: #1a1a2e; }
        .admin-user .logout { color: #e94560; text-decoration: none; margin-top: 5px; display: inline-block; font-size: 12px; }
        .sidebar-menu { list-style: none; padding: 0; margin: 20px 0; }
        .sidebar-menu li { margin-bottom: 10px; }
        .sidebar-menu a { color: #ccc; text-decoration: none; padding: 12px 15px; display: block; border-radius: 4px; transition: all 0.3s; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background: #e94560; color: white; }
        .sidebar-logo { font-size: 18px; font-weight: 700; margin-bottom: 30px; color: #e94560; }
        .message { padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .message.success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .message.error { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-left: 4px solid #e94560; }
        .stat-card h3 { margin: 0 0 10px 0; color: #666; font-size: 14px; text-transform: uppercase; }
        .stat-card .value { font-size: 28px; font-weight: 700; color: #1a1a2e; }
        .settings-section { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 30px; }
        .settings-section h2 { margin: 0 0 20px 0; color: #1a1a2e; border-bottom: 2px solid #e94560; padding-bottom: 10px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .form-grid.full { grid-template-columns: 1fr; }
        .form-group { margin-bottom: 0; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #1a1a2e; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 6px; font-size: 14px; font-family: inherit; box-sizing: border-box; }
        .form-group input:focus, .form-group textarea:focus, .form-group select:focus { outline: none; border-color: #e94560; }
        .form-group textarea { resize: vertical; min-height: 100px; }
        .form-actions { display: flex; gap: 10px; margin-top: 20px; }
        .btn-save { background: #27ae60; color: white; padding: 12px 30px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 16px; }
        .btn-save:hover { background: #229954; }
        .btn-reset { background: #95a5a6; color: white; padding: 12px 30px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 16px; }
        .btn-reset:hover { background: #7f8c8d; }
        .setting-info { background: #e8f4f8; padding: 10px; border-radius: 4px; margin-top: 5px; font-size: 12px; color: #0c5460; border-left: 3px solid #17a2b8; }
        .toggle-switch { display: flex; align-items: center; gap: 10px; }
        .toggle-switch input[type="checkbox"] { width: 40px; height: 20px; cursor: pointer; }
    </style>
</head>
<body>

<div class="admin-wrapper">
    <!-- SIDEBAR -->
    <div class="admin-sidebar">
        <div class="sidebar-logo">🏢 Admin Panel</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php">📊 Dashboard</a></li>
            <li><a href="products.php">📦 Products</a></li>
            <li><a href="orders.php">📋 Orders</a></li>
            <li><a href="users.php">👥 Users</a></li>
            <li><a href="settings.php" class="active">⚙️ Settings</a></li>
            <li><a href="../logout.php" style="color: #e94560;">🚪 Logout</a></li>
        </ul>
    </div>

    <!-- MAIN CONTENT -->
    <div class="admin-main">
        <!-- HEADER -->
        <div class="admin-header">
            <h1>Settings</h1>
            <div class="admin-user">
                <div class="name">👤 <?php echo htmlspecialchars($user['name']); ?></div>
                <a href="../logout.php" class="logout">Logout</a>
            </div>
        </div>

        <!-- MESSAGES -->
        <?php if ($message): ?>
            <div class="message success"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- STATISTICS -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Products</h3>
                <div class="value"><?php echo $stats['total_products']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Users</h3>
                <div class="value"><?php echo $stats['total_users']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Orders</h3>
                <div class="value"><?php echo $stats['total_orders']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Revenue</h3>
                <div class="value">MAD <?php echo number_format($stats['total_revenue'], 2); ?></div>
            </div>
        </div>

        <!-- SETTINGS FORM -->
        <form method="POST" action="settings.php">
            <!-- STORE SETTINGS -->
            <div class="settings-section">
                <h2>🏪 Store Information</h2>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="store_name">Store Name</label>
                        <input type="text" id="store_name" name="store_name" value="<?php echo htmlspecialchars($settings['store_name']); ?>" required>
                        <div class="setting-info">The name of your store displayed to customers</div>
                    </div>

                    <div class="form-group">
                        <label for="currency">Currency</label>
                        <input type="text" id="currency" name="currency" value="<?php echo htmlspecialchars($settings['currency']); ?>" required>
                        <div class="setting-info">Currency code (e.g., MAD, USD, EUR)</div>
                    </div>
                </div>

                <div class="form-grid full">
                    <div class="form-group">
                        <label for="store_description">Store Description</label>
                        <textarea id="store_description" name="store_description"><?php echo htmlspecialchars($settings['store_description']); ?></textarea>
                        <div class="setting-info">Brief description of your store</div>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="store_email">Store Email</label>
                        <input type="email" id="store_email" name="store_email" value="<?php echo htmlspecialchars($settings['store_email']); ?>" required>
                        <div class="setting-info">Contact email for customers</div>
                    </div>

                    <div class="form-group">
                        <label for="store_phone">Store Phone</label>
                        <input type="text" id="store_phone" name="store_phone" value="<?php echo htmlspecialchars($settings['store_phone']); ?>" required>
                        <div class="setting-info">Contact phone number</div>
                    </div>
                </div>

                <div class="form-grid full">
                    <div class="form-group">
                        <label for="store_address">Store Address</label>
                        <input type="text" id="store_address" name="store_address" value="<?php echo htmlspecialchars($settings['store_address']); ?>" required>
                        <div class="setting-info">Physical address of your store</div>
                    </div>
                </div>
            </div>

            <!-- SHIPPING SETTINGS -->
            <div class="settings-section">
                <h2>📦 Shipping Settings</h2>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="shipping_free_threshold">Free Shipping Threshold (MAD)</label>
                        <input type="number" id="shipping_free_threshold" name="shipping_free_threshold" value="<?php echo htmlspecialchars($settings['shipping_free_threshold']); ?>" step="0.01" required>
                        <div class="setting-info">Customers get free shipping on orders above this amount</div>
                    </div>

                    <div class="form-group">
                        <label for="express_shipping_cost">Express Shipping Cost (MAD)</label>
                        <input type="number" id="express_shipping_cost" name="express_shipping_cost" value="<?php echo htmlspecialchars($settings['express_shipping_cost']); ?>" step="0.01" required>
                        <div class="setting-info">Cost of express/premium shipping option</div>
                    </div>
                </div>
            </div>

            <!-- FORM ACTIONS -->
            <div class="form-actions">
                <button type="submit" class="btn-save">💾 Save Settings</button>
                <button type="reset" class="btn-reset">↺ Reset Form</button>
            </div>
        </form>

        <!-- DATABASE INFO -->
        <div class="settings-section">
            <h2>📊 Database Information</h2>
            <div class="detail-row" style="padding: 15px; background: #f5f5f5; border-radius: 4px; margin-bottom: 10px;">
                <strong>Server:</strong> <?php echo htmlspecialchars($_SERVER['SERVER_SOFTWARE']); ?>
            </div>
            <div class="detail-row" style="padding: 15px; background: #f5f5f5; border-radius: 4px; margin-bottom: 10px;">
                <strong>PHP Version:</strong> <?php echo phpversion(); ?>
            </div>
            <div class="detail-row" style="padding: 15px; background: #f5f5f5; border-radius: 4px;">
                <strong>Current Date/Time:</strong> <?php echo date('d/m/Y H:i:s'); ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>