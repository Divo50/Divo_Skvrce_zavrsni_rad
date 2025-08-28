<?php
session_start();
require_once 'baza.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isset($_SESSION['user_id'])) {
    $task_id = intval($_POST['id']);
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $task_id, $user_id);

    if ($stmt->execute()) {
        header("Location: main.php");
        exit();
    } else {
        echo "Greška pri brisanju.";
    }
} else {
    echo "Nevažeći zahtjev.";
}
