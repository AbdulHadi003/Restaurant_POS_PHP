<?php 
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

//Cancel Order
if (isset($_GET['cancel'])) {
    $order_id = $_GET['cancel'];

    // Fetch customer_id from the order
    $query = "SELECT customer_id FROM rpos_orders WHERE order_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('s', $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_object();
    $customer_id = $order->customer_id;
    
    // Fetch reservation_id from rpos_reservations table using customer_id
    $reservation_query = "SELECT reservation_id, table_id FROM rpos_reservations WHERE customer_id = ?";
    $stmt_reservation = $mysqli->prepare($reservation_query);
    $stmt_reservation->bind_param('s', $customer_id);
    $stmt_reservation->execute();
    $reservation_result = $stmt_reservation->get_result();
    $reservation = $reservation_result->fetch_object();

    if ($reservation) {
        // Cancel the reservation and update the table status
        $reservation_id = $reservation->reservation_id;
        $table_id = $reservation->table_id;

        // Update reservation status to 'Cancelled'
        $update_reservation = "UPDATE rpos_reservations SET status = 'Cancelled' WHERE reservation_id = ?";
        $stmt_update_reservation = $mysqli->prepare($update_reservation);
        $stmt_update_reservation->bind_param('s', $reservation_id);
        $stmt_update_reservation->execute();

        // Update table status to 'Available'
        $update_table = "UPDATE rpos_tables SET table_status = 'Available' WHERE table_id = ?";
        $stmt_update_table = $mysqli->prepare($update_table);
        $stmt_update_table->bind_param('s', $table_id);
        $stmt_update_table->execute();
    }

    // Delete the order from the rpos_orders table
    $delete_order = "DELETE FROM rpos_orders WHERE order_id = ?";
    $stmt_delete_order = $mysqli->prepare($delete_order);
    $stmt_delete_order->bind_param('s', $order_id);
    $stmt_delete_order->execute();

    if ($stmt_delete_order) {
        $success = "Order cancelled and reservation updated.";
        header("refresh:1; url=payments.php");
    } else {
        $err = "Try Again Later";
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
                            <a href="orders.php" class="btn btn-outline-success">
                                <i class="fas fa-plus"></i> <i class="fas fa-utensils"></i>
                                Make A New Order
                            </a>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-items-center table-flush">
                                <thead class="thead-light">
                                    <tr>
                                        <th scope="col">Code</th>
                                        <th scope="col">Customer</th>
                                        <th scope="col">Product</th>
                                        <th scope="col">Total Price</th>
                                        <th scope="col">Date</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $ret = "SELECT * FROM rpos_orders WHERE order_status ='' ORDER BY rpos_orders.created_at DESC";
                                    $stmt = $mysqli->prepare($ret);
                                    $stmt->execute();
                                    $res = $stmt->get_result();
                                    while ($order = $res->fetch_object()) {
                                        if ($order->prod_qty > 0) {
                                            $total = ($order->prod_price * $order->prod_qty);
                                        }
                                    ?>
                                        <tr>
                                            <th class="text-success" scope="row"><?php echo $order->order_code; ?></th>
                                            <td><?php echo $order->customer_name; ?></td>
                                            <td><?php echo $order->prod_name; ?></td>
                                            <td>$ <?php echo $total; ?></td>
                                            <td><?php echo date('d/M/Y g:i', strtotime($order->created_at)); ?></td>
                                            <td>
                                                <a href="pay_order.php?order_code=<?php echo $order->order_code; ?>&customer_id=<?php echo $order->customer_id; ?>&order_status=Paid">
                                                    <button class="btn btn-sm btn-success">
                                                        <i class="fas fa-handshake"></i>
                                                        Pay Order
                                                    </button>
                                                </a>

                                                <a href="payments.php?cancel=<?php echo $order->order_id; ?>">
                                                    <button class="btn btn-sm btn-danger">
                                                        <i class="fas fa-window-close"></i>
                                                        Cancel Order
                                                    </button>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
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
