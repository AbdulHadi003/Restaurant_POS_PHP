<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
include('config/code-generator.php');

check_login();

if (isset($_POST['pay'])) {
  // Prevent Posting Blank Values
  if (empty($_POST["pay_code"]) || empty($_POST["pay_amt"]) || empty($_POST['pay_method'])) {
    $err = "Blank Values Not Accepted";
  } else {

    $pay_code = $_POST['pay_code'];
    $order_code = $_GET['order_code'];
    $customer_id = $_GET['customer_id'];
    $pay_amt  = $_POST['pay_amt'];
    $pay_method = $_POST['pay_method'];
    $pay_id = $_POST['pay_id'];

    $order_status = $_GET['order_status'];

    // Insert Captured information into the rpos_payments table
    $postQuery = "INSERT INTO rpos_payments (pay_id, pay_code, order_code, customer_id, pay_amt, pay_method) VALUES(?,?,?,?,?,?)";
    $upQry = "UPDATE rpos_orders SET order_status =? WHERE order_code =?";

    $postStmt = $mysqli->prepare($postQuery);
    $upStmt = $mysqli->prepare($upQry);
    // Bind parameters
    $rc = $postStmt->bind_param('ssssss', $pay_id, $pay_code, $order_code, $customer_id, $pay_amt, $pay_method);
    $rc = $upStmt->bind_param('ss', $order_status, $order_code);

    $postStmt->execute();
    $upStmt->execute();

    // After successful payment, handle reservation and table status
    if ($upStmt && $postStmt) {
      // Fetch the customer_id from the order to check for any reservation
      $reservation_query = "SELECT reservation_id, table_id FROM rpos_reservations WHERE customer_id = ?";
      $stmt_reservation = $mysqli->prepare($reservation_query);
      $stmt_reservation->bind_param('s', $customer_id);
      $stmt_reservation->execute();
      $reservation_result = $stmt_reservation->get_result();
      $reservation = $reservation_result->fetch_object();

      if ($reservation) {
        // Update the reservation status to 'Paid'
        $update_reservation = "UPDATE rpos_reservations SET status = 'Completed' WHERE reservation_id = ?";
        $stmt_update_reservation = $mysqli->prepare($update_reservation);
        $stmt_update_reservation->bind_param('s', $reservation->reservation_id);
        $stmt_update_reservation->execute();

        // Update the table status to 'Available' if the order is 'DineIn'
        $update_table = "UPDATE rpos_tables SET table_status = 'Available' WHERE table_id = ?";
        $stmt_update_table = $mysqli->prepare($update_table);
        $stmt_update_table->bind_param('s', $reservation->table_id);
        $stmt_update_table->execute();
      }

      $success = "Payment Successful. The order has been paid.";
      header("refresh:1; url=receipts.php");
    } else {
      $err = "Please Try Again Or Try Later";
    }
  }
}

require_once('partials/_head.php');
?>

<body>
  <!-- Sidenav -->
  <?php
  require_once('partials/_sidebar.php');
  ?>
  <!-- Main content -->
  <div class="main-content">
    <!-- Top navbar -->
    <?php
    require_once('partials/_topnav.php');
    $order_code = $_GET['order_code'];
    $ret = "SELECT * FROM rpos_orders WHERE order_code ='$order_code' ";
    $stmt = $mysqli->prepare($ret);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($order = $res->fetch_object()) {
        $total = ($order->prod_price * $order->prod_qty);
    ?>
    
    <!-- Header -->
    <div style="background-image: url(assets/img/theme/restro00.jpg); background-size: cover;" class="header pb-8 pt-5 pt-md-8">
    <span class="mask bg-gradient-dark opacity-8"></span>
      <div class="container-fluid">
        <div class="header-body">
        </div>
      </div>
    </div>
    <!-- Page content -->
    <div class="container-fluid mt--8">
      <!-- Table -->
      <div class="row">
        <div class="col">
          <div class="card shadow">
            <div class="card-header border-0">
              <h3>Please Fill All Fields</h3>
            </div>
            <div class="card-body">
              <form method="POST" enctype="multipart/form-data">
                <div class="form-row">
                  <div class="col-md-6">
                    <label>Payment ID</label>
                    <input type="text" name="pay_id" readonly value="<?php echo $payid;?>" class="form-control">
                  </div>
                  <div class="col-md-6">
                    <label>Payment Code</label>
                    <input type="text" name="pay_code" value="<?php echo $mpesaCode; ?>" class="form-control">
                  </div>
                </div>
                <hr>
                <div class="form-row">
                  <div class="col-md-6">
                    <label>Amount ($)</label>
                    <input type="text" name="pay_amt" readonly value="<?php echo $total;?>" class="form-control">
                  </div>
                  <div class="col-md-6">
                    <label>Payment Method</label>
                    <select class="form-control" name="pay_method">
                        <option selected>Cash</option>
                        <option>Paypal</option>
                    </select>
                  </div>
                </div>
                <br>
                <div class="form-row">
                  <div class="col-md-6">
                    <input type="submit" name="pay" value="Pay Order" class="btn btn-success">
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
      <!-- Footer -->
      <?php
      require_once('partials/_footer.php');
      ?>
    </div>
  </div>
  <?php
  require_once('partials/_scripts.php'); 
  }
  ?>
</body>
</html>
