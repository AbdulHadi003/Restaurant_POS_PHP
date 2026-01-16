<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
include('config/code-generator.php');

check_login();

$reserved_table_id = null; // Default value for reserved table ID
$reservation_message = ''; // Default message for reservation status

if (isset($_POST['make'])) {
  if (!isset($_POST["prod_qty"]) || $_POST["prod_qty"] === "" || $_POST["prod_qty"] <= 0) {
    $err = "Invalid Quantity! Quantity must be greater than 0.";
  }
  // Prevent Posting Blank Values
  else if (empty($_POST["order_code"]) || empty($_POST["customer_name"]) || empty($_GET['prod_price']) || empty($_POST['order_type'] )) {
    $err = "Blank Values Not Accepted";
  } else {
    $order_id = $_POST['order_id'];
    $order_code  = $_POST['order_code'];
    $customer_id = $_POST['customer_id'];
    $customer_name = $_POST['customer_name'];
    $prod_id  = $_GET['prod_id'];
    $prod_name = $_GET['prod_name'];
    $prod_price = $_GET['prod_price'];
    $prod_qty = $_POST['prod_qty'];
    $order_type = $_POST['order_type'];  // Get the selected order type

    // If DineIn is selected, check if a reservation exists for the customer
    if ($order_type == "DineIn") {
      // Query to check for ongoing reservation for the customer
      $check_reservation =  "SELECT r.table_id, r.reservation_id  FROM rpos_reservations r 
      JOIN rpos_tables t ON r.table_id = t.table_id 
      WHERE t.table_status = 'Reserved' and r.customer_id = ? LIMIT 1";
      $stmt = $mysqli->prepare($check_reservation);
      $stmt->bind_param('s', $customer_id);
      $stmt->execute();
      $result = $stmt->get_result();

      if ($result->num_rows > 0 ) {
        // Reservation found, fetch table_id and reservation_id
        $reservation = $result->fetch_object();
        $reserved_table_id = $reservation->table_id;
        $reservation_id = $reservation->reservation_id; // Get the reservation_id
        $reservation_message = "Reserved Table ID: " . $reserved_table_id; // Message to display reserved table

        // Update the reservation status to "Ongoing" in rpos_reservations table
        $update_reservation_status = "UPDATE rpos_reservations SET status = 'Ongoing' WHERE reservation_id = ?";
        $update_stmt = $mysqli->prepare($update_reservation_status);
        $update_stmt->bind_param('i', $reservation_id);
        $update_stmt->execute();
        $postQuery = "INSERT INTO rpos_orders (order_id, order_code, customer_id, customer_name, prod_id, prod_name, prod_price, prod_qty, order_type, table_id) VALUES(?,?,?,?,?,?,?,?,?,?)";
        $postStmt = $mysqli->prepare($postQuery);
        // Bind parameters
        $rc = $postStmt->bind_param('sssssssssi', $order_id, $order_code, $customer_id, $customer_name, $prod_id, $prod_name, $prod_price, $prod_qty, $order_type, $reserved_table_id);
        $postStmt->execute();
    
        if ($postStmt) {
          $success = "Order Submitted" && header("refresh:1; url=payments.php");
          
        } else {
          $err = "Please Try Again Or Try Later";
        }
      } else {
        // No reservation found, show "Not Found"
        $reservation_message = "No Reservation Found. Please Reserve First.";
      }
    }
    if ($order_type == "TakeAway") {

      $postQuery = "INSERT INTO rpos_orders (order_id, order_code, customer_id, customer_name, prod_id, prod_name, prod_price, prod_qty, order_type, table_id) VALUES(?,?,?,?,?,?,?,?,?,?)";
      $postStmt = $mysqli->prepare($postQuery);
      // Bind parameters
      $rc = $postStmt->bind_param('sssssssssi', $order_id, $order_code, $customer_id, $customer_name, $prod_id, $prod_name, $prod_price, $prod_qty, $order_type, $reserved_table_id);
      $postStmt->execute();
  
      if ($postStmt) {
        $success = "Order Submitted" && header("refresh:1; url=payments.php");
        
      } else {
        $err = "Please Try Again Or Try Later";
      }  
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
                  <div class="col-md-4">
                    <label>Customer Name</label>
                    <select class="form-control" name="customer_name" id="custName" onChange="getCustomer(this.value)">
                      <option value="">Select Customer Name</option>
                      <?php
                      // Load All Customers
                      $ret = "SELECT * FROM rpos_customers";
                      $stmt = $mysqli->prepare($ret);
                      $stmt->execute();
                      $res = $stmt->get_result();
                      while ($cust = $res->fetch_object()) {
                      ?>
                        <option><?php echo $cust->customer_name; ?></option>
                      <?php } ?>
                    </select>
                    <input type="hidden" name="order_id" value="<?php echo $orderid; ?>" class="form-control">
                  </div>

                  <div class="col-md-4">
                    <label>Customer ID</label>
                    <input type="text" name="customer_id" readonly id="customerID" class="form-control">
                  </div>

                  <div class="col-md-4">
                    <label>Order Code</label>
                    <input type="text" name="order_code" value="<?php echo $alpha; ?>-<?php echo $beta; ?>" class="form-control">
                  </div>
                </div>
                <hr>
                <?php
                $prod_id = $_GET['prod_id'];
                $ret = "SELECT * FROM rpos_products WHERE prod_id = '$prod_id'";
                $stmt = $mysqli->prepare($ret);
                $stmt->execute();
                $res = $stmt->get_result();
                while ($prod = $res->fetch_object()) {
                ?>
                  <div class="form-row">
                    <div class="col-md-6">
                      <label>Product Price ($)</label>
                      <input type="text" readonly name="prod_price" value="$ <?php echo $prod->prod_price; ?>" class="form-control">
                    </div>
                    <div class="col-md-6">
                      <label>Product Quantity</label>
                      <input type="text" name="prod_qty" class="form-control" value=""/>
                    </div>
                  </div>
                <?php } ?>
                <br>
                <!-- Order Type Dropdown -->
                <div class="form-row">
                  <div class="col-md-6">
                    <label>Order Type</label>
                    <select class="form-control" name="order_type" onChange="toggleReservedTable()">
                      <option value="TakeAway">TakeAway</option>
                      <option value="DineIn">DineIn</option>
                    </select>
                  </div>
                </div>
                <br>

                <!-- Reserved Table Display -->
                <div id="reservedTableDisplay">
                  <?php if ($reserved_table_id) { ?>
                    <p>Reserved Table: <?php echo $reserved_table_id; ?></p>
                  <?php } else { ?>
                    <p><?php echo $reservation_message; ?></p>
                  <?php } ?>
                </div>

                <div class="form-row">
                  <div class="col-md-6">
                    <input type="submit" name="make" value="Make Order" class="btn btn-success">
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
  ?>
</body>

</html>
