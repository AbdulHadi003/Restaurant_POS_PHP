<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

// Add Table
if (isset($_POST['add_table'])) {
  // Find the lowest available table_id
  $query = "SELECT table_id FROM rpos_tables ORDER BY table_id ASC";
  $result = $mysqli->query($query);

  $expected_id = 1;
  while ($row = $result->fetch_assoc()) {
    if ((int)$row['table_id'] != $expected_id) {
      break;
    }
    $expected_id++;
  }

  // Insert table with available ID and 'Available' status
  $insert = "INSERT INTO rpos_tables (table_id, table_status) VALUES (?, 'Available')";
  $stmt = $mysqli->prepare($insert);
  $stmt->bind_param('i', $expected_id);
  $stmt->execute();
  $stmt->close();

  if ($stmt) {
    $success = "Table Added Successfully";
    header("refresh:1; url=table.php");
  } else {
    $err = "Something went wrong. Try again.";
  }
}
?>

<?php require_once('partials/_head.php'); ?>
<body>
  <!-- Sidebar -->
  <?php require_once('partials/_sidebar.php'); ?>
  <!-- Main content -->
  <div class="main-content">
    <?php require_once('partials/_topnav.php'); ?>

    <!-- Header -->
    <div class="header pb-8 pt-5 pt-md-8" style="background-image: url(assets/img/theme/restro00.jpg); background-size: cover;">
      <span class="mask bg-gradient-dark opacity-8"></span>
      <div class="container-fluid">
        <div class="header-body">
          <h2 class="text-white">Add New Table</h2>
        </div>
      </div>
    </div>

    <!-- Page content -->
    <div class="container-fluid mt--7">
      <div class="row justify-content-center">
        <div class="col-xl-6 order-xl-1">
          <div class="card bg-secondary shadow">
            <div class="card-body">
              <form method="post">
                <div class="pl-lg-4">
                  <div class="form-group">
                    <label class="form-control-label">Click button below to add a new table</label>
                  </div>
                  <button type="submit" name="add_table" class="btn btn-success">
                    <i class="fas fa-plus"></i> Add Table
                  </button>
                </div>
              </form>
              <?php if (isset($success)) { echo "<div class='alert alert-success mt-3'>$success</div>"; } ?>
              <?php if (isset($err)) { echo "<div class='alert alert-danger mt-3'>$err</div>"; } ?>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <?php require_once('partials/_footer.php'); ?>
    </div>
  </div>

  <?php require_once('partials/_scripts.php'); ?>
</body>
</html>
