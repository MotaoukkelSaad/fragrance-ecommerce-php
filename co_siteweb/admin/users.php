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

// Handle DELETE user
if (isset($_GET['delete'])) {
    $user_id = (int)$_GET['delete'];
    
    // Don't allow deleting the admin
    if ($user_id == $_SESSION['user_id']) {
        $error = "❌ You cannot delete your own account!";
    } else {
        if ($conn->query("DELETE FROM users WHERE id = $user_id")) {
            $message = "✅ User deleted successfully!";
        } else {
            $error = "❌ Failed to delete user";
        }
    }
}

// Handle role change
if (isset($_POST['change_role'])) {
    $user_id = (int)$_POST['user_id'];
    $new_role = htmlspecialchars($_POST['new_role']);
    
    // Don't allow changing admin's role
    if ($user_id == $_SESSION['user_id']) {
        $error = "❌ You cannot change your own role!";
    } else {
        $stmt = $conn->prepare("UPDATE users SET role=? WHERE id=?");
        $stmt->bind_param("si", $new_role, $user_id);
        
        if ($stmt->execute()) {
            $message = "✅ User role updated successfully!";
        } else {
            $error = "❌ Failed to update user role";
        }
    }
}

// Get single user details (if viewing)
$user_detail = null;
$user_orders = [];
if (isset($_GET['id'])) {
    $user_id = (int)$_GET['id'];
    $result = $conn->query("SELECT id, name, email, role, created_at FROM users WHERE id = $user_id");
    $user_detail = $result->fetch_assoc();
    
    if ($user_detail) {
        // Get user's orders
        $result = $conn->query("SELECT id, total, status, created_at FROM orders WHERE user_id = $user_id ORDER BY created_at DESC");
        while ($order = $result->fetch_assoc()) {
            $user_orders[] = $order;
        }
    }
}

// Get all users (if not viewing single user)
$users = [];
if (!isset($_GET['id'])) {
    $result = $conn->query("SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC");
    while ($user = $result->fetch_assoc()) {
        $users[] = $user;
    }
}

// Get statistics
$stats = [];
$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'");
$stats['total_users'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
$stats['total_admins'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE user_id IN (SELECT id FROM users WHERE role = 'user')");
$stats['total_orders'] = $result->fetch_assoc()['count'];

$current_user = Auth::getCurrentUser();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management — Admin</title>
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
        .users-table { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 30px; }
        .users-table h2 { margin: 0 0 20px 0; color: #1a1a2e; }
        table { width: 100%; border-collapse: collapse; }
        table th, table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        table th { background: #f5f5f5; font-weight: 600; color: #1a1a2e; }
        table tr:hover { background: #f9f9f9; }
        .badge { display: inline-block; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge-user { background: #e3f2fd; color: #1565c0; }
        .badge-admin { background: #fff3e0; color: #e65100; }
        .btn-view { background: #3498db; color: white; padding: 6px 12px; text-decoration: none; border-radius: 4px; font-size: 12px; }
        .btn-view:hover { background: #2980b9; }
        .btn-delete { background: #e74c3c; color: white; padding: 6px 12px; border: none; border-radius: 4px; font-size: 12px; cursor: pointer; }
        .btn-delete:hover { background: #c0392b; }
        .back-link { margin-bottom: 20px; }
        .back-link a { color: #3498db; text-decoration: none; font-weight: 600; }
        .back-link a:hover { text-decoration: underline; }
        .user-detail-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .user-detail-card h3 { margin: 0 0 15px 0; color: #1a1a2e; border-bottom: 2px solid #e94560; padding-bottom: 10px; }
        .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .detail-grid.full { grid-template-columns: 1fr; }
        .detail-row { margin-bottom: 15px; }
        .detail-row label { font-weight: 600; color: #1a1a2e; display: block; margin-bottom: 5px; }
        .detail-row span { color: #666; }
        .role-form { display: flex; gap: 10px; align-items: center; }
        .role-form select { padding: 8px 12px; border: 2px solid #ddd; border-radius: 4px; }
        .role-form button { padding: 8px 16px; background: #27ae60; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600; }
        .role-form button:hover { background: #229954; }
        .orders-section { background: #f9f9f9; border-radius: 4px; overflow: hidden; }
        .orders-section table { margin-bottom: 0; }
        .no-orders { color: #999; text-align: center; padding: 20px; }
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
            <li><a href="users.php" class="active">👥 Users</a></li>
            <li><a href="settings.php">⚙️ Settings</a></li>
            <li><a href="../logout.php" style="color: #e94560;">🚪 Logout</a></li>
        </ul>
    </div>

    <!-- MAIN CONTENT -->
    <div class="admin-main">
        <!-- HEADER -->
        <div class="admin-header">
            <h1>User Management</h1>
            <div class="admin-user">
                <div class="name">👤 <?php echo htmlspecialchars($current_user['name']); ?></div>
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

        <?php if (isset($_GET['id']) && $user_detail): ?>
            <!-- USER DETAILS VIEW -->
            <div class="back-link">
                <a href="users.php">← Back to Users</a>
            </div>

            <!-- USER INFO -->
            <div class="user-detail-card">
                <h3>👤 User #<?php echo $user_detail['id']; ?></h3>
                <div class="detail-grid">
                    <div class="detail-row">
                        <label>Name</label>
                        <span><?php echo htmlspecialchars($user_detail['name']); ?></span>
                    </div>
                    <div class="detail-row">
                        <label>Email</label>
                        <span><?php echo htmlspecialchars($user_detail['email']); ?></span>
                    </div>
                    <div class="detail-row">
                        <label>Member Since</label>
                        <span><?php echo date('d/m/Y', strtotime($user_detail['created_at'])); ?></span>
                    </div>
                    <div class="detail-row">
                        <label>Role</label>
                        <form method="POST" class="role-form">
                            <input type="hidden" name="user_id" value="<?php echo $user_detail['id']; ?>">
                            <select name="new_role">
                                <option value="user" <?php echo $user_detail['role'] == 'user' ? 'selected' : ''; ?>>User</option>
                                <option value="admin" <?php echo $user_detail['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                            </select>
                            <button type="submit" name="change_role">Update</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- USER ORDERS -->
            <div class="user-detail-card">
                <h3>📋 User Orders (<?php echo count($user_orders); ?>)</h3>
                <?php if (!empty($user_orders)): ?>
                    <div class="orders-section">
                        <table>
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($user_orders as $order): ?>
                                <tr>
                                    <td><strong>#<?php echo $order['id']; ?></strong></td>
                                    <td>MAD <?php echo number_format($order['total'], 2); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo strtolower($order['status']); ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                    <td><a href="orders.php?id=<?php echo $order['id']; ?>" class="btn-view">View</a></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="no-orders">This user has no orders yet</p>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <!-- STATISTICS -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Users</h3>
                    <div class="value"><?php echo $stats['total_users']; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Total Admins</h3>
                    <div class="value"><?php echo $stats['total_admins']; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Total Orders</h3>
                    <div class="value"><?php echo $stats['total_orders']; ?></div>
                </div>
            </div>

            <!-- USERS LIST VIEW -->
            <div class="users-table">
                <h2>All Users (<?php echo count($users); ?>)</h2>
                <?php if (!empty($users)): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Member Since</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><strong>#<?php echo $user['id']; ?></strong></td>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo strtolower($user['role']); ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <a href="users.php?id=<?php echo $user['id']; ?>" class="btn-view">👁️ View</a>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <button onclick="if(confirm('Delete this user?')) window.location='users.php?delete=<?php echo $user['id']; ?>';" class="btn-delete">🗑️ Delete</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="color: #999; text-align: center; padding: 20px;">No users found</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>