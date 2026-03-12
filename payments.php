<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_payment'])) {
    $reservation_id = $_POST['reservation_id'];
    $amount = $_POST['amount'];
    $payment_method = $_POST['payment_method'];
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO payments (reservation_id, user_id, amount, payment_method) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iids", $reservation_id, $user_id, $amount, $payment_method);
    $stmt->execute();
    
    header("Location: payments.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_payment'])) {
    $payment_id = $_POST['payment_id'];

    $stmt = $conn->prepare("DELETE FROM payments WHERE payment_id = ?");
    $stmt->bind_param("i", $payment_id);
    $stmt->execute();
    
    header("Location: payments.php");
    exit();
}

$payments_query = "SELECT p.*, r.room_id, rm.room_number, g.first_name, g.last_name 
                   FROM payments p 
                   JOIN reservations r ON p.reservation_id = r.reservation_id 
                   JOIN rooms rm ON r.room_id = rm.room_id 
                   JOIN guests g ON r.guest_id = g.guest_id 
                   ORDER BY p.payment_date DESC";
$payments_result = $conn->query($payments_query);

$res_query = "SELECT r.reservation_id, r.status, rm.room_number, g.first_name, g.last_name 
              FROM reservations r 
              JOIN rooms rm ON r.room_id = rm.room_id 
              JOIN guests g ON r.guest_id = g.guest_id 
              WHERE r.status != 'Cancelled'";
$res_result = $conn->query($res_query);
$res_array = [];
while($res = $res_result->fetch_assoc()) {
    $res_array[] = $res;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments - Hotel Management System</title>
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
            <li><a href="payments.php" class="active"><i class="bi bi-cash-stack me-2"></i> Payments</a></li>
            <li><a href="supplies.php"><i class="bi bi-box-seam me-2"></i> Supplies</a></li>
            <li><a href="logout.php" class="text-danger"><i class="bi bi-box-arrow-left me-2"></i> Logout</a></li>
        </ul>
    </nav>

    <div class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Payment Management</h2>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addPaymentModal">
                <i class="bi bi-plus-circle"></i> Record Payment
            </button>
        </div>

        <div class="bg-white p-4 rounded shadow-sm">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Receipt ID</th>
                        <th>Date & Time</th>
                        <th>Guest</th>
                        <th>Room</th>
                        <th>Method</th>
                        <th>Amount</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $payments_result->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo htmlspecialchars($row['payment_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['payment_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['room_number']); ?></td>
                        <td>
                            <span class="badge bg-info text-dark"><?php echo htmlspecialchars($row['payment_method']); ?></span>
                        </td>
                        <td class="fw-bold text-success">$<?php echo htmlspecialchars(number_format($row['amount'], 2)); ?></td>
                        <td>
                            <form method="POST" action="" class="d-inline" onsubmit="return confirm('Are you sure you want to void this payment?');">
                                <input type="hidden" name="delete_payment" value="1">
                                <input type="hidden" name="payment_id" value="<?php echo $row['payment_id']; ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> Void</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Record New Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="add_payment" value="1">
                    
                    <div class="mb-3">
                        <label class="form-label">Select Reservation</label>
                        <select name="reservation_id" class="form-select" required>
                            <option value="">-- Choose Active Reservation --</option>
                            <?php foreach($res_array as $res): ?>
                                <option value="<?php echo $res['reservation_id']; ?>">
                                    Res #<?php echo htmlspecialchars($res['reservation_id']); ?> - 
                                    <?php echo htmlspecialchars($res['first_name'] . ' ' . $res['last_name']); ?> 
                                    (Room <?php echo htmlspecialchars($res['room_number']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="Cash">Cash</option>
                            <option value="Credit Card">Credit Card</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount ($)</label>
                        <input type="number" step="0.01" name="amount" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Process Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>