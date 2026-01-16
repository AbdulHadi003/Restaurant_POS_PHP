<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

$table_available = false;

// Check if any tables are available
$table_check = $mysqli->query("SELECT * FROM rpos_tables WHERE table_status = 'Available'");
if ($table_check && $table_check->num_rows > 0) {
    $table_available = true;
}

if ($table_available && isset($_POST['add_reservation'])) {
    $customer_id = $_POST['customer_id'];
    $table_id = $_POST['table_id'];

    if (empty($customer_id) || empty($table_id)) {
        $err = "Both fields are required";
    } else {
        // Insert reservation
        $query = "INSERT INTO rpos_reservations (customer_id, table_id) VALUES (?, ?)";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('si', $customer_id, $table_id);
        if ($stmt->execute()) {
            // Update table status to Reserved
            $update_table = $mysqli->prepare("UPDATE rpos_tables SET table_status = 'Reserved' WHERE table_id = ?");
            $update_table->bind_param('i', $table_id);
            $update_table->execute();

            $success = "Reservation Added Successfully";
            header("refresh:1; url=reservation.php");
        } else {
            $err = "Please Try Again";
        }
    }
}

require_once('partials/_head.php');
?>

<body>
  <?php require_once('partials/_sidebar.php'); ?>
  <div class="main-content">
    <?php require_once('partials/_topnav.php'); ?>

    <div style="background-image: url(assets/img/theme/restro00.jpg); background-size: cover;" class="header pb-8 pt-5 pt-md-8">
      <span class="mask bg-gradient-dark opacity-8"></span>
    </div>

    <div class="container-fluid mt--8">
      <div class="row">
        <div class="col">
          <div class="card shadow">
            <div class="card-header border-0">
              <h3>Make a Reservation</h3>
            </div>
            <div class="card-body">
              <?php if (isset($err)) echo "<div class='alert alert-danger'>$err</div>"; ?>
              <?php if (isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>

              <?php if (!$table_available): ?>
                <div class="alert alert-warning text-center">
                  <strong>Sorry!</strong> We are full at the moment. Please opt for <a href="make_order.php" class="alert-link">Takeaway</a>.
                </div>
                <script>
                  setTimeout(function() {
                    window.location.href = "reservation.php";
                  }, 2000);
                </script>
              <?php else: ?>
                <form method="POST">
                  <div class="form-group">
                    <label>Customer</label>
                    <select name="customer_id" class="form-control" required>
                      <option value="">Select Customer</option>
                      <?php
                      $result = $mysqli->query("SELECT * FROM rpos_customers");
                      while ($cust = $result->fetch_object()) {
                          echo "<option value='{$cust->customer_id}'>{$cust->customer_id} - {$cust->customer_name}</option>";
                      }
                      ?>
                    </select>
                  </div>

                  <div class="form-group">
                    <label>Table</label>
                    <select name="table_id" class="form-control" required>
                      <option value="">Select Table</option>
                      <?php
                      while ($table = $table_check->fetch_object()) {
                          echo "<option value='{$table->table_id}'>Table #{$table->table_id}</option>";
                      }
                      ?>
                    </select>
                  </div>

                  <button type="submit" name="add_reservation" class="btn btn-primary">Make Reservation</button>
                </form>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
      <?php require_once('partials/_footer.php'); ?>
    </div>
  </div>
  <?php require_once('partials/_scripts.php'); ?>
</body>
</html>
