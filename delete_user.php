<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];

    if ($user_id == $_SESSION['user_id']) {
        die("Ne možete obrisati sami sebe.");
    }

    $host = "localhost";
    $dbname = "user_management";
    $username = "root";
    $password = "";

    $conn = new mysqli($host, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Greška pri povezivanju s bazom: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Greška pri brisanju korisnika.";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Neispravan zahtjev.";
}
