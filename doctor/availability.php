<?php
// doctor/availability.php
session_start();
require_once __DIR__ . '/../db.php';

// 1. Ensure only doctors can access
if (!isset($_SESSION['doctor_id'])) {
    header("Location: ../staff_login.html");
    exit();
}
$docId = $_SESSION['doctor_id'];

// 2. Fetch existing availability rules for this doctor
$stmt = $pdo->prepare("
    SELECT id, day_of_week, start_time, end_time, slot_duration_min
    FROM doctor_availability
    WHERE doctor_id = ?
    ORDER BY day_of_week, start_time
");
$stmt->execute([$docId]);
$rules = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper array for day names
$days = [
    0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday',
    3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Availability</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
  <div class="container">
    <h2>Your Weekly Availability</h2>
    <table class="table table-striped">
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
      <?php if (count($rules) === 0): ?>
        <tr><td colspan="5" class="text-center">No availability set yet.</td></tr>
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
                 onclick="return confirm('Delete this rule?');">
                Delete
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
      </tbody>
    </table>

    <h3>Add New Rule</h3>
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
        <label class="form-label">Slot Min</label>
        <input type="number" name="slot_duration_min" class="form-control" value="30" min="1" required>
      </div>
      <div class="col-md-1 align-self-end">
        <button type="submit" class="btn btn-primary">Add</button>
      </div>
    </form>
  </div>
</body>
</html>
