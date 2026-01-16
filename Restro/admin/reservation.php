<?php 
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

// Handle cancellation
if (isset($_GET['cancel_id'])) {
    $reservation_id = $_GET['cancel_id'];

    // Get the table ID from the reservation
    $stmt = $mysqli->prepare("SELECT table_id FROM rpos_reservations WHERE reservation_id = ?");
    $stmt->bind_param('i', $reservation_id);
    $stmt->execute();
    $stmt->bind_result($table_id);
    $stmt->fetch();
    $stmt->close();

    // Update reservation status to 'Cancelled'
    $stmt = $mysqli->prepare("UPDATE rpos_reservations SET status = 'Cancelled' WHERE reservation_id = ?");
    $stmt->bind_param('i', $reservation_id);
    $stmt->execute();
    $stmt->close();

    // Update table status back to 'Available'
    $stmt = $mysqli->prepare("UPDATE rpos_tables SET table_status = 'Available' WHERE table_id = ?");
    $stmt->bind_param('i', $table_id);
    $stmt->execute();
    $stmt->close();

    // Redirect to avoid re-submission
    header("Location: reservation.php");
    exit();
}

require_once('partials/_head.php');
?>

<body>
  <?php require_once('partials/_sidebar.php'); ?>
  <div class="main-content">
    <?php require_once('partials/_topnav.php'); ?>

    <div style="background-image: url(assets/img/theme/restro00.jpg); background-size: cover;" class="header pb-8 pt-5 pt-md-8">
      <span class="mask bg-gradient-dark opacity-8"></span>
      <div class="container-fluid">
        <div class="header-body"></div>
      </div>
    </div>

    <div class="container-fluid mt--8">
      <div class="row">
        <div class="col">
          <div class="card shadow">
            <div class="card-header border-0">
              <a href="add_reservation.php" class="btn btn-outline-success">
                <i class="fas fa-plus"></i> Add New Reservation
              </a>
            </div>
            <div class="table-responsive">
              <table class="table align-items-center table-flush">
                <thead class="thead-light">
                  <tr>
                    <th scope="col">Reservation ID</th>
                    <th scope="col">Customer Name</th>
                    <th scope="col">Table ID</th>
                    <th scope="col">Time</th>
                    <th scope="col">Status</th>
                    <th scope="col">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $ret = "SELECT * FROM rpos_reservations ORDER BY reservation_id DESC";
                  $stmt = $mysqli->prepare($ret);
                  $stmt->execute();
                  $res = $stmt->get_result();
                  while ($reservation = $res->fetch_object()) {
                    // Fetch customer name
                    $customerQuery = "SELECT customer_name FROM rpos_customers WHERE customer_id = ?";
                    $customerStmt = $mysqli->prepare($customerQuery);
                    $customerStmt->bind_param('s', $reservation->customer_id);
                    $customerStmt->execute();
                    $customerResult = $customerStmt->get_result();
                    $customer = $customerResult->fetch_object();
                    $customer_name = $customer ? $customer->customer_name : 'Unknown';

                    $status = $reservation->status ? $reservation->status : 'Not Set';
                  ?>
                    <tr>
                      <td><?php echo $reservation->reservation_id; ?></td>
                      <td><?php echo htmlspecialchars($customer_name); ?></td>
                      <td><?php echo $reservation->table_id; ?></td>
                      <td><?php echo $reservation->reservation_time; ?></td>
                      <td><?php echo $status; ?></td>
                      <td>
                        <?php if ($reservation->status === NULL): ?>
                          <a href="reservation.php?cancel_id=<?php echo $reservation->reservation_id; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to cancel this reservation?');">
                            Cancel
                          </a>
                        <?php else: ?>
                          <span class="text-muted">-</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
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
