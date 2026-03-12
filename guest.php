<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_guest'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $special_notes = $_POST['special_notes'];

    $stmt = $conn->prepare("INSERT INTO guests (first_name, last_name, phone, email, special_notes) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $first_name, $last_name, $phone, $email, $special_notes);
    $stmt->execute();
    header("Location: guests.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_guest'])) {
    $guest_id = $_POST['guest_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $special_notes = $_POST['special_notes'];

    $stmt = $conn->prepare("UPDATE guests SET first_name = ?, last_name = ?, phone = ?, email = ?, special_notes = ? WHERE guest_id = ?");
    $stmt->bind_param("sssssi", $first_name, $last_name, $phone, $email, $special_notes, $guest_id);
    $stmt->execute();
    header("Location: guests.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_guest'])) {
    $guest_id = $_POST['guest_id'];

    $stmt = $conn->prepare("DELETE FROM guests WHERE guest_id = ?");
    $stmt->bind_param("i", $guest_id);
    $stmt->execute();
    header("Location: guests.php");
    exit();
}

$guests_query = "SELECT * FROM guests ORDER BY guest_id DESC";
$guests_result = $conn->query($guests_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guests - Hotel Management System</title>
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
            <li><a href="guests.php" class="active"><i class="bi bi-people me-2"></i> Guests</a></li>
            <li><a href="supplies.php"><i class="bi bi-box-seam me-2"></i> Supplies</a></li>
            <li><a href="logout.php" class="text-danger"><i class="bi bi-box-arrow-left me-2"></i> Logout</a></li>
        </ul>
    </nav>

    <div class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Guest Management</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addGuestModal">
                <i class="bi bi-person-plus"></i> Add New Guest
            </button>
        </div>

        <div class="bg-white p-4 rounded shadow-sm">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Contact Info</th>
                        <th>Special Notes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $guests_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['guest_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                        <td>
                            <div><i class="bi bi-telephone me-1"></i> <?php echo htmlspecialchars($row['phone']); ?></div>
                            <div><i class="bi bi-envelope me-1"></i> <?php echo htmlspecialchars($row['email']); ?></div>
                        </td>
                        <td><?php echo htmlspecialchars($row['special_notes']); ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editGuestModal<?php echo $row['guest_id']; ?>">
                                <i class="bi bi-pencil"></i>
                            </button>
                            
                            <form method="POST" action="" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this guest?');">
                                <input type="hidden" name="delete_guest" value="1">
                                <input type="hidden" name="guest_id" value="<?php echo $row['guest_id']; ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>

                    <div class="modal fade" id="editGuestModal<?php echo $row['guest_id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Guest</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="POST" action="">
                                    <div class="modal-body">
                                        <input type="hidden" name="edit_guest" value="1">
                                        <input type="hidden" name="guest_id" value="<?php echo $row['guest_id']; ?>">
                                        
                                        <div class="row mb-3">
                                            <div class="col">
                                                <label class="form-label">First Name</label>
                                                <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($row['first_name']); ?>" required>
                                            </div>
                                            <div class="col">
                                                <label class="form-label">Last Name</label>
                                                <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($row['last_name']); ?>" required>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Phone</label>
                                            <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($row['phone']); ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($row['email']); ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Special Notes</label>
                                            <textarea name="special_notes" class="form-control" rows="3"><?php echo htmlspecialchars($row['special_notes']); ?></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary">Update Guest</button>
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

<div class="modal fade" id="addGuestModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Guest</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="add_guest" value="1">
                    
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">First Name</label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        <div class="col">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Special Notes</label>
                        <textarea name="special_notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Guest</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>