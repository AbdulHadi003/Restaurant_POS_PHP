<?php
session_start();
include('config/config.php');

// Add customer
if (isset($_POST['addCustomer'])) {

    // Blank check
    if (
        empty($_POST["customer_phoneno"]) ||
        empty($_POST["customer_name"]) ||
        empty($_POST['customer_email']) ||
        empty($_POST['customer_password'])
    ) {
        $err = "Blank Values Not Accepted";
    }

    // Name validation (no numbers allowed)
    else if (preg_match('/[0-9]/', $_POST['customer_name'])) {
        $err = "Customer name cannot contain numbers";
    }

    // Phone validation (digits only, length > 10 and <= 12)
    else if (
        !ctype_digit($_POST['customer_phoneno']) ||
        strlen($_POST['customer_phoneno']) <= 10 ||
        strlen($_POST['customer_phoneno']) > 12
    ) {
        $err = "Phone number must contain only digits and length must be between 11 and 12";
    }

    // Password validation (minimum 8 characters)
    else if (strlen($_POST['customer_password']) < 8) {
        $err = "Password must be at least 8 characters long";
    }

    // All validations passed
    else {

        $customer_name     = $_POST['customer_name'];
        $customer_phoneno  = $_POST['customer_phoneno'];
        $customer_email    = $_POST['customer_email'];
        $customer_password = sha1(md5($_POST['customer_password'])); // Hash password
        $customer_id       = $_POST['customer_id'];

        // Insert into database
        $postQuery = "
            INSERT INTO rpos_customers
            (customer_id, customer_name, customer_phoneno, customer_email, customer_password)
            VALUES (?,?,?,?,?)
        ";

        $postStmt = $mysqli->prepare($postQuery);
        $postStmt->bind_param(
            'sssss',
            $customer_id,
            $customer_name,
            $customer_phoneno,
            $customer_email,
            $customer_password
        );

        if ($postStmt->execute()) {
            $success = "Customer Account Created";
            header("refresh:1; url=index.php");
        } else {
            $err = "Please Try Again Or Try Later";
        }
    }
}

require_once('partials/_head.php');
require_once('config/code-generator.php');
?>
<body class="bg-dark">
    <div class="main-content">
        <div class="header bg-gradient-primar py-7">
            <div class="container">
                <div class="header-body text-center mb-7">
                    <div class="row justify-content-center">
                        <div class="col-lg-5 col-md-6">
                            <h1 class="text-white">Restaurant Point Of Sale</h1>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Page content -->
        <div class="container mt--8 pb-5">
            <div class="row justify-content-center">
                <div class="col-lg-5 col-md-7">
                    <div class="card bg-secondary shadow border-0">
                        <div class="card-body px-lg-5 py-lg-5">
                            <form method="post" role="form">
                                <div class="form-group mb-3">
                                    <div class="input-group input-group-alternative">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        </div>
                                        <input class="form-control" required name="customer_name" placeholder="Full Name" type="text">
                                        <input class="form-control" value="<?php echo $cus_id;?>" required name="customer_id"  type="hidden">
                                    </div>
                                </div>
                                <div class="form-group mb-3">
                                    <div class="input-group input-group-alternative">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                        </div>
                                        <input class="form-control" required name="customer_phoneno" placeholder="Phone Number" type="text">
                                    </div>
                                </div>
                                <div class="form-group mb-3">
                                    <div class="input-group input-group-alternative">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="ni ni-email-83"></i></span>
                                        </div>
                                        <input class="form-control" required name="customer_email" placeholder="Email" type="email">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="input-group input-group-alternative">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="ni ni-lock-circle-open"></i></span>
                                        </div>
                                        <input class="form-control" required name="customer_password" placeholder="Password" type="password">
                                    </div>
                                </div>

                                <div class="text-center">
                                </div>
                                <div class="form-group">
                                    <div class="text-left">
                                        <button type="submit" name="addCustomer" class="btn btn-primary my-4">Create Account</button>
                                        <a href="index.php" class=" btn btn-success pull-right">Log In</a>
                                        <a href="http://localhost/rpo/Restro/customer/" class="btn btn-secondary my-4 ml-2">Back</a>
                                    </div>
                                </div>
                            </form>

                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-6">
                            <a href="../admin/forgot_pwd.php" target="_blank" class="text-light"><small>Forgot password?</small></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer -->
    <?php
    require_once('partials/_footer.php');
    ?>
    <!-- Argon Scripts -->
    <?php
    require_once('partials/_scripts.php');
    ?>
</body>

</html>