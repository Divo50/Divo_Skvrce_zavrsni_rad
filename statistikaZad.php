<?php
session_start();
require_once 'baza.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id'], $_POST['completed']) && isset($_SESSION['user_id'])) {
    $id = ($_POST['id']);
    $completed = ($_POST['completed']);
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("UPDATE tasks SET completed = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("iii", $completed, $id, $user_id);
    $stmt->execute();
    $stmt->close();
}