<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit;
}

$username = $_SESSION['username'] ?? 'Administrator';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Dashboard | Appeal Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      background-color: #1c1f26;
      color: #f8f9fa;
    }
    .navbar {
      background-color: #12151c;
    }
    .dashboard-header {
      margin-top: 40px;
      margin-bottom: 30px;
      text-align: center;
    }
    .card-option {
      background-color: #292c35;
      color: white;
      border: 1px solid #444;
      transition: 0.3s;
    }
    .card-option:hover {
      background-color: #383c46;
      transform: scale(1.03);
    }
    a.card-link {
      text-decoration: none;
      color: inherit;
    }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container">
    <a class="navbar-brand" href="#">Appeal Management</a>
    <div class="d-flex">
      <span class="text-light me-3">Hello, <?php echo htmlspecialchars($username); ?></span>
      <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
  </div>
</nav>

<div class="container">
  <div class="dashboard-header">
    <h2>Admin Dashboard</h2>
    <p class="text-secondary">Manage all academic appeal records below</p>
  </div>

  <div class="row g-4">
    <div class="col-md-6 col-lg-4">
      <a href="view_appeals.php" class="card-link">
        <div class="card card-option text-center p-4">
          <h5>View & Update Appeals</h5>
        </div>
      </a>
    </div>
    <div class="col-md-6 col-lg-4">
      <a href="add_student.php" class="card-link">
        <div class="card card-option text-center p-4">
          <h5>Register New Student</h5>
        </div>
      </a>
    </div>
    <div class="col-md-6 col-lg-4">
      <a href="add_module.php" class="card-link">
        <div class="card card-option text-center p-4">
          <h5>Create Module Record</h5>
        </div>
      </a>
    </div>
    <div class="col-md-6 col-lg-4">
      <a href="enter_marks.php" class="card-link">
        <div class="card card-option text-center p-4">
          <h5>Enter Student Marks</h5>
        </div>
      </a>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
