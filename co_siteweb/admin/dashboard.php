<?php
session_start();
require '../config.php';
require '../auth.php';

// Check if admin is logged in
if (!Auth::isLoggedIn() || !Auth::isAdmin()) {
    header('Location: ../login.php');
    exit();
}

// Get statistics
$stats = [];

// Total orders
$result = $conn->query("SELECT COUNT(*) as count FROM orders");
$stats['total_orders'] = $result->fetch_assoc()['count'];

// Total users
$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'");
$stats['total_users'] = $result->fetch_assoc()['count'];

// Total revenue
$result = $conn->query("SELECT SUM(total) as total FROM orders");
$revenue = $result->fetch_assoc()['total'];
$stats['total_revenue'] = $revenue ?? 0;

// Recent orders (last 5)
$recent_orders = [];
$result = $conn->query("SELECT id, user_id, total, status, created_at FROM orders ORDER BY created_at DESC LIMIT 5");
while ($order = $result->fetch_assoc()) {
    $recent_orders[] = $order;
}

$user = Auth::getCurrentUser();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard — FragranceBoutique</title>
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
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-left: 4px solid #e94560; }
        .stat-card h3 { margin: 0 0 10px 0; color: #666; font-size: 14px; text-transform: uppercase; }
        .stat-card .value { font-size: 28px; font-weight: 700; color: #1a1a2e; }
        .table-section { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .table-section h2 { margin: 0 0 20px 0; color: #1a1a2e; font-size: 18px; }
        table { width: 100%; border-collapse: collapse; }
        table th, table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        table th { background: #f5f5f5; font-weight: 600; color: #1a1a2e; }
        table tr:hover { background: #f9f9f9; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge-pending { background: #fff3cd; color: #856404; }
        .badge-completed { background: #d4edda; color: #155724; }
        .badge-cancelled { background: #f8d7da; color: #721c24; }
        .btn-small { padding: 6px 12px; font-size: 12px; text-decoration: none; display: inline-block; border-radius: 4px; }
        .btn-view { background: #667eea; color: white; }
        .btn-view:hover { background: #5568d3; }
    </style>
</head>
<body>

<div class="admin-wrapper">
    <!-- SIDEBAR -->
    <div class="admin-sidebar">
        <div class="sidebar-logo">🏢 Admin Panel</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php" class="active">📊 Dashboard</a></li>
            <li><a href="products.php">📦 Products</a></li>
            <li><a href="orders.php">📋 Orders</a></li>
            <li><a href="users.php">👥 Users</a></li>
            <li><a href="settings.php">⚙️ Settings</a></li>
            <li><a href="../logout.php" style="color: #e94560;">🚪 Logout</a></li>
        </ul>
    </div>

    <!-- MAIN CONTENT -->
    <div class="admin-main">
        <!-- HEADER -->
        <div class="admin-header">
            <h1>Dashboard</h1>
            <div class="admin-user">
                <div class="name">👤 <?php echo htmlspecialchars($user['name']); ?></div>
                <a href="../logout.php" class="logout">Logout</a>
            </div>
        </div>

        <!-- STATISTICS CARDS -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Orders</h3>
                <div class="value"><?php echo $stats['total_orders']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Users</h3>
                <div class="value"><?php echo $stats['total_users']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Revenue</h3>
                <div class="value">MAD <?php echo number_format($stats['total_revenue'], 2); ?></div>
            </div>
        </div>

        <!-- RECENT ORDERS -->
        <div class="table-section">
            <h2>Recent Orders</h2>
            <?php if (!empty($recent_orders)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>User ID</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_orders as $order): ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td><?php echo $order['user_id']; ?></td>
                            <td><strong>MAD <?php echo number_format($order['total'], 2); ?></strong></td>
                            <td>
                                <span class="badge badge-<?php echo strtolower($order['status']); ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                            <td><a href="orders.php?id=<?php echo $order['id']; ?>" class="btn-small btn-view">View</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color: #999; text-align: center; padding: 20px;">No orders yet</p>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>