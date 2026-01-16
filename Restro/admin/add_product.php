<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
include('config/code-generator.php');

check_login();

if (isset($_POST['addProduct'])) {

  // Blank check
  if (
    empty($_POST["prod_code"]) ||
    empty($_POST["prod_name"]) ||
    empty($_POST['prod_desc']) ||
    empty($_POST['prod_price'])
  ) {
    $err = "Blank Values Not Accepted";
  }

  // Product name: alphabets only
  else if (!preg_match("/^[a-zA-Z ]+$/", $_POST['prod_name'])) {
    $err = "Product name can only contain alphabets";
  }

  // Product price: must be greater than 0
  else if (!is_numeric($_POST['prod_price']) || $_POST['prod_price'] <= 0) {
    $err = "Product price must be greater than zero";
  }

  // All validations passed
  else {

    $prod_id    = $_POST['prod_id'];
    $prod_code  = $_POST['prod_code'];
    $prod_name  = $_POST['prod_name'];
    $prod_img   = $_FILES['prod_img']['name'];
    $prod_desc  = $_POST['prod_desc'];
    $prod_price = $_POST['prod_price'];

    move_uploaded_file(
      $_FILES["prod_img"]["tmp_name"],
      "assets/img/products/" . $prod_img
    );

    // Insert product
    $postQuery = "
      INSERT INTO rpos_products
      (prod_id, prod_code, prod_name, prod_img, prod_desc, prod_price)
      VALUES (?,?,?,?,?,?)
    ";

    $postStmt = $mysqli->prepare($postQuery);
    $postStmt->bind_param(
      'ssssss',
      $prod_id,
      $prod_code,
      $prod_name,
      $prod_img,
      $prod_desc,
      $prod_price
    );

    if ($postStmt->execute()) {
      $success = "Product Added";
      header("refresh:1; url=add_product.php");
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
    ?>
    <!-- Header -->
    <div style="background-image: url(assets/img/theme/restro00.jpg); background-size: cover;" class="header  pb-8 pt-5 pt-md-8">
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
                    <label>Product Name</label>
                    <input type="text" name="prod_name" class="form-control">
                    <input type="hidden" name="prod_id" value="<?php echo $prod_id; ?>" class="form-control">
                  </div>
                  <div class="col-md-6">
                    <label>Product Code</label>
                    <input type="text" name="prod_code" value="<?php echo $alpha; ?>-<?php echo $beta; ?>" class="form-control" value="">
                  </div>
                </div>
                <hr>
                <div class="form-row">
                  <div class="col-md-6">
                    <label>Product Image</label>
                    <input type="file" name="prod_img" class="btn btn-outline-success form-control" value="">
                  </div>
                  <div class="col-md-6">
                    <label>Product Price</label>
                    <input type="text" name="prod_price" class="form-control" value="">
                  </div>
                </div>
                <hr>
                <div class="form-row">
                  <div class="col-md-12">
                    <label>Product Description</label>
                    <textarea rows="5" name="prod_desc" class="form-control" value=""></textarea>
                  </div>
                </div>
                <br>
                <div class="form-row">
                  <div class="col-md-6">
                    <input type="submit" name="addProduct" value="Add Product" class="btn btn-success" value="">
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
  <!-- Argon Scripts -->
  <?php
  require_once('partials/_scripts.php');
  ?>
</body>
</html>