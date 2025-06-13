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
    die("Database error: " . $conn->connect_error);
}

$feedback = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $conn->real_escape_string(trim($_POST['module_code']));
    $name = $conn->real_escape_string(trim($_POST['module_name']));
    $dept = $conn->real_escape_string(trim($_POST['department']));

    if (!$code || !$name || !$dept) {
        $feedback = "⚠️ Please fill in all fields.";
    } else {
        $exists = $conn->query("SELECT * FROM modules WHERE module_code = '$code'");
        if ($exists->num_rows > 0) {
            $feedback = "⚠️ This module code is already registered.";
        } else {
            $insert = $conn->query("INSERT INTO modules (module_code, module_name, department) VALUES ('$code', '$name', '$dept')");
            $feedback = $insert ? "✅ Module successfully added!" : "❌ Failed to save module. Try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Register Module | Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      background-color: #f4f6f9;
    }
    .navbar {
      background-color: #1d3557;
    }
    .form-control:focus {
      border-color: #457b9d;
      box-shadow: 0 0 0 0.2rem rgba(69, 123, 157, 0.25);
    }
    .btn-custom {
      background-color: #457b9d;
      color: white;
    }
    .btn-custom:hover {
      background-color: #1d3557;
    }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container">
    <a class="navbar-brand" href="dashboard.php">Admin Portal</a>
    <div class="d-flex">
      <a href="dashboard.php" class="btn btn-outline-light me-2">Home</a>
      <a href="logout.php" class="btn btn-light">Logout</a>
    </div>
  </div>
</nav>

<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-7">
      <div class="card shadow-lg">
        <div class="card-header bg-white text-center">
          <h4 class="fw-bold text-dark mb-0">Module Registration</h4>
        </div>
        <div class="card-body">
          <?php if (!empty($feedback)): ?>
            <div class="alert <?php echo (strpos($feedback, '✅') !== false) ? 'alert-success' : 'alert-warning'; ?>">
              <?php echo $feedback; ?>
            </div>
          <?php endif; ?>

          <form method="POST">
            <div class="mb-3">
              <label for="module_code" class="form-label">Module Code</label>
              <input type="text" name="module_code" id="module_code" class="form-control" maxlength="20" required>
            </div>

            <div class="mb-3">
              <label for="module_name" class="form-label">Module Title</label>
              <input type="text" name="module_name" id="module_name" class="form-control" maxlength="100" required>
            </div>

            <div class="mb-3">
              <label for="department" class="form-label">Department Name</label>
              <input type="text" name="department" id="department" class="form-control" maxlength="50" required>
            </div>

            <div class="d-grid">
              <button type="submit" class="btn btn-custom">Save Module</button>
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
