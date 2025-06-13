<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit;
}

$host = 'localhost';
$db   = 'Appeal_database';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}

$message = '';

// Fetch students and modules
$students = $conn->query("SELECT student_id, full_name FROM students ORDER BY full_name ASC");
$modules = $conn->query("SELECT module_id, module_name FROM modules ORDER BY module_name ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $conn->real_escape_string($_POST['student_id']);
    $module_id = $conn->real_escape_string($_POST['module_id']);
    $mark = $conn->real_escape_string($_POST['mark']);

    if (!$student_id || !$module_id || $mark === '') {
        $message = "All fields are required.";
    } elseif (!is_numeric($mark) || $mark < 0 || $mark > 100) {
        $message = "Please enter a valid mark between 0 and 100.";
    } else {
        $check = $conn->query("SELECT * FROM marks WHERE student_id='$student_id' AND module_id='$module_id'");
        if ($check->num_rows > 0) {
            $update = $conn->query("UPDATE marks SET mark='$mark' WHERE student_id='$student_id' AND module_id='$module_id'");
            $message = $update ? "Mark updated successfully." : "Error updating mark: " . $conn->error;
        } else {
            $insert = $conn->query("INSERT INTO marks (student_id, module_id, mark) VALUES ('$student_id', '$module_id', '$mark')");
            $message = $insert ? "Mark entered successfully." : "Error entering mark: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Enter Marks - Admin Panel</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark" style="background-color: black;">
  <div class="container">
    <a class="navbar-brand" href="dashboard.php">Students Marks Appeal Admin</a>

    <div class="collapse navbar-collapse justify-content-end">
      <ul class="navbar-nav">
        <li class="nav-item"><a href="dashboard.php" class="nav-link">Dashboard</a></li>
        <li class="nav-item"><a href="logout.php" class="nav-link btn btn-outline-light ms-2">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-dark text-white">
            <h4 class="mb-0">Record Student Mark</h4>
        </div>
        <div class="card-body">
            <?php if ($message): ?>
                <div class="alert <?php echo (strpos($message, 'successfully') !== false) ? 'alert-success' : 'alert-danger'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" novalidate>
                <div class="mb-3">
                    <label for="student_id" class="form-label">Select Student</label>
                    <select name="student_id" id="student_id" class="form-select" required>
                        <option value="">-- Select Student --</option>
                        <?php while ($student = $students->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($student['student_id']); ?>">
                                <?php echo htmlspecialchars($student['full_name']) . " ({$student['student_id']})"; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="module_id" class="form-label">Select Module</label>
                    <select name="module_id" id="module_id" class="form-select" required>
                        <option value="">-- Select Module --</option>
                        <?php while ($module = $modules->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($module['module_id']); ?>">
                                <?php echo htmlspecialchars($module['module_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="mark" class="form-label">Mark (0 - 100)</label>
                    <input type="number" name="mark" id="mark" class="form-control" min="0" max="100" required />
                </div>
                <button type="submit" class="btn btn-dark w-100">Submit Mark</button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
