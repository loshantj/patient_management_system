<?php
session_start();
if (!isset($_SESSION['doctor_id'])) {
    header("Location: ../staff_login.html");
    exit();
}
$doctorName = $_SESSION['doctor_name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Doctor Dashboard - Clinic PMS</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      background-color: #f5f9ff;
    }
    .sidebar {
      background-color: #fff;
      min-height: 100vh;
      box-shadow: 2px 0 10px rgba(0,0,0,0.05);
    }
    .sidebar h5 {
      margin-top: 20px;
      font-weight: 600;
    }
    .card {
      border: none;
      border-radius: 10px;
    }
    .card-title {
      font-size: 18px;
    }
    .btn-checkup {
      background-color: #198754;
      color: white;
    }
    .btn-next {
      background-color: #0d6efd;
      color: white;
    }
  </style>
</head>
<body>

<div class="container-fluid">
  <div class="row">
    <!-- Sidebar -->
    <div class="col-md-2 sidebar py-4 px-3">
      <div class="text-center mb-4">
        <img src="../assets/img/doctor1.jpg" class="rounded-circle" width="80" />
        <h5 class="mt-2"><?= htmlspecialchars($doctorName) ?></h5>
        <p class="text-muted mb-0">Doctor</p>
      </div>
      <ul class="nav flex-column">
        <li class="nav-item mb-2"><a href="#" class="nav-link text-primary fw-semibold">🧑‍⚕️ My Patients</a></li>
        <li class="nav-item mb-2"><a href="#" class="nav-link">📅 Appointments Today</a></li>
        <li class="nav-item mb-2"><a href="#" class="nav-link">📁 Medical History</a></li>
        <li class="nav-item mt-3"><a href="../logout.php" class="btn btn-outline-danger w-100">Logout</a></li>
      </ul>
    </div>

    <!-- Main Content -->
    <div class="col-md-10 p-4">
      <div class="row mb-4 text-center">
        <div class="col-md-3">
          <div class="card p-3">
            <h5 class="card-title text-muted">Current Queue</h5>
            <h4>#12 <br><small class="text-secondary">Chris Evans</small></h4>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card p-3">
            <h5 class="card-title text-muted">Total Patients Today</h5>
            <h4>8</h4>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card p-3">
            <h5 class="card-title text-muted">Completed</h5>
            <h4>3</h4>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card p-3">
            <h5 class="card-title text-muted">Next Patient</h5>
            <h4>Rachel Green <br><small class="text-secondary">11:10 AM</small></h4>
          </div>
        </div>
      </div>

      <div class="row g-4">
        <!-- Patient Details -->
        <div class="col-md-5">
          <div class="card p-3">
            <h5 class="fw-bold">Chris Evans</h5>
            <p class="mb-1">Male | 32 yrs</p>
            <p class="mb-1">Appointed: 10:45 AM</p>
            <p class="mb-1"><strong>📞</strong> +94 712 345 678</p>
            <p class="mb-1"><strong>📍</strong> Colombo, Sri Lanka</p>
            <p class="mb-1"><strong>Reason:</strong> Dermatology</p>
            <hr>
            <h6 class="fw-semibold">Previous Notes:</h6>
            <ul class="text-muted small">
              <li><strong>2024-05-14:</strong> Skin rash, prescribed ointment</li>
              <li><strong>2024-02-02:</strong> Allergy, antihistamine given</li>
            </ul>
          </div>
        </div>

        <!-- Consultation Form -->
        <div class="col-md-7">
          <div class="card p-3">
            <h5 class="fw-bold mb-3">🩺 Consultation Notes</h5>
            <form action="#" method="POST">
              <div class="mb-3">
                <label>Diagnosis</label>
                <input type="text" class="form-control" name="diagnosis" placeholder="Enter diagnosis" />
              </div>
              <div class="mb-3">
                <label>Prescription</label>
                <input type="text" class="form-control" name="prescription" placeholder="Medication, dosage, etc." />
              </div>
              <div class="mb-3">
                <label>Treatment Plan</label>
                <textarea class="form-control" name="treatment" rows="3" placeholder="Describe brief treatment plan"></textarea>
              </div>
              <div class="d-flex justify-content-end gap-3">
                <button type="submit" class="btn btn-checkup">✓ Done Checkup</button>
                <a href="#" class="btn btn-next">⏭️ Call Next Patient</a>
              </div>
            </form>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

</body>
</html>
