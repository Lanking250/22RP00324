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
    die("Connection failed: " . $conn->connect_error);
}

$feedback = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sid = $conn->real_escape_string(trim($_POST['student_id']));
    $name = $conn->real_escape_string(trim($_POST['full_name']));
    $phone = $conn->real_escape_string(trim($_POST['phone']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $dept = $conn->real_escape_string(trim($_POST['department']));

    if (!$sid || !$name || !$phone || !$email || !$dept) {
        $feedback = "⚠️ All fields must be completed.";
    } else {
        $check = $conn->query("SELECT * FROM students WHERE student_id = '$sid'");
        if ($check->num_rows > 0) {
            $feedback = "⚠️ This Student ID already exists.";
        } else {
            $save = $conn->query("INSERT INTO students (student_id, full_name, phone_number, email) VALUES ('$sid', '$name', '$phone', '$email')");
            $feedback = $save ? "✅ Student record added!" : "❌ Failed to add student: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Student Registration</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      background: #f7f9fc;
    }
    .navbar {
      background-color: #0b4f6c;
    }
    .form-control:focus {
      border-color: #3c9ee7;
      box-shadow: 0 0 0 0.2rem rgba(60, 158, 231, 0.25);
    }
    .btn-save {
      background-color: #3c9ee7;
      color: #fff;
    }
    .btn-save:hover {
      background-color: #226e99;
    }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container">
    <a class="navbar-brand" href="dashboard.php">Appeal Admin Panel</a>
    <div class="d-flex">
      <a href="dashboard.php" class="btn btn-outline-light me-2">Home</a>
      <a href="logout.php" class="btn btn-light">Logout</a>
    </div>
  </div>
</nav>

<div class="container my-5">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card shadow-sm">
        <div class="card-header text-center bg-white">
          <h4 class="fw-bold text-dark mb-0">New Student Registration</h4>
        </div>
        <div class="card-body">
          <?php if (!empty($feedback)): ?>
            <div class="alert <?php echo (strpos($feedback, '✅') !== false) ? 'alert-success' : 'alert-warning'; ?>">
              <?php echo htmlspecialchars($feedback); ?>
            </div>
          <?php endif; ?>

          <form method="POST" novalidate>
            <div class="mb-3">
              <label for="student_id" class="form-label">Student ID</label>
              <input type="text" name="student_id" id="student_id" class="form-control" required maxlength="20" />
            </div>
            <div class="mb-3">
              <label for="full_name" class="form-label">Full Name</label>
              <input type="text" name="full_name" id="full_name" class="form-control" required maxlength="100" />
            </div>
            <div class="mb-3">
              <label for="phone" class="form-label">Phone Number</label>
              <input type="tel" name="phone" id="phone" class="form-control" required pattern="[0-9]+" maxlength="15" />
            </div>
            <div class="mb-3">
              <label for="email" class="form-label">Email Address</label>
              <input type="email" name="email" id="email" class="form-control" required maxlength="100" />
            </div>
            <div class="mb-3">
              <label for="department" class="form-label">Department</label>
              <input type="text" name="department" id="department" class="form-control" required maxlength="50" />
            </div>
            <div class="d-grid">
              <button type="submit" class="btn btn-save">Register Student</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
