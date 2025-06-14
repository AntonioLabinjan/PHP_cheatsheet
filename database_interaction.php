<?php
/*
Prikazuje sve vrste PHP grešaka (`E_ALL`) i uključuje njihov prikaz u pregledniku pomoću `ini_set('display_errors', 1)`.
Idealno za debugging tijekom razvoja.
*/
error_reporting(E_ALL);
ini_set('display_errors', 1);

/*
Pokreće sesiju za pohranu podataka između zahtjeva.
Zatim se spaja na SQLite bazu i postavlja da baca iznimke pri greškama; ako tablica `users` ne postoji, kreira je s poljima `id`, `name` i `email`.
*/
session_start();

/*
Spaja se na SQLite bazu (my_database.sqlite) i postavlja da PDO baca greške kao iznimke.
Zatim SQL-om kreira tablicu users ako već ne postoji, s automatskim ID-em i poljima name i email.
*/
try {
    $db = new PDO('sqlite:my_database.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Kreiraj tablicu ako ne postoji
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT,
        email TEXT
    )");

    /*
    Ako je zahtjev `POST` i postoje `name` i `email`, podaci se očiste (`trim`) i ubacuju u bazu pomoću pripremljenog upita (`prepare + execute`).
    Zatim se sprema poruka u sesiju i radi redirect da se izbjegne ponovno slanje forme.

    */

    // Dodavanje korisnika
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["name"], $_POST["email"])) {
        $name = trim($_POST["name"]);
        $email = trim($_POST["email"]);

        $stmt = $db->prepare("INSERT INTO users (name, email) VALUES (?, ?)");
        $stmt->execute([$name, $email]);

        $_SESSION["message"] = "Korisnik dodan uspješno!";
        header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
        exit;
    }

    /*
    Ako je postavljen `GET` parametar `delete`, uzima se ID korisnika, briše se iz baze pomoću `DELETE` upita,
     postavlja se poruka, i radi redirect kako bi se osvježila stranica bez `GET` parametra.

    */
    // Brisanje korisnika
    if (isset($_GET['delete'])) {
        $deleteId = (int) $_GET['delete'];
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$deleteId]);
        $_SESSION["message"] = "Korisnik obrisan!";
        header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
        exit;
    }

    /*
    Ako je `GET` parametar `export=csv`, postavljaju se CSV zaglavlja, otvara se izlaz u preglednik (`php://output`), 
    upisuju se zaglavlja i svi korisnici iz baze red po red u CSV formatu pomoću `fputcsv()`, zatvara se izlaz i prekida izvršavanje.
    */
    // Export u CSV
    if (isset($_GET['export']) && $_GET['export'] === 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename=korisnici.csv');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Ime', 'Email'], ',', '"', "\\");
        foreach ($db->query("SELECT * FROM users") as $row) {
            fputcsv($output, $row, ',', '"', "\\");
        }
        fclose($output);
        exit;
    }
    /*
    Dohvaća pojam za pretragu i smjer sortiranja iz URL-a; ako postoji pretraga,
    traži korisnike čije ime sadrži taj pojam i sortira ih po imenu uz zadani redoslijed, inače dohvaća sve korisnike sortirane po imenu.
    */
    // Pretraga i sortiranje
    $searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
    $order = (isset($_GET['order']) && $_GET['order'] === 'desc') ? 'DESC' : 'ASC';

    if ($searchTerm !== '') {
        $stmt = $db->prepare("SELECT * FROM users WHERE name LIKE ? ORDER BY name $order");
        $stmt->execute(['%' . $searchTerm . '%']);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $users = $db->query("SELECT * FROM users ORDER BY name $order")->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    die("Greška kod baze podataka: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <title>PHP Demo App</title>
</head>
<body>
    <h1>PHP Demo Aplikacija</h1>

<!-- 
Ako postoji poruka u sesiji, ispiše je u zeleni paragraf i odmah briše da se ne prikazuje više puta.
    
-->

    <?php if (!empty($_SESSION["message"])): ?>
        <p style="color: green;"><?php echo htmlspecialchars($_SESSION["message"]); unset($_SESSION["message"]); ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <label>Ime:</label>
        <input type="text" name="name" required>
        <label>Email:</label>
        <input type="email" name="email" required>
        <button type="submit">Dodaj korisnika</button>
    </form>

    <hr>

    <!--
    Forma šalje `GET` zahtjev s pojmom za pretragu; prikazuje zadnji upisani pojam, a link "Poništi" resetira pretragu vraćanjem na početnu stranicu.

    -->
    <form method="GET" action="">
        <input type="text" name="search" placeholder="Pretraži po imenu" value="<?php echo htmlspecialchars($searchTerm ?? ''); ?>">
        <button type="submit">Pretraži</button>
        <a href="?">Poništi</a>
    </form>

    <!-- 
    Prikazuje linkove za sortiranje korisnika po imenu; `http_build_query` zadržava postojeće parametre (npr. `search`) i dodaje `order=asc` ili `order=desc`.
    -->
    <p>Sortiraj po imenu:
        <a href="?<?php echo http_build_query(array_merge($_GET, ['order' => 'asc'])); ?>">A-Z</a> |
        <a href="?<?php echo http_build_query(array_merge($_GET, ['order' => 'desc'])); ?>">Z-A</a>
    </p>
    <!-- Link koji korisniku omogućuje da preuzme listu korisnika u CSV formatu slanjem GET zahtjeva s `export=csv` parametrom.
    -->
    <p><a href="?export=csv">📄 Exportiraj korisnike (CSV)</a></p>

    <!--Ispisuje popis korisnika s imenom i emailom, uz link za brisanje koji traži potvrdu, te ispod prikazuje ukupni broj korisnika.
 -->
    <h2>Popis korisnika</h2>
    <ul>
        <?php foreach ($users as $user): ?>
            <li>
                <?php echo htmlspecialchars($user["name"]) . " (" . htmlspecialchars($user["email"]) . ")"; ?>
                <a href="?delete=<?php echo $user['id']; ?>" onclick="return confirm('Jesi siguran da želiš obrisati ovog korisnika?')">[Obriši]</a>
            </li>
        <?php endforeach; ?>
    </ul>

    <p>Ukupno korisnika: <?php echo count($users); ?></p>
</body>
</html>
