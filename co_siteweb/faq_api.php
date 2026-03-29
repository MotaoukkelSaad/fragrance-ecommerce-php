<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require 'config.php';

// Check request method
$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? htmlspecialchars($_GET['action']) : '';

// =====================================================
// GET ALL FAQs
// =====================================================
if ($action === 'get_all' || $action === '') {
    $query = "SELECT id, category, question, answer FROM faqs WHERE is_active = 1 ORDER BY category, order_num ASC";
    $result = $conn->query($query);
    
    $faqs = [];
    while ($row = $result->fetch_assoc()) {
        $faqs[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'total' => count($faqs),
        'data' => $faqs
    ]);
    exit();
}

// =====================================================
// GET FAQs BY CATEGORY
// =====================================================
if ($action === 'get_by_category') {
    $category = isset($_GET['category']) ? htmlspecialchars($_GET['category']) : '';
    
    if (empty($category)) {
        echo json_encode(['success' => false, 'message' => 'Category required']);
        exit();
    }
    
    $stmt = $conn->prepare("SELECT id, category, question, answer FROM faqs WHERE is_active = 1 AND category = ? ORDER BY order_num ASC");
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $faqs = [];
    while ($row = $result->fetch_assoc()) {
        $faqs[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'category' => $category,
        'total' => count($faqs),
        'data' => $faqs
    ]);
    exit();
}

// =====================================================
// SEARCH FAQs BY KEYWORDS (IMPROVED)
// =====================================================
if ($action === 'search') {
    $search = isset($_GET['q']) ? trim($_GET['q']) : '';
    
    if (empty($search)) {
        echo json_encode(['success' => false, 'message' => 'Search query required']);
        exit();
    }

    // First, try exact category match
    $stmt = $conn->prepare("SELECT id, category, question, answer FROM faqs WHERE is_active = 1 AND category LIKE ? ORDER BY order_num ASC");
    $searchTerm = '%' . $search . '%';
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    $faqs = [];
    
    // If found by category, return those
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $faqs[] = $row;
        }
    } else {
        // Otherwise search by question/answer
        $stmt = $conn->prepare("SELECT id, category, question, answer FROM faqs WHERE is_active = 1 AND (question LIKE ? OR answer LIKE ? OR keywords LIKE ?) ORDER BY order_num ASC");
        $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $faqs[] = $row;
        }
    }

    echo json_encode([
        'success' => true,
        'search_query' => $search,
        'total' => count($faqs),
        'data' => $faqs
    ]);
    exit();
}

// =====================================================
// GET ALL CATEGORIES
// =====================================================
if ($action === 'get_categories') {
    $query = "SELECT DISTINCT category FROM faqs WHERE is_active = 1 ORDER BY category ASC";
    $result = $conn->query($query);
    
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row['category'];
    }
    
    echo json_encode([
        'success' => true,
        'total' => count($categories),
        'data' => $categories
    ]);
    exit();
}

// =====================================================
// GET SINGLE FAQ BY ID
// =====================================================
if ($action === 'get_by_id') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid ID']);
        exit();
    }
    
    $stmt = $conn->prepare("SELECT id, category, question, answer FROM faqs WHERE id = ? AND is_active = 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'FAQ not found']);
        exit();
    }
    
    $faq = $result->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'data' => $faq
    ]);
    exit();
}

// =====================================================
// DEFAULT: INVALID ACTION
// =====================================================
echo json_encode(['success' => false, 'message' => 'Invalid action']);
exit();
?>