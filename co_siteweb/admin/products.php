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

// Handle DELETE
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($conn->query("DELETE FROM products WHERE id = $id")) {
        $message = "✅ Product deleted successfully!";
    } else {
        $error = "❌ Failed to delete product";
    }
}

// Handle ADD/EDIT
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
    $name = htmlspecialchars(trim($_POST['name']));
    $description = htmlspecialchars(trim($_POST['description']));
    $price = (float)$_POST['price'];
    $image = htmlspecialchars(trim($_POST['image']));

    if (empty($name) || empty($price)) {
        $error = "❌ Name and price are required";
    } else {
        if ($id) {
            // UPDATE
            $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, image=? WHERE id=?");
            $stmt->bind_param("ssdsi", $name, $description, $price, $image, $id);
            if ($stmt->execute()) {
                $message = "✅ Product updated successfully!";
            } else {
                $error = "❌ Failed to update product";
            }
        } else {
            // INSERT
            $stmt = $conn->prepare("INSERT INTO products (name, description, price, image) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssds", $name, $description, $price, $image);
            if ($stmt->execute()) {
                $message = "✅ Product added successfully!";
            } else {
                $error = "❌ Failed to add product";
            }
        }
    }
}

// Get product to edit (if editing)
$edit_product = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $result = $conn->query("SELECT * FROM products WHERE id = $id");
    $edit_product = $result->fetch_assoc();
}

// Get all products
$products = [];
$result = $conn->query("SELECT id, name, price, image FROM products ORDER BY id DESC");
while ($product = $result->fetch_assoc()) {
    $products[] = $product;
}

$user = Auth::getCurrentUser();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management — Admin</title>
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
        .form-section { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 30px; }
        .form-section h2 { margin: 0 0 20px 0; color: #1a1a2e; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .form-grid.full { grid-template-columns: 1fr; }
        .form-group { margin-bottom: 0; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #1a1a2e; }
        .form-group input, .form-group textarea { width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 6px; font-size: 14px; font-family: inherit; }
        .form-group input:focus, .form-group textarea:focus { outline: none; border-color: #e94560; }
        .form-actions { display: flex; gap: 10px; }
        .btn-submit { background: #27ae60; color: white; padding: 12px 25px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; }
        .btn-submit:hover { background: #229954; }
        .btn-cancel { background: #95a5a6; color: white; padding: 12px 25px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; text-decoration: none; display: inline-block; }
        .btn-cancel:hover { background: #7f8c8d; }
        .products-table { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .products-table h2 { margin: 0 0 20px 0; color: #1a1a2e; }
        table { width: 100%; border-collapse: collapse; }
        table th, table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        table th { background: #f5f5f5; font-weight: 600; color: #1a1a2e; }
        table tr:hover { background: #f9f9f9; }
        .product-img { width: 50px; height: 50px; object-fit: cover; border-radius: 4px; }
        .btn-edit { background: #3498db; color: white; padding: 6px 12px; text-decoration: none; border-radius: 4px; font-size: 12px; }
        .btn-edit:hover { background: #2980b9; }
        .btn-delete { background: #e74c3c; color: white; padding: 6px 12px; border: none; border-radius: 4px; font-size: 12px; cursor: pointer; }
        .btn-delete:hover { background: #c0392b; }
    </style>
</head>
<body>

<div class="admin-wrapper">
    <!-- SIDEBAR -->
    <div class="admin-sidebar">
        <div class="sidebar-logo">🏢 Admin Panel</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php">📊 Dashboard</a></li>
            <li><a href="products.php" class="active">📦 Products</a></li>
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
            <h1>Product Management</h1>
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

        <!-- FORM -->
        <div class="form-section">
            <h2><?php echo $edit_product ? '✏️ Edit Product' : '➕ Add New Product'; ?></h2>
            <form method="POST" action="products.php">
                <?php if ($edit_product): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_product['id']; ?>">
                <?php endif; ?>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">Product Name *</label>
                        <input type="text" id="name" name="name" placeholder="Product name" required 
                               value="<?php echo $edit_product ? htmlspecialchars($edit_product['name']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="price">Price (MAD) *</label>
                        <input type="number" id="price" name="price" placeholder="0.00" step="0.01" required 
                               value="<?php echo $edit_product ? $edit_product['price'] : ''; ?>">
                    </div>
                </div>

                <div class="form-grid full">
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" placeholder="Product description" rows="4"><?php echo $edit_product ? htmlspecialchars($edit_product['description']) : ''; ?></textarea>
                    </div>
                </div>

                <div class="form-grid full">
                    <div class="form-group">
                        <label for="image">Image Path</label>
                        <input type="text" id="image" name="image" placeholder="images/products/product.png" 
                               value="<?php echo $edit_product ? htmlspecialchars($edit_product['image']) : ''; ?>">
                        <small style="color: #666; margin-top: 5px; display: block;">Example: images/products/bleu chanel.png</small>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit">
                        <?php echo $edit_product ? '💾 Update Product' : '➕ Add Product'; ?>
                    </button>
                    <?php if ($edit_product): ?>
                        <a href="products.php" class="btn-cancel">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- PRODUCTS TABLE -->
        <div class="products-table">
            <h2>All Products (<?php echo count($products); ?>)</h2>
            <?php if (!empty($products)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo $product['id']; ?></td>
                            <td>
                                <img src="../<?php echo htmlspecialchars($product['image']); ?>" alt="Product" class="product-img"
                                     onerror="this.src='https://via.placeholder.com/50x50?text=No+Image'">
                            </td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><strong>MAD <?php echo number_format($product['price'], 2); ?></strong></td>
                            <td>
                                <a href="products.php?edit=<?php echo $product['id']; ?>" class="btn-edit">✏️ Edit</a>
                                <button onclick="if(confirm('Delete this product?')) window.location='products.php?delete=<?php echo $product['id']; ?>';" class="btn-delete">🗑️ Delete</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color: #999; text-align: center; padding: 20px;">No products found</p>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>