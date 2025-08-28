<?php

$host = "localhost";
$dbname = "user_management";
$username = "root";
$password = "";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Greška pri povezivanju s bazom: " . $conn->connect_error);
}

$name = $_POST['name'];
$email = $_POST['email'];
$password = $_POST['password'];

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $name, $email, $hashed_password);

if ($stmt->execute()) {
    session_start();
    $_SESSION['user_id'] = $stmt->insert_id; 
    $_SESSION['user_name'] = $name;
    $_SESSION['user_role'] = 'user'; 
    header("Location: ../main.php");
    exit();
} else {
    echo "Greška: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
