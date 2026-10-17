<?php
$host = "127.0.0.1";
$username = "root";
$password = "root"; // သင့် MySQL Workbench ချိတ်ထားသည့် Password ကိုထည့်ပါ (Password မရှိပါက '' ဟုထားပါ)
$dbname = "student_management";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}
?>