<?php
require_once 'baza.php';

$sutra = date('Y-m-d', strtotime('+1 day'));

$sql = "
    SELECT t.*, u.email, u.name 
    FROM tasks t
    JOIN users u ON t.user_id = u.id
    WHERE DATE(t.end_date) = ? AND t.completed = 0
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $sutra);
$stmt->execute();
$result = $stmt->get_result();

$apiKey = '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';

while ($row = $result->fetch_assoc()) {
    $email = $row['email'];
    $ime = $row['name'];
    $naslov = $row['title'];
    $opis = $row['description'];

    $data = [
        'sender' => [
            'name' => 'Aplikacija za upravljanje zadatcima',
            'email' => 'skvrce44@gmail.com'
        ],
        'to' => [[
            'email' => $email,
            'name' => $ime
        ]],
        'subject' => "Podsjetnik: '$naslov' ističe sutra",
        'htmlContent' => "
            <p>Bok $ime,</p>
            <p>Podsjećamo Vas da zadatak <strong>$naslov</strong> ističe sutra.</p>
            <p><em>$opis</em></p>
            <p>Lijep pozdrav,<br>Aplikacija za upravljanje zadatcima</p>
        "
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.brevo.com/v3/smtp/email');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "api-key: $apiKey",
        "Content-Type: application/json",
        "Accept: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $odgovor = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 201) {
        echo "E-mail poslan za zadatak: $naslov korisniku $email<br>";
    } else {
        echo "Greška za $email – API odgovor: $odgovor<br>";
    }
}

$stmt->close();
$conn->close();
?>
