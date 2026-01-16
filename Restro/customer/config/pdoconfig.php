<?php
$DB_host = "localhost";
$DB_port = "3307";         
$DB_user = "root";
$DB_pass = "";
$DB_name = "rposystem";

try {
    $DB_con = new PDO("mysql:host={$DB_host};port={$DB_port};dbname={$DB_name}", $DB_user, $DB_pass);
    $DB_con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}
?>
