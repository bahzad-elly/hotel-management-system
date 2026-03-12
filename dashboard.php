<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: index.php");
    exit();
}

$total_guests = $conn->query("SELECT COUNT(*) as count FROM guests")->fetch_assoc()['count'];
$occupied_rooms = $conn->query("SELECT COUNT(*) as count FROM rooms WHERE status = 'Occupied'")->fetch_assoc()['count'];
$available_rooms = $conn->query("SELECT COUNT(*) as count FROM rooms WHERE status = 'Available'")->fetch_assoc()['count'];

$revenue_result = $conn->query("SELECT SUM(amount) as total FROM payments")->fetch_assoc();
$total_revenue = $revenue_result['total'] ? $revenue_result['total'] : 0.00;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Hotel Management System</title>
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
            <li><a href="dashboard.php" class="active"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
            <li><a href="rooms.php"><i class="bi bi-door-open me-2"></i> Rooms</a></li>
            <li><a href="reservations.php"><i class="bi bi-calendar-check me-2"></i> Reservations</a></li>
            <li><a href="guests.php"><i class="bi bi-people me-2"></i> Guests</a></li>
            <li><a href="supplies.php"><i class="bi bi-box-seam me-2"></i> Supplies</a></li>
            <li><a href="logout.php" class="text-danger"><i class="bi bi-box-arrow-left me-2"></i> Logout</a></li>
        </ul>
    </nav>

    <div class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Admin Dashboard</h2>
        </div>

        <div class="row g-4">
            <div class="col-md-3">
                <div class="card card-summary bg-primary text-white p-3">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-uppercase">Total Guests</h6>
                            <h3 class="mb-0"><?php echo $total_guests; ?></h3>
                        </div>
                        <i class="bi bi-people fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-summary bg-success text-white p-3">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-uppercase">Occupied Rooms</h6>
                            <h3 class="mb-0"><?php echo $occupied_rooms; ?></h3>
                        </div>
                        <i class="bi bi-door-closed fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-summary bg-warning text-dark p-3">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-uppercase">Available Rooms</h6>
                            <h3 class="mb-0"><?php echo $available_rooms; ?></h3>
                        </div>
                        <i class="bi bi-door-open fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-summary bg-info text-white p-3">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-uppercase">Revenue</h6>
                            <h3 class="mb-0">$<?php echo number_format($total_revenue, 2); ?></h3>
                        </div>
                        <i class="bi bi-cash-stack fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>