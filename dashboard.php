<?php 
// Include the database connection at the very top
include 'db_connect.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Management System - Dashboard</title>
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
            <h4 class="mt-2">BAAMS Hotel</h4>
        </div>
        <ul class="list-unstyled">
            <li><a href="dashboard.php" class="active"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
            <li><a href="rooms.php"><i class="bi bi-door-open me-2"></i> Rooms</a></li>
            <li><a href="reservations.php"><i class="bi bi-calendar-check me-2"></i> Reservations</a></li>
            <li><a href="guests.php"><i class="bi bi-people me-2"></i> Guests</a></li>
            <li><a href="supplies.php"><i class="bi bi-box-seam me-2"></i> Supplies</a></li>
            <li><a href="reports.php"><i class="bi bi-bar-chart me-2"></i> Reports</a></li>
        </ul>
    </nav>

    <div class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Admin Dashboard</h2>
            <div>
                <span class="me-3">Welcome, Admin</span>
                <button class="btn btn-outline-danger btn-sm">Logout</button>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-3">
                <div class="card card-summary bg-primary text-white p-3">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-uppercase">Total Guests</h6>
                            <h3 class="mb-0">150</h3> </div>
                        <i class="bi bi-people fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-summary bg-success text-white p-3">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-uppercase">Occupied Rooms</h6>
                            <h3 class="mb-0">12</h3> </div>
                        <i class="bi bi-door-closed fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-summary bg-warning text-dark p-3">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-uppercase">Available Rooms</h6>
                            <h3 class="mb-0">18</h3> </div>
                        <i class="bi bi-door-open fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-summary bg-info text-white p-3">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-uppercase">Revenue</h6>
                            <h3 class="mb-0">$4,250</h3> </div>
                        <i class="bi bi-cash-stack fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-5 bg-white p-4 rounded shadow-sm">
            <h4>Recent Activity / Quick Actions</h4>
            <p class="text-muted">We will populate this area with dynamic tables using PHP and AJAX soon.</p>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>