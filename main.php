<?php
session_start(); 
require_once 'baza.php';

$prijavljen = isset($_SESSION['user_name']);

// spremanje novog zadatka
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['title']) && $prijavljen) {
    $naslov = trim($_POST['title']);
    $opis_zadatka = trim($_POST['task_description']);
    $datum_pocetka = $_POST['start_date'];
    $datum_zavrsetka = $_POST['end_date'];
    $korisnik_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO tasks (user_id, title, description, start_date, end_date) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $korisnik_id, $naslov, $opis_zadatka, $datum_pocetka, $datum_zavrsetka);
    $stmt->execute();
    $stmt->close();

    header("Location: main.php");
    exit;
}

$zadaci = [];
if ($prijavljen) {
    $korisnik_id = $_SESSION['user_id'];
    $rezultat = $conn->prepare("SELECT * FROM tasks WHERE user_id = ? ORDER BY start_date DESC");
    $rezultat->bind_param("i", $korisnik_id);
    $rezultat->execute();
    $zadaci = $rezultat->get_result();
}

$statistika = [
    'ukupno' => 0,
    'odradjeno' => 0,
    'neodradjeno' => 0,
    'postotak' => 0
];

if ($prijavljen) {
    $stmt = $conn->prepare("SELECT COUNT(*) AS ukupno, 
                                   SUM(completed = 1) AS odradjeno, 
                                   SUM(completed = 0) AS neodradjeno 
                            FROM tasks WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($ukupno, $odradjeno, $neodradjeno);
    $stmt->fetch();
    $stmt->close();

    $postotak = ($ukupno > 0) ? round(($odradjeno / $ukupno) * 100, 2) : 0;

    $statistika = [
        'ukupno' => $ukupno,
        'odradjeno' => $odradjeno,
        'neodradjeno' => $neodradjeno,
        'postotak' => $postotak
    ];
}

if (isset($_GET['get_tasks']) && $prijavljen) {
    header('Content-Type: application/json');
    $korisnik_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT id, title, start_date, end_date FROM tasks WHERE user_id = ?");
    $stmt->bind_param("i", $korisnik_id);
    $stmt->execute();
    $rezultat = $stmt->get_result();

    $dogadaji = [];
    while ($red = $rezultat->fetch_assoc()) {
        $dogadaji[] = [
            'id' => $red['id'],
            'title' => $red['title'],
            'start' => $red['start_date'],
            'end' => date('Y-m-d', strtotime($red['end_date'] . ' +1 day')) 
        ];
    }

    echo json_encode($dogadaji);
    exit;
}
?>
<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <title>Glavna stranica</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="main.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
</head>
<body>

<?php if ($prijavljen): ?>
    
    <header class="pt-5 pb-5 bg-danger">
        <div class="container pt-2">
            <p>Dobrodošao, <?= htmlspecialchars($_SESSION['user_name']) ?>!</p>
            <div class="row gore align-items-center py-1">
                <div class="col-12 col-lg-9">
                    <nav>
                        <ul class="d-flex flex-column flex-lg-row align-items-center gap-3">
                            <li>
                                <img src="img/logo.png" alt="Logo" style="max-height: 40px;">
                            </li>
                            <li><a href="#">Usluge</a></li>
                            <li><a href="#">Novosti</a></li>
                            <li><a href="logout.php">Odjavi se</a></li>
                        </ul>
                    </nav>
                </div>

                <div class="col-12 col-lg-3 text-lg-end text-center">
                    <div class="d-flex justify-content-center justify-content-lg-end gap-3">
                        <i class="fab fa-facebook-square fa-2x"></i>
                        <i class="fab fa-twitter-square fa-2x"></i>
                        <i class="fab fa-linkedin fa-2x"></i>
                    </div>
                </div>
            </div>    
        </div>

        <div class="container pt-5 pb-5">
            <div class="row">
                <div class="col-lg-6">
                    <h2>Dobro došli na sučelje za dodavanje zadataka.</h2>
                    <h2 id="naslov" class="mt-4">Kako koristiti aplikaciju</h2>
                    <ul>
                        <li>Unesite naziv, opis, datum početka i završetka zadatka.</li>
                        <li>Kliknite na "Spremi zadatak" da biste unijeli zadatak.</li>
                        <li>U listi "Moji zadatci", možete urediti ili obrisati svaki zadatak.</li>
                        <li>Koristan savjet: Postavite stvarne datume i opišite zadatak jasno.</li>
                    </ul>   
                </div>
                <div class="col-lg-6">
                    <lottie-player 
                        src="https://assets5.lottiefiles.com/packages/lf20_1pxqjqps.json"
                        background="transparent" speed="1" loop autoplay
                        style="width: 100%; max-width: 500px; height: 350px;">
                    </lottie-player>
                </div>
            </div>
        </div>
    </header>
    
    <section class="container pb-5">

        <form method="POST" class="mb-4 mt-4 p-4 border border-danger rounded shadow-sm" style="background-color: #fff5f5;">
            <h4 class="mb-4 text-danger">Unos novog zadatka</h4>
    
            <div class="mb-3">
                <label for="title" class="form-label text-danger">Naziv zadatka</label>
                <input type="text" class="form-control border-danger" id="title" name="title" required>
            </div>

            <div class="mb-3">
                <label for="task_description" class="form-label text-danger">Opis zadatka</label>
                <textarea class="form-control border-danger" id="task_description" name="task_description" rows="3" required></textarea>
            </div>

            <div class="mb-3">
                <label for="start_date" class="form-label text-danger">Datum početka</label>
                <input type="date" class="form-control border-danger" id="start_date" name="start_date" required>
            </div>

            <div class="mb-3">
                <label for="end_date" class="form-label text-danger">Datum završetka</label>
                <input type="date" class="form-control border-danger" id="end_date" name="end_date" required>
            </div>

            <button type="submit" class="btn btn-danger w-100">Spremi zadatak</button>
        </form>

        <h4>Moji zadatci</h4>
        <?php if ($zadaci && $zadaci->num_rows > 0): ?>
        <ul class="list-group">
            <?php while ($red = $zadaci->fetch_assoc()): ?>
                <li class="list-group-item d-flex justify-content-between flex-column flex-md-row align-items-start align-items-md-center">
                    <div class="d-flex">
                        <input type="checkbox" class="form-check-input me-2" data-id="<?= $red['id'] ?>"
                        onchange="zadatakRijesen(this)" <?= $red['completed'] ? 'checked' : '' ?>> 
                        <div class="task-text <?= $red['completed'] ? 'task-completed' : '' ?>">
                            <strong><?= htmlspecialchars($red['title']) ?></strong><br>
                            <?= htmlspecialchars($red['description']) ?><br>
                            <small>Od: <?= $red['start_date'] ?> do <?= $red['end_date'] ?></small>
                        </div>
                    </div>
                    <div class="mt-2 mt-md-0">
                        <a href="uredi_zad.php?id=<?= $red['id'] ?>" class="btn btn-sm btn-primary me-2">Uredi</a>
                        <form method="POST" action="obrisi_zad.php" style="display:inline;" onsubmit="return confirm('Jeste li sigurni da želite obrisati ovaj zadatak?');">
                            <input type="hidden" name="id" value="<?= $red['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Obriši</button>
                        </form>
                    </div>
                </li>
            <?php endwhile; ?>
        </ul>
        <hr class="crta">
        <h3>Kalendar zadataka</h3>
        <hr class="crta">
        <div id='calendar'></div>
   
    <?php else: ?>
        <p>Trenutno nema dodanih zadataka.</p>
    <?php endif; ?>

        <h3>Vaša statistika</h3>
        <hr class="crta">
        <div class="row tekst pb-5">
            <div class="col-md-3">
                <h5>Ukupno zadataka</h5>
                <p><?= $statistika['ukupno'] ?></p>
            </div>
            <div class="col-md-3">
                <h5>Odrađenih</h5>
                <p><?= $statistika['odradjeno'] ?></p>
            </div>
            <div class="col-md-3">
                <h5>Neodrađenih</h5>
                <p><?= $statistika['neodradjeno'] ?></p>
            </div>
            <div class="col-md-3">
                <h5>Uspješnost</h5>
                <p><?= $statistika['postotak'] ?>%</p>
            </div>
        </div>
    </section>
        <article class="container-fluid text-center pt-4 pb-4 bg-danger">
            <p>Završni rad <br> 2025. <br> Đivo Skvrce</p>
        </article>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const calendarEl = document.getElementById('calendar');

        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'hr',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: ''
            },
            events: 'main.php?get_tasks=1',
            eventColor: '#0d6efd',
            height: 'auto'
        });

        calendar.render();
    });

    function zadatakRijesen(checkbox) {
        const zadatak = checkbox.closest('.d-flex').querySelector('.task-text');
        const zadatakId = checkbox.dataset.id;

        if (checkbox.checked) {
            zadatak.classList.add('task-completed');
        } else {
            zadatak.classList.remove('task-completed');
        }

        fetch('statistikaZad.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `id=${zadatakId}&completed=${checkbox.checked ? 1 : 0}`
        }).then(() => {
            location.reload();
        });
    }
    </script>

<?php else: ?>

      <header class="pt-5 pb-5 bg-danger">
        <div class="container pt-5">
            <div class="row gore align-items-center py-1">
                <div class="col-12 col-lg-9">
                    <nav>
                        <ul class="d-flex flex-column flex-lg-row align-items-center gap-3">
                            <li>
                                <img src="img/logo.png" alt="Logo" style="max-height: 40px;">
                            </li>
                            <li><a href="#">Značajke</a></li>
                            <li><a href="#">Novosti</a></li>
                            <li><a href="prijava_registracija/login.html">Prijavite se</a></li>
                            <li><a href="prijava_registracija/register.html">Registrirajte se</a></li>
                        </ul>
                    </nav>
                </div>

                <div class="col-12 col-lg-3 text-lg-end text-center">
                    <div class="d-flex justify-content-center justify-content-lg-end gap-3">
                        <i class="fab fa-facebook-square fa-2x"></i>
                        <i class="fab fa-twitter-square fa-2x"></i>
                        <i class="fab fa-linkedin fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="container pt-5 pb-5">
            <div class="row">
                <div class="col-lg-6">
                    <h2>Dobro došli u aplikaciju za upravljanje zadatcima.</h2>
                    <p>Nakon registracije i prijave, aplikacija nudi korisnicima mogućnost dodavanja zadataka, njihovu izmjenu, brisanje,
                        statistiku i druge stvari.
                    </p>
                    <a href="#" id="link">O NAMA</a>
                </div>
                <div class="col-lg-6">
                    <img src="img/img4.png" alt="">
                </div>
            </div>
        </div>
    </header>

    <section class="container pt-4">
    <h3>Što nudi naša aplikacija</h3>
    <hr class="crta">
    <div class="row tekst pb-5">
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow h-100">
                <img src="img/taskman1.webp" alt="" class="card-img-top">
                <div class="card-body">
                    <h5 class="card-title">Dodavanje i upravljanje zadatcima</h5>
                    <p class="card-text">Nakon što se prijavite, imate mogućnost dodavanja više zadataka, njihovu izmjenu (naziv, opis, datum) te brisanje istog.</p>
                    <a href="#" class="text-danger">Pročitaj više</a>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow h-100">
                <img src="img/taskman2.avif" alt="" class="card-img-top">
                <div class="card-body">
                    <h5 class="card-title">Kalendar zadataka</h5>
                    <p class="card-text">Na kalendaru zadataka prikazani su svi zadatci, od njihova dana početka do zadnjeg dana.</p>
                    <a href="#" class="text-danger">Pročitaj više</a>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow h-100">
                <img src="img/taskman3.avif" alt="" class="card-img-top">
                <div class="card-body">
                    <h5 class="card-title">Upozorenje za istek zadatka</h5>
                    <p class="card-text">Aplikacija ima mogućnost slanja automatskog mail-a korisnicima čiji zadatci ističu za 1 dan.</p>
                    <a href="#" class="text-danger">Pročitaj više</a>
                </div>
            </div>
        </div>
    </div>

        <h3>O nama</h3>
        <hr class="crta">
        <div class="row">
            <div class="col-lg-5">
                <iframe width="540" height="315" src="https://www.youtube.com/embed/4ysyybi4068?si=gcBWxcYS1_vv8UIX" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
            </div>
            <div class="col-lg-3">
                <p>Aplikacija je napravljena u svrhu završnog rada za akademsku
                    godinu 2024./2025. 
                </p>
                <p>Tehnologije i programski jezici korišteni u izradi aplikacije su: Html, 
                    Css, PHP, MySql, Bootstrap i JavaScript. </p>
            </div>
            <div class="col-lg-4">
                <h4>Kontakt informacije</h4>
                <p>Adresa: <br> Kneza Domagoja 14, 20000 Dubrovnik, Hrvatska</p>
                <p>Telefon: <br> 099 123 1111</p>
                <p>Email: <br> podrska@taskapp.com</p>
            </div>
        </div>
    </section>
    
    <article class="container-fluid text-center pt-4 pb-4 bg-danger">
        <p>Završni rad <br> 2025. <br> Đivo Skvrce</p>
    </article>
<?php endif; ?>
</body>
</html>
