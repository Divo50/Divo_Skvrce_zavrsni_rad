<?php
session_start();

$host = "localhost";
$dbname = "user_management";
$username = "root";
$password = "";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Greška pri povezivanju s bazom: " . $conn->connect_error);
}

$search = $_GET['search'] ?? '';

if ($search) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE name LIKE ? OR email LIKE ?");
    $like = "%$search%";
    $stmt->bind_param("ss", $like, $like);
} else {
    $stmt = $conn->prepare("SELECT * FROM users");
}

$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}
?>

<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>

    <style>
    table {
        border-collapse: collapse;
        width: 100%;
        font-family: Arial, sans-serif;
        margin-top: 20px;
    }

    th, td {
        border: 1px solid #ccc;
        padding: 12px;
        text-align: left;
    }

    th {
        background-color: #f4f4f4;
        color: #333;
    }

    tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    tr:hover {
        background-color: #f1f1f1;
    }

    button {
        background-color: #e74c3c;
        color: white;
        border: none;
        padding: 6px 12px;
        cursor: pointer;
        border-radius: 4px;
    }

    button:hover {
        background-color: #c0392b;
    }

    h2 {
        font-family: Arial, sans-serif;
        color: #333;
    }

    .logout-link {
        margin-top: 20px;
        display: inline-block;
        font-size: 14px;
        text-decoration: none;
        background-color: #e74c3c;
        color: #f9f9f9;
        padding: 8px 14px;
        cursor: pointer;
        border-radius: 10%;
    }
</style>
</head>
<body>
    <h2>Admin Panel – Lista svih korisnika</h2>
    <p>Dobrodošao, <?php echo $_SESSION['user_name']; ?>!</p>


    <form method="get" style="margin-bottom: 20px;">
    <input type="text" name="search" placeholder="Pretraži po imenu ili emailu..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" style="padding: 8px; width: 300px;">
    <button type="submit" style="padding: 8px;">Pretraži</button>
    </form>

    <table border="1" cellpadding="8">
    <thead>
        <tr>
            <th>ID</th>
            <th>Ime</th>
            <th>Email</th>
            <th>Uloga</th>
            <th>Datum kreiranja</th>
            <th>Akcija</th> 
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $row): ?>
        <tr>
            <td><?= htmlspecialchars($row['id']) ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['role']) ?></td>
            <td><?= htmlspecialchars($row['created_at']) ?></td>
            <td>
                <form method="post" action="delete_user.php" onsubmit="return confirm('Jeste li sigurni da želite obrisati ovog korisnika?');">
                    <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                    <button type="submit">Obriši</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

    <br>
    <a href="../logout.php" class="logout-link">Odjavi se</a>
</body>
</html>

<?php
$conn->close();
?>
