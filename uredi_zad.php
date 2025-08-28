<?php
session_start();
require_once 'baza.php';

if (!isset($_SESSION['user_id'])) {
    die("Pristup zabranjen.");
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows == 1) {
        $task = $result->fetch_assoc();
    } else {
        die("Zadatak nije pronađen.");
    }
} else {
    die("Nevažeći ID.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("UPDATE tasks SET title = ?, description = ?, start_date = ?, end_date = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ssssii", $title, $description, $start_date, $end_date, $id, $user_id);
    $stmt->execute();

    header("Location: main.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <title>Uredi zadatak</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css">
</head>
<body class="container pt-5">
    <!-- <h2>Uredi zadatak</h2>
    <form method="POST">
        <input type="hidden" name="id" value="<?= $task['id'] ?>">
        <div class="mb-3">
            <label class="form-label">Naziv zadatka</label>
            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($task['title']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Opis</label>
            <textarea name="description" class="form-control" required><?= htmlspecialchars($task['description']) ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Datum početka</label>
            <input type="date" name="start_date" class="form-control" value="<?= $task['start_date'] ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Datum završetka</label>
            <input type="date" name="end_date" class="form-control" value="<?= $task['end_date'] ?>" required>
        </div>
        <button type="submit" class="btn btn-success">Spremi promjene</button>
        <a href="main.php" class="btn btn-secondary">Natrag</a>
    </form> -->
    <h2 class="text-danger">Uredi zadatak</h2>
    <form method="POST" class="mb-4 mt-4 p-4 border border-danger rounded shadow-sm" style="background-color: #fff5f5;">
        <input type="hidden" name="id" value="<?= $task['id'] ?>">

        <div class="mb-3">
            <label class="form-label text-danger">Naziv zadatka</label>
            <input type="text" name="title" class="form-control border-danger" 
               value="<?= htmlspecialchars($task['title']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label text-danger">Opis</label>
            <textarea name="description" class="form-control border-danger" required><?= htmlspecialchars($task['description']) ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label text-danger">Datum početka</label>
            <input type="date" name="start_date" class="form-control border-danger" 
               value="<?= $task['start_date'] ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label text-danger">Datum završetka</label>
            <input type="date" name="end_date" class="form-control border-danger" 
               value="<?= $task['end_date'] ?>" required>
        </div>

        <button type="submit" class="btn btn-danger">Spremi promjene</button>
        <a href="main.php" class="btn btn-outline-danger">Natrag</a>
    </form>

</body>
</html>
