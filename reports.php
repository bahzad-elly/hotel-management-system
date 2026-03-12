<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: index.php");
    exit();
}

$daily_rev_query = $conn->query("SELECT SUM(amount) as total FROM payments WHERE DATE(payment_date) = CURDATE()");
$daily_revenue = $daily_rev_query->fetch_assoc()['total'] ?? 0.00;

$weekly_rev_query = $conn->query("SELECT SUM(amount) as total FROM payments WHERE payment_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
$weekly_revenue = $weekly_rev_query->fetch_assoc()['total'] ?? 0.00;

$monthly_rev_query = $conn->query("SELECT SUM(amount) as total FROM payments WHERE MONTH(payment_date) = MONTH(CURDATE()) AND YEAR(payment_date) = YEAR(CURDATE())");
$monthly_revenue = $monthly_rev_query->fetch_assoc()['total'] ?? 0.00;

$occupancy_query = $conn->query("SELECT COUNT(*) as occupied FROM rooms WHERE status = 'Occupied'");
$occupied_rooms = $occupancy_query->fetch_assoc()['occupied'];

$total_rooms_query = $conn->query("SELECT COUNT(*) as total FROM rooms");
$total_rooms = $total_rooms_query->fetch_assoc()['total'];

$occupancy_rate = ($total_rooms > 0) ? round(($occupied_rooms / $total_rooms) * 100, 2) : 0;

$recent_payments = $conn->query("SELECT p.amount, p.payment_date, g.first_name, g.last_name, r.room_number FROM payments p JOIN reservations res ON p.reservation_id = res.reservation_id JOIN guests g ON res.guest_id = g.guest_id JOIN rooms r ON res.room_id = r.room_id ORDER BY p.payment_date DESC LIMIT 10");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Hotel Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f6f9; }
        .sidebar { min-height: 100vh; background-color: #343a40; }
        .sidebar a { color: #c2c7d0; text-decoration: none; padding: 15px 20px; display: block; }
        .sidebar a:hover { background-color: #495057; color: #fff; }
        .sidebar .active { background-color: #0d6efd; color: #fff; }
        .card-summary { border-radius: 10px; border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
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
            <li><a href="supplies.php"><i class="bi bi-box-seam me-2"></i> Supplies</a></li>
            <li><a href="reports.php" class="active"><i class="bi bi-bar-chart me-2"></i> Reports</a></li>
            <li><a href="logout.php" class="text-danger"><i class="bi bi-box-arrow-left me-2"></i> Logout</a></li>
        </ul>
    </nav>

    <div class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Financial & Occupancy Reports</h2>
            <button class="btn btn-secondary" onclick="window.print()"><i class="bi bi-printer"></i> Print Report</button>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card card-summary bg-primary text-white p-3">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-uppercase">Daily Revenue</h6>
                            <h3 class="mb-0">$<?php echo number_format($daily_revenue, 2); ?></h3>
                        </div>
                        <i class="bi bi-calendar-day fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-summary bg-success text-white p-3">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-uppercase">Weekly Revenue</h6>
                            <h3 class="mb-0">$<?php echo number_format($weekly_revenue, 2); ?></h3>
                        </div>
                        <i class="bi bi-calendar-week fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-summary bg-info text-white p-3">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-uppercase">Monthly Revenue</h6>
                            <h3 class="mb-0">$<?php echo number_format($monthly_revenue, 2); ?></h3>
                        </div>
                        <i class="bi bi-calendar-month fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-6">
                <div class="bg-white p-4 rounded shadow-sm h-100">
                    <h4>Current Occupancy</h4>
                    <div class="d-flex align-items-center mt-4">
                        <div class="flex-grow-1">
                            <div class="progress" style="height: 30px;">
                                <div class="progress-bar bg-warning text-dark fw-bold" role="progressbar" style="width: <?php echo $occupancy_rate; ?>%;">
                                    <?php echo $occupancy_rate; ?>%
                                </div>
                            </div>
                        </div>
                        <div class="ms-3">
                            <strong><?php echo $occupied_rooms; ?> / <?php echo $total_rooms; ?> Rooms</strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="bg-white p-4 rounded shadow-sm h-100">
                    <h4>Recent Transactions</h4>
                    <table class="table table-sm mt-3">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Guest</th>
                                <th>Room</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($pt = $recent_payments->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($pt['payment_date'])); ?></td>
                                <td><?php echo htmlspecialchars($pt['first_name'] . ' ' . $pt['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($pt['room_number']); ?></td>
                                <td class="text-success fw-bold">$<?php echo number_format($pt['amount'], 2); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>