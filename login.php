<?php

$host = "localhost";
$dbname = "user_management";
$username = "root";
$password = "";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Greška pri povezivanju s bazom: " . $conn->connect_error);
}

$email = $_POST['email'];
$password = $_POST['password'];

$sql = "SELECT id, name, email, password, role FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($id, $name, $email, $hashed_password, $role);
    $stmt->fetch();

     if (password_verify($password, $hashed_password)) {

        session_start();
        $_SESSION['user_id'] = $id;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_role'] = $role;

        if ($role === 'admin') {
            header("Location: ../admin/dashboard.php");
        } else {
            header("Location: ../main.php");
        }
        exit();
    } else {
        echo "Neispravna lozinka!";
    }
} else {
    echo "Korisnik ne postoji!";
}

$stmt->close();
$conn->close();
?>
