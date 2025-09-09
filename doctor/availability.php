<?php
// doctor/availability.php
session_start();
require_once __DIR__ . '/../db.php';

// 1. Ensure only logged-in doctors can access
if (!isset($_SESSION['doctor_id'])) {
    header("Location: ../staff_login.html");
    exit();
}
$docId      = $_SESSION['doctor_id'];
$doctorName = $_SESSION['doctor_name'] ?? 'Doctor';

// 2. Fetch availability rules
$stmt = $pdo->prepare("
    SELECT id, day_of_week, start_time, end_time, slot_duration_min
    FROM doctor_availability
    WHERE doctor_id = ?
    ORDER BY day_of_week, start_time
");
$stmt->execute([$docId]);
$rules = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Weekday names
$days = [
    0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday',
    3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'
];

// Determine current page for sidebar highlighting
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Availability - Clinic PMS</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background-color: #f5f9ff; }
    .sidebar { background-color: #fff; min-height: 100vh; box-shadow: 2px 0 10px rgba(0,0,0,0.05); }
  </style>
</head>
<body>
<div class="container-fluid">
  <div class="row">
    <!-- Sidebar -->
    <div class="col-md-2 sidebar py-4 px-3">
      <div class="text-center mb-4">
        <img src="../assets/img/doctor1.jpg" class="rounded-circle" width="80" alt="Doctor">
        <h5 class="mt-2"><?= htmlspecialchars($doctorName) ?></h5>
        <p class="text-muted mb-0">Doctor</p>
      </div>
      <ul class="nav flex-column">
        <li class="nav-item mb-2">
          <a href="dashboard.php"
             class="nav-link <?= $currentPage==='dashboard.php' ? 'text-primary fw-semibold' : '' ?>">
            🧑‍⚕️ Dashboard
          </a>
        </li>
        <li class="nav-item mb-2">
          <a href="availability.php"
             class="nav-link <?= $currentPage==='availability.php' ? 'text-primary fw-semibold' : '' ?>">
            ⏰ Manage Availability
          </a>
        </li>
        <li class="nav-item mb-2">
          <a href="appointments_today.php"
             class="nav-link <?= $currentPage==='appointments_today.php' ? 'text-primary fw-semibold' : '' ?>">
            📅 Appointments Today
          </a>
        </li>
        <li class="nav-item mb-2">
          <a href="medical_history.php"
             class="nav-link <?= $currentPage==='medical_history.php' ? 'text-primary fw-semibold' : '' ?>">
            📁 Medical History
          </a>
        </li>
        <li class="nav-item mt-3">
          <a href="../logout.php" class="btn btn-outline-danger w-100">Logout</a>
        </li>
      </ul>
    </div>

    <!-- Main Content -->
    <div class="col-md-10 p-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Your Weekly Availability</h2>
        <a href="dashboard.php" class="btn btn-outline-primary">← Back to Dashboard</a>
      </div>

      <table class="table table-striped mb-4">
        <thead>
          <tr>
            <th>Day</th>
            <th>Start</th>
            <th>End</th>
            <th>Slot (min)</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($rules)): ?>
            <tr>
              <td colspan="5" class="text-center">No availability set yet.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($rules as $r): ?>
              <tr>
                <td><?= htmlspecialchars($days[$r['day_of_week']]) ?></td>
                <td><?= htmlspecialchars($r['start_time']) ?></td>
                <td><?= htmlspecialchars($r['end_time']) ?></td>
                <td><?= htmlspecialchars($r['slot_duration_min']) ?></td>
                <td>
                  <a href="delete_availability.php?id=<?= $r['id'] ?>"
                     class="btn btn-sm btn-danger"
                     onclick="return confirm('Delete this availability rule?')">
                    Delete
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>

      <h3>Add New Availability Rule</h3>
      <form action="save_availability.php" method="POST" class="row g-3">
        <div class="col-md-3">
          <label class="form-label">Day of Week</label>
          <select name="day_of_week" class="form-select" required>
            <?php foreach ($days as $num => $name): ?>
              <option value="<?= $num ?>"><?= $name ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Start Time</label>
          <input type="time" name="start_time" class="form-control" required>
        </div>
        <div class="col-md-3">
          <label class="form-label">End Time</label>
          <input type="time" name="end_time" class="form-control" required>
        </div>
        <div class="col-md-2">
          <label class="form-label">Slot (min)</label>
          <input type="number" name="slot_duration_min" class="form-control" value="30" min="1" required>
        </div>
        <div class="col-md-1 d-flex align-items-end">
          <button type="submit" class="btn btn-primary w-100">Add</button>
        </div>
      </form>
    </div>
  </div>
</div>
</body>
</html>
