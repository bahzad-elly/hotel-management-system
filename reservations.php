<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_reservation'])) {
    $guest_id = $_POST['guest_id'];
    $room_id = $_POST['room_id'];
    $check_in_date = $_POST['check_in_date'];
    $check_out_date = $_POST['check_out_date'];
    $status = $_POST['status'];
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO reservations (user_id, guest_id, room_id, check_in_date, check_out_date, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiisss", $user_id, $guest_id, $room_id, $check_in_date, $check_out_date, $status);
    $stmt->execute();
    
    if($status == 'Checked-in') {
        $update_room = $conn->prepare("UPDATE rooms SET status = 'Occupied' WHERE room_id = ?");
        $update_room->bind_param("i", $room_id);
        $update_room->execute();
    }
    
    header("Location: reservations.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_reservation'])) {
    $reservation_id = $_POST['reservation_id'];
    $guest_id = $_POST['guest_id'];
    $room_id = $_POST['room_id'];
    $check_in_date = $_POST['check_in_date'];
    $check_out_date = $_POST['check_out_date'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE reservations SET guest_id = ?, room_id = ?, check_in_date = ?, check_out_date = ?, status = ? WHERE reservation_id = ?");
    $stmt->bind_param("iisssi", $guest_id, $room_id, $check_in_date, $check_out_date, $status, $reservation_id);
    $stmt->execute();
    
    if($status == 'Checked-out' || $status == 'Cancelled') {
        $update_room = $conn->prepare("UPDATE rooms SET status = 'Available' WHERE room_id = ?");
        $update_room->bind_param("i", $room_id);
        $update_room->execute();
    } elseif ($status == 'Checked-in') {
        $update_room = $conn->prepare("UPDATE rooms SET status = 'Occupied' WHERE room_id = ?");
        $update_room->bind_param("i", $room_id);
        $update_room->execute();
    }
    
    header("Location: reservations.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_reservation'])) {
    $reservation_id = $_POST['reservation_id'];
    $room_id = $_POST['room_id'];

    $stmt = $conn->prepare("DELETE FROM reservations WHERE reservation_id = ?");
    $stmt->bind_param("i", $reservation_id);
    $stmt->execute();
    
    $update_room = $conn->prepare("UPDATE rooms SET status = 'Available' WHERE room_id = ?");
    $update_room->bind_param("i", $room_id);
    $update_room->execute();
    
    header("Location: reservations.php");
    exit();
}

$res_query = "SELECT res.*, g.first_name, g.last_name, r.room_number 
              FROM reservations res 
              JOIN guests g ON res.guest_id = g.guest_id 
              JOIN rooms r ON res.room_id = r.room_id 
              ORDER BY res.reservation_id DESC";
$res_result = $conn->query($res_query);

$guests_query = "SELECT guest_id, first_name, last_name FROM guests ORDER BY first_name ASC";
$guests_result = $conn->query($guests_query);
$guests_array = [];
while($g = $guests_result->fetch_assoc()) {
    $guests_array[] = $g;
}

$rooms_query = "SELECT room_id, room_number, status FROM rooms ORDER BY room_number ASC";
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
    <title>Reservations - Hotel Management System</title>
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
            <li><a href="reservations.php" class="active"><i class="bi bi-calendar-check me-2"></i> Reservations</a></li>
            <li><a href="guests.php"><i class="bi bi-people me-2"></i> Guests</a></li>
            <li><a href="supplies.php"><i class="bi bi-box-seam me-2"></i> Supplies</a></li>
            <li><a href="logout.php" class="text-danger"><i class="bi bi-box-arrow-left me-2"></i> Logout</a></li>
        </ul>
    </nav>

    <div class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Reservation Management</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addResModal">
                <i class="bi bi-plus-circle"></i> New Booking
            </button>
        </div>

        <div class="bg-white p-4 rounded shadow-sm">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Res ID</th>
                        <th>Guest Name</th>
                        <th>Room No.</th>
                        <th>Check-In</th>
                        <th>Check-Out</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $res_result->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo htmlspecialchars($row['reservation_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['room_number']); ?></td>
                        <td><?php echo htmlspecialchars($row['check_in_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['check_out_date']); ?></td>
                        <td>
                            <?php if($row['status'] == 'Booked'): ?>
                                <span class="badge bg-primary">Booked</span>
                            <?php elseif($row['status'] == 'Checked-in'): ?>
                                <span class="badge bg-success">Checked-in</span>
                            <?php elseif($row['status'] == 'Checked-out'): ?>
                                <span class="badge bg-secondary">Checked-out</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Cancelled</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editResModal<?php echo $row['reservation_id']; ?>">
                                <i class="bi bi-pencil"></i>
                            </button>
                            
                            <form method="POST" action="" class="d-inline" onsubmit="return confirm('Delete this reservation completely?');">
                                <input type="hidden" name="delete_reservation" value="1">
                                <input type="hidden" name="reservation_id" value="<?php echo $row['reservation_id']; ?>">
                                <input type="hidden" name="room_id" value="<?php echo $row['room_id']; ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>

                    <div class="modal fade" id="editResModal<?php echo $row['reservation_id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Reservation</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="POST" action="">
                                    <div class="modal-body">
                                        <input type="hidden" name="edit_reservation" value="1">
                                        <input type="hidden" name="reservation_id" value="<?php echo $row['reservation_id']; ?>">
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Guest</label>
                                            <select name="guest_id" class="form-select" required>
                                                <?php foreach($guests_array as $g): ?>
                                                    <option value="<?php echo $g['guest_id']; ?>" <?php echo ($g['guest_id'] == $row['guest_id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($g['first_name'] . ' ' . $g['last_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Room</label>
                                            <select name="room_id" class="form-select" required>
                                                <?php foreach($rooms_array as $r): ?>
                                                    <option value="<?php echo $r['room_id']; ?>" <?php echo ($r['room_id'] == $row['room_id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($r['room_number']); ?> (<?php echo htmlspecialchars($r['status']); ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col">
                                                <label class="form-label">Check-In Date</label>
                                                <input type="date" name="check_in_date" class="form-control" value="<?php echo htmlspecialchars($row['check_in_date']); ?>" required>
                                            </div>
                                            <div class="col">
                                                <label class="form-label">Check-Out Date</label>
                                                <input type="date" name="check_out_date" class="form-control" value="<?php echo htmlspecialchars($row['check_out_date']); ?>" required>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Status</label>
                                            <select name="status" class="form-select" required>
                                                <option value="Booked" <?php echo ($row['status'] == 'Booked') ? 'selected' : ''; ?>>Booked</option>
                                                <option value="Checked-in" <?php echo ($row['status'] == 'Checked-in') ? 'selected' : ''; ?>>Checked-in</option>
                                                <option value="Checked-out" <?php echo ($row['status'] == 'Checked-out') ? 'selected' : ''; ?>>Checked-out</option>
                                                <option value="Cancelled" <?php echo ($row['status'] == 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary">Update Reservation</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addResModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">New Reservation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="add_reservation" value="1">
                    
                    <div class="mb-3">
                        <label class="form-label">Select Guest</label>
                        <select name="guest_id" class="form-select" required>
                            <option value="">-- Choose Guest --</option>
                            <?php foreach($guests_array as $g): ?>
                                <option value="<?php echo $g['guest_id']; ?>">
                                    <?php echo htmlspecialchars($g['first_name'] . ' ' . $g['last_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Select Room</label>
                        <select name="room_id" class="form-select" required>
                            <option value="">-- Choose Room --</option>
                            <?php foreach($rooms_array as $r): ?>
                                <option value="<?php echo $r['room_id']; ?>" <?php echo ($r['status'] != 'Available') ? 'disabled' : ''; ?>>
                                    Room <?php echo htmlspecialchars($r['room_number']); ?> - <?php echo htmlspecialchars($r['status']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Check-In Date</label>
                            <input type="date" name="check_in_date" class="form-control" required>
                        </div>
                        <div class="col">
                            <label class="form-label">Check-Out Date</label>
                            <input type="date" name="check_out_date" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="Booked">Booked</option>
                            <option value="Checked-in">Checked-in</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Reservation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>