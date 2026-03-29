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

// Handle status update
if (isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $status = htmlspecialchars($_POST['status']);
    
    $stmt = $conn->prepare("UPDATE orders SET status=?, updated_at=NOW() WHERE id=?");
    $stmt->bind_param("si", $status, $order_id);
    
    if ($stmt->execute()) {
        $message = "✅ Order status updated successfully!";
    } else {
        $error = "❌ Failed to update order status";
    }
}

// Get single order details (if viewing)
$order_details = null;
$order_items = [];
if (isset($_GET['id'])) {
    $order_id = (int)$_GET['id'];
    $result = $conn->query("SELECT * FROM orders WHERE id = $order_id");
    $order_details = $result->fetch_assoc();
    
    if ($order_details) {
        // Get order items - use correct column names
        $check_table = $conn->query("SHOW TABLES LIKE 'order_items'");
        if ($check_table->num_rows > 0) {
            $result = $conn->query("SELECT oi.*, p.image FROM order_items oi 
                                   LEFT JOIN products p ON oi.product_id = p.id 
                                   WHERE oi.order_id = $order_id");
            while ($item = $result->fetch_assoc()) {
                $order_items[] = $item;
            }
        }
    }
}

// Get all orders (if not viewing single order)
$orders = [];
if (!isset($_GET['id'])) {
    $result = $conn->query("SELECT id, user_id, name, email, phone, total, status, created_at FROM orders ORDER BY created_at DESC");
    while ($order = $result->fetch_assoc()) {
        $orders[] = $order;
    }
}

$user = Auth::getCurrentUser();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management — Admin</title>
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
        .orders-table { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 30px; }
        .orders-table h2 { margin: 0 0 20px 0; color: #1a1a2e; }
        table { width: 100%; border-collapse: collapse; }
        table th, table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        table th { background: #f5f5f5; font-weight: 600; color: #1a1a2e; }
        table tr:hover { background: #f9f9f9; }
        .badge { display: inline-block; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge-pending { background: #fff3cd; color: #856404; }
        .badge-processing { background: #cfe2ff; color: #084298; }
        .badge-shipped { background: #d1ecf1; color: #0c5460; }
        .badge-delivered { background: #d4edda; color: #155724; }
        .btn-view { background: #3498db; color: white; padding: 6px 12px; text-decoration: none; border-radius: 4px; font-size: 12px; }
        .btn-view:hover { background: #2980b9; }
        .back-link { margin-bottom: 20px; }
        .back-link a { color: #3498db; text-decoration: none; font-weight: 600; }
        .back-link a:hover { text-decoration: underline; }
        .order-detail-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .order-detail-card h3 { margin: 0 0 15px 0; color: #1a1a2e; border-bottom: 2px solid #e94560; padding-bottom: 10px; }
        .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .detail-grid.full { grid-template-columns: 1fr; }
        .detail-row { margin-bottom: 15px; }
        .detail-row label { font-weight: 600; color: #1a1a2e; display: block; margin-bottom: 5px; }
        .detail-row span { color: #666; }
        .status-form { display: flex; gap: 10px; align-items: center; }
        .status-form select { padding: 8px 12px; border: 2px solid #ddd; border-radius: 4px; }
        .status-form button { padding: 8px 16px; background: #27ae60; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600; }
        .status-form button:hover { background: #229954; }
        .items-table { background: #f9f9f9; border-radius: 4px; overflow: hidden; }
        .items-table table { margin-bottom: 0; }
        .items-table img { width: 50px; height: 50px; object-fit: cover; border-radius: 4px; }
        .no-items { color: #999; text-align: center; padding: 20px; }
        .product-cell { display: flex; align-items: center; gap: 10px; }
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
            <li><a href="orders.php" class="active">📋 Orders</a></li>
            <li><a href="users.php">👥 Users</a></li>
            <li><a href="settings.php">⚙️ Settings</a></li>
            <li><a href="../logout.php" style="color: #e94560;">🚪 Logout</a></li>
        </ul>
    </div>

    <!-- MAIN CONTENT -->
    <div class="admin-main">
        <!-- HEADER -->
        <div class="admin-header">
            <h1>Order Management</h1>
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

        <?php if (isset($_GET['id']) && $order_details): ?>
            <!-- ORDER DETAILS VIEW -->
            <div class="back-link">
                <a href="orders.php">← Back to Orders</a>
            </div>

            <!-- ORDER INFO -->
            <div class="order-detail-card">
                <h3>📋 Order #<?php echo $order_details['id']; ?></h3>
                <div class="detail-grid">
                    <div class="detail-row">
                        <label>Order Date</label>
                        <span><?php echo date('d/m/Y H:i', strtotime($order_details['created_at'])); ?></span>
                    </div>
                    <div class="detail-row">
                        <label>Status</label>
                        <form method="POST" class="status-form">
                            <input type="hidden" name="order_id" value="<?php echo $order_details['id']; ?>">
                            <select name="status">
                                <option value="pending" <?php echo $order_details['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="processing" <?php echo $order_details['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                <option value="shipped" <?php echo $order_details['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                <option value="delivered" <?php echo $order_details['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                            </select>
                            <button type="submit" name="update_status">Update</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- CUSTOMER INFO -->
            <div class="order-detail-card">
                <h3>👤 Customer Information</h3>
                <div class="detail-grid">
                    <div class="detail-row">
                        <label>Full Name</label>
                        <span><?php echo htmlspecialchars($order_details['name']); ?></span>
                    </div>
                    <div class="detail-row">
                        <label>Email</label>
                        <span><?php echo htmlspecialchars($order_details['email']); ?></span>
                    </div>
                    <div class="detail-row">
                        <label>Phone</label>
                        <span><?php echo htmlspecialchars($order_details['phone']); ?></span>
                    </div>
                    <div class="detail-row">
                        <label>City</label>
                        <span><?php echo htmlspecialchars($order_details['city'] ?? 'N/A'); ?></span>
                    </div>
                </div>
                <div class="detail-grid full">
                    <div class="detail-row">
                        <label>Address</label>
                        <span><?php echo htmlspecialchars($order_details['address'] ?? 'N/A'); ?></span>
                    </div>
                </div>
            </div>

            <!-- ORDER ITEMS -->
            <div class="order-detail-card">
                <h3>📦 Order Items</h3>
                <?php if (!empty($order_items)): ?>
                    <div class="items-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order_items as $item): ?>
                                <tr>
                                    <td>
                                        <div class="product-cell">
                                            <img src="../<?php echo htmlspecialchars($item['image'] ?? ''); ?>" alt="Product"
                                                 onerror="this.src='https://via.placeholder.com/50x50?text=No+Image'">
                                            <span><?php echo htmlspecialchars($item['product_name']); ?></span>
                                        </div>
                                    </td>
                                    <td>MAD <?php echo number_format($item['price_at_purchase'], 2); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td><strong>MAD <?php echo number_format($item['quantity'] * $item['price_at_purchase'], 2); ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="no-items">No items in this order</p>
                <?php endif; ?>
            </div>

            <!-- ORDER SUMMARY -->
            <div class="order-detail-card">
                <h3>💰 Order Summary</h3>
                <div class="detail-grid">
                    <div class="detail-row">
                        <label>Subtotal</label>
                        <span>MAD <?php echo number_format($order_details['subtotal'] ?? 0, 2); ?></span>
                    </div>
                    <div class="detail-row">
                        <label>Shipping (<?php echo htmlspecialchars($order_details['shipping_type'] ?? 'Standard'); ?>)</label>
                        <span>MAD <?php echo number_format($order_details['shipping_cost'] ?? 0, 2); ?></span>
                    </div>
                    <div class="detail-row" style="font-weight: 700; font-size: 18px; color: #e94560; border-top: 2px solid #e94560; padding-top: 10px; margin-top: 10px;">
                        <label>Total</label>
                        <span>MAD <?php echo number_format($order_details['total'], 2); ?></span>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- ORDERS LIST VIEW -->
            <div class="orders-table">
                <h2>All Orders (<?php echo count($orders); ?>)</h2>
                <?php if (!empty($orders)): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Email</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><strong>#<?php echo $order['id']; ?></strong></td>
                                <td><?php echo htmlspecialchars($order['name']); ?></td>
                                <td><?php echo htmlspecialchars($order['email']); ?></td>
                                <td><strong>MAD <?php echo number_format($order['total'], 2); ?></strong></td>
                                <td>
                                    <span class="badge badge-<?php echo str_replace(' ', '-', strtolower($order['status'])); ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $order['status'])); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                <td><a href="orders.php?id=<?php echo $order['id']; ?>" class="btn-view">👁️ View</a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="color: #999; text-align: center; padding: 20px;">No orders found</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>