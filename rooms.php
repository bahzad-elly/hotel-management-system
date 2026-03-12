<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_room'])) {
    $room_number = $_POST['room_number'];
    $type_id = $_POST['type_id'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("INSERT INTO rooms (room_number, type_id, status) VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $room_number, $type_id, $status);
    $stmt->execute();
    header("Location: rooms.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_room'])) {
    $room_id = $_POST['room_id'];
    $room_number = $_POST['room_number'];
    $type_id = $_POST['type_id'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE rooms SET room_number = ?, type_id = ?, status = ? WHERE room_id = ?");
    $stmt->bind_param("sisi", $room_number, $type_id, $status, $room_id);
    $stmt->execute();
    header("Location: rooms.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_room'])) {
    $room_id = $_POST['room_id'];

    $stmt = $conn->prepare("DELETE FROM rooms WHERE room_id = ?");
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    header("Location: rooms.php");
    exit();
}

$rooms_query = "SELECT r.room_id, r.room_number, r.status, r.type_id, t.type_name, t.base_price, t.capacity 
                FROM rooms r 
                JOIN room_types t ON r.type_id = t.type_id";
$rooms_result = $conn->query($rooms_query);

$types_query = "SELECT * FROM room_types";
$types_result = $conn->query($types_query);
$types_array = [];
while($type = $types_result->fetch_assoc()) {
    $types_array[] = $type;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rooms - Hotel Management System</title>
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
            <li><a href="rooms.php" class="active"><i class="bi bi-door-open me-2"></i> Rooms</a></li>
            <li><a href="reservations.php"><i class="bi bi-calendar-check me-2"></i> Reservations</a></li>
            <li><a href="guests.php"><i class="bi bi-people me-2"></i> Guests</a></li>
            <li><a href="supplies.php"><i class="bi bi-box-seam me-2"></i> Supplies</a></li>
            <li><a href="logout.php" class="text-danger"><i class="bi bi-box-arrow-left me-2"></i> Logout</a></li>
        </ul>
    </nav>

    <div class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Room Management</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoomModal">
                <i class="bi bi-plus-lg"></i> Add New Room
            </button>
        </div>

        <div class="bg-white p-4 rounded shadow-sm">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Room No.</th>
                        <th>Type</th>
                        <th>Capacity</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $rooms_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['room_number']); ?></td>
                        <td><?php echo htmlspecialchars($row['type_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['capacity']); ?> Persons</td>
                        <td>$<?php echo htmlspecialchars(number_format($row['base_price'], 2)); ?></td>
                        <td>
                            <?php if($row['status'] == 'Available'): ?>
                                <span class="badge bg-success">Available</span>
                            <?php elseif($row['status'] == 'Occupied'): ?>
                                <span class="badge bg-danger">Occupied</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">Maintenance</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editRoomModal<?php echo $row['room_id']; ?>">
                                <i class="bi bi-pencil"></i>
                            </button>
                            
                            <form method="POST" action="" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this room?');">
                                <input type="hidden" name="delete_room" value="1">
                                <input type="hidden" name="room_id" value="<?php echo $row['room_id']; ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>

                    <div class="modal fade" id="editRoomModal<?php echo $row['room_id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Room</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="POST" action="">
                                    <div class="modal-body">
                                        <input type="hidden" name="edit_room" value="1">
                                        <input type="hidden" name="room_id" value="<?php echo $row['room_id']; ?>">
                                        <div class="mb-3">
                                            <label class="form-label">Room Number</label>
                                            <input type="text" name="room_number" class="form-control" value="<?php echo htmlspecialchars($row['room_number']); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Room Type</label>
                                            <select name="type_id" class="form-select" required>
                                                <?php foreach($types_array as $type): ?>
                                                    <option value="<?php echo $type['type_id']; ?>" <?php echo ($type['type_id'] == $row['type_id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($type['type_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Status</label>
                                            <select name="status" class="form-select" required>
                                                <option value="Available" <?php echo ($row['status'] == 'Available') ? 'selected' : ''; ?>>Available</option>
                                                <option value="Occupied" <?php echo ($row['status'] == 'Occupied') ? 'selected' : ''; ?>>Occupied</option>
                                                <option value="Maintenance" <?php echo ($row['status'] == 'Maintenance') ? 'selected' : ''; ?>>Maintenance</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary">Update Room</button>
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

<div class="modal fade" id="addRoomModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Room</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="add_room" value="1">
                    <div class="mb-3">
                        <label class="form-label">Room Number</label>
                        <input type="text" name="room_number" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Room Type</label>
                        <select name="type_id" class="form-select" required>
                            <?php foreach($types_array as $type): ?>
                                <option value="<?php echo $type['type_id']; ?>"><?php echo htmlspecialchars($type['type_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="Available">Available</option>
                            <option value="Occupied">Occupied</option>
                            <option value="Maintenance">Maintenance</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Room</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>