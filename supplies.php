<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_category'])) {
    $category_name = $_POST['category_name'];
    
    $stmt = $conn->prepare("INSERT INTO supply_categories (category_name) VALUES (?)");
    $stmt->bind_param("s", $category_name);
    $stmt->execute();
    
    header("Location: supplies.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_supply'])) {
    $category_id = $_POST['category_id'];
    $supply_name = $_POST['supply_name'];
    $stock_quantity = $_POST['stock_quantity'];
    
    $stmt = $conn->prepare("INSERT INTO supplies (category_id, supply_name, stock_quantity) VALUES (?, ?, ?)");
    $stmt->bind_param("isi", $category_id, $supply_name, $stock_quantity);
    $stmt->execute();
    
    header("Location: supplies.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['record_usage'])) {
    $supply_id = $_POST['supply_id'];
    $room_id = $_POST['room_id'];
    $quantity_used = $_POST['quantity_used'];
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO supply_usage (user_id, room_id, supply_id, quantity_used) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiii", $user_id, $room_id, $supply_id, $quantity_used);
    $stmt->execute();

    $update = $conn->prepare("UPDATE supplies SET stock_quantity = stock_quantity - ? WHERE supply_id = ?");
    $update->bind_param("ii", $quantity_used, $supply_id);
    $update->execute();

    header("Location: supplies.php");
    exit();
}

$supplies_query = "SELECT s.*, c.category_name 
                   FROM supplies s 
                   JOIN supply_categories c ON s.category_id = c.category_id 
                   ORDER BY c.category_name ASC, s.supply_name ASC";
$supplies_result = $conn->query($supplies_query);

$categories_query = "SELECT * FROM supply_categories ORDER BY category_name ASC";
$categories_result = $conn->query($categories_query);
$categories_array = [];
while($c = $categories_result->fetch_assoc()) {
    $categories_array[] = $c;
}

$rooms_query = "SELECT room_id, room_number FROM rooms ORDER BY room_number ASC";
$rooms_result = $conn->query($rooms_query);
$rooms_array = [];
while($r = $rooms_result->fetch_assoc()) {
    $rooms_array[] = $r;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplies - Hotel Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f6f9; }
        .sidebar { min-height: 100vh; background-color: #343a40; }
        .sidebar a { color: #c2c7d0; text-decoration: none; padding: 15px 20px; display: block; }
        .sidebar a:hover { background-color: #495057; color: #fff; }
        .sidebar .active { background-color: #0d6efd; color: #fff; }
    </style>
</head>
<body>

<div class="d-flex">
    <nav class="sidebar flex-shrink-0" style="width: 250px;">
        <div class="py-4 px-3 mb-3 text-white text-center border-bottom border-secondary">
            <i class="bi bi-building fs-3"></i>
            <h4 class="mt-2">Hotel System</h4>
        </div>
        <ul class="list-unstyled">
            <li><a href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
            <li><a href="rooms.php"><i class="bi bi-door-open me-2"></i> Rooms</a></li>
            <li><a href="reservations.php"><i class="bi bi-calendar-check me-2"></i> Reservations</a></li>
            <li><a href="guests.php"><i class="bi bi-people me-2"></i> Guests</a></li>
            <li><a href="payments.php"><i class="bi bi-cash-stack me-2"></i> Payments</a></li>
            <li><a href="supplies.php" class="active"><i class="bi bi-box-seam me-2"></i> Supplies</a></li>
            <li><a href="logout.php" class="text-danger"><i class="bi bi-box-arrow-left me-2"></i> Logout</a></li>
        </ul>
    </nav>

    <div class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Supplies & Inventory</h2>
            <div>
                <button class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                    <i class="bi bi-tags"></i> Add Category
                </button>
                <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#addSupplyModal">
                    <i class="bi bi-plus-lg"></i> Add Supply
                </button>
                <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#recordUsageModal">
                    <i class="bi bi-clipboard-minus"></i> Record Usage
                </button>
            </div>
        </div>

        <div class="bg-white p-4 rounded shadow-sm">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Category</th>
                        <th>Supply Name</th>
                        <th>Current Stock</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $supplies_result->data_seek(0);
                    while($row = $supplies_result->fetch_assoc()): 
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['supply_id']); ?></td>
                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($row['category_name']); ?></span></td>
                        <td><?php echo htmlspecialchars($row['supply_name']); ?></td>
                        <td class="fw-bold <?php echo ($row['stock_quantity'] < 10) ? 'text-danger' : 'text-success'; ?>">
                            <?php echo htmlspecialchars($row['stock_quantity']); ?> units
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">New Supply Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="add_category" value="1">
                    <div class="mb-3">
                        <label class="form-label">Category Name (e.g., Toiletries, Cleaning)</label>
                        <input type="text" name="category_name" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="addSupplyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Supply</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="add_supply" value="1">
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">-- Select Category --</option>
                            <?php foreach($categories_array as $c): ?>
                                <option value="<?php echo $c['category_id']; ?>"><?php echo htmlspecialchars($c['category_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Supply Name</label>
                        <input type="text" name="supply_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Initial Stock Quantity</label>
                        <input type="number" name="stock_quantity" class="form-control" min="0" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Supply</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="recordUsageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Record Supply Usage</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="record_usage" value="1">
                    <div class="mb-3">
                        <label class="form-label">Room</label>
                        <select name="room_id" class="form-select" required>
                            <option value="">-- Select Room --</option>
                            <?php foreach($rooms_array as $r): ?>
                                <option value="<?php echo $r['room_id']; ?>">Room <?php echo htmlspecialchars($r['room_number']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Supply Item</label>
                        <select name="supply_id" class="form-select" required>
                            <option value="">-- Select Supply --</option>
                            <?php 
                            $supplies_result->data_seek(0);
                            while($s = $supplies_result->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $s['supply_id']; ?>"><?php echo htmlspecialchars($s['supply_name']); ?> (Stock: <?php echo $s['stock_quantity']; ?>)</option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantity Used</label>
                        <input type="number" name="quantity_used" class="form-control" min="1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-warning">Record Usage</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>