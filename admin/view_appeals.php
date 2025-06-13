<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit;
}

// DB connection
$host = 'localhost';
$db = 'Appeal_database';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appeal_id'])) {
    $appeal_id = (int)$_POST['appeal_id'];

    if (isset($_POST['new_mark']) && is_numeric($_POST['new_mark'])) {
        $new_mark = floatval($_POST['new_mark']);

        $stmt = $conn->prepare("SELECT student_id, module_id FROM appeals WHERE appeal_id = ?");
        $stmt->bind_param("i", $appeal_id);
        $stmt->execute();
        $stmt->bind_result($student_id, $module_id);
        $stmt->fetch();
        $stmt->close();

        if ($student_id && $module_id) {
            $conn->begin_transaction();
            try {
                $stmt = $conn->prepare("UPDATE appeals SET new_mark = ?, status = 'solved' WHERE appeal_id = ?");
                $stmt->bind_param("di", $new_mark, $appeal_id);
                $stmt->execute();
                $stmt->close();

                $stmt = $conn->prepare("UPDATE marks SET mark = ? WHERE student_id = ? AND module_id = ?");
                $stmt->bind_param("dii", $new_mark, $student_id, $module_id);
                $stmt->execute();
                $stmt->close();

                $conn->commit();
                $message = "Score updated and marked as solved.";
            } catch (Exception $e) {
                $conn->rollback();
                $message = "Error: " . $e->getMessage();
            }
        } else {
            $message = "Appeal data not found.";
        }

    } elseif (isset($_POST['status']) && in_array($_POST['status'], ['pending', 'approved', 'rejected'])) {
        $status = $conn->real_escape_string($_POST['status']);
        $conn->query("UPDATE appeals SET status='$status' WHERE appeal_id=$appeal_id");
        $message = "Status updated.";
    }
}

// Fetch appeals
$sql = "SELECT a.appeal_id, a.student_id, s.full_name, a.module_id, m.module_name, a.reason, a.status, a.new_mark
        FROM appeals a
        JOIN students s ON a.student_id = s.student_id
        JOIN modules m ON a.module_id = m.module_id
        ORDER BY a.status, a.appeal_id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Manage Appeals</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      background-color: #20232a;
      color: #f0f0f0;
    }
    .navbar {
      background-color: #1a1d24;
    }
    .card-appeal {
      background-color: #2c3039;
      border: 1px solid #444;
      padding: 1.2rem;
      border-radius: 12px;
      margin-bottom: 1rem;
    }
    .badge-custom {
      font-size: 0.85rem;
      padding: 0.4em 0.7em;
    }
    .form-control-sm, .form-select-sm {
      max-width: 120px;
    }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container">
    <a class="navbar-brand" href="dashboard.php">Admin - Appeals</a>
    <div class="d-flex">
      <a href="dashboard.php" class="btn btn-outline-light btn-sm me-2">Dashboard</a>
      <a href="logout.php" class="btn btn-light btn-sm">Logout</a>
    </div>
  </div>
</nav>

<div class="container mt-4">
  <h2 class="mb-4">Appeal Management Panel</h2>

  <?php if (isset($message)): ?>
    <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
  <?php endif; ?>

  <?php if ($result && $result->num_rows > 0): ?>
    <?php while ($row = $result->fetch_assoc()): ?>
      <div class="card-appeal">
        <h5>#<?php echo $row['appeal_id']; ?> - <?php echo htmlspecialchars($row['full_name']); ?> (<?php echo htmlspecialchars($row['student_id']); ?>)</h5>
        <p><strong>Module:</strong> <?php echo htmlspecialchars($row['module_name']); ?> (<?php echo $row['module_id']; ?>)</p>
        <p><strong>Reason:</strong> <?php echo htmlspecialchars($row['reason']); ?></p>
        <p><strong>Status:</strong>
          <?php 
            $status_colors = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger', 'solved' => 'info'];
            $color = $status_colors[$row['status']] ?? 'secondary';
            echo '<span class="badge bg-' . $color . ' badge-custom">' . ucfirst($row['status']) . '</span>';
          ?>
          &nbsp;&nbsp;
          <strong>Updated Mark:</strong> 
          <?php echo is_null($row['new_mark']) ? '<em>Not updated</em>' : htmlspecialchars($row['new_mark']); ?>
        </p>

        <div class="d-flex flex-wrap gap-3">
          <!-- Update Mark -->
          <form method="POST" class="d-flex flex-wrap align-items-center gap-2">
            <input type="hidden" name="appeal_id" value="<?php echo $row['appeal_id']; ?>">
            <input type="number" name="new_mark" step="0.01" class="form-control form-control-sm" placeholder="New score" required>
            <button type="submit" class="btn btn-success btn-sm">Apply</button>
          </form>

          <!-- Update Status -->
          <form method="POST" class="d-flex flex-wrap align-items-center gap-2">
            <input type="hidden" name="appeal_id" value="<?php echo $row['appeal_id']; ?>">
            <select name="status" class="form-select form-select-sm" required>
              <option value="pending" <?php if($row['status'] == 'pending') echo 'selected'; ?>>Pending</option>
              <option value="approved" <?php if($row['status'] == 'approved') echo 'selected'; ?>>Approved</option>
              <option value="rejected" <?php if($row['status'] == 'rejected') echo 'selected'; ?>>Rejected</option>
            </select>
            <button type="submit" class="btn btn-primary btn-sm">Update</button>
          </form>
        </div>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p>No appeals submitted yet.</p>
  <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
