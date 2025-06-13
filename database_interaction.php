<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

try {
    // Spajanje na SQLite bazu (kreira datoteku ako ne postoji)
    $db = new PDO('sqlite:my_database.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Kreiraj tablicu ako ne postoji
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT,
        email TEXT
    )");

    // Ako je forma poslana, dodaj korisnika
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["name"], $_POST["email"])) {
        // Osnovna sanitizacija
        $name = trim($_POST["name"]);
        $email = trim($_POST["email"]);

        // Dodaj korisnika u bazu
        $stmt = $db->prepare("INSERT INTO users (name, email) VALUES (?, ?)");
        $stmt->execute([$name, $email]);

        $_SESSION["message"] = "Korisnik dodan uspješno!";

        // Redirect da spriječi ponavljanje unosa i očisti POST podatke
        header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
        exit;
    }

    // Dohvati sve korisnike iz baze
    $users = $db->query("SELECT * FROM users")->fetchAll(PDO::FETCH_ASSOC);

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

    <h2>Popis korisnika</h2>
    <ul>
        <?php foreach ($users as $user): ?>
            <li><?php echo htmlspecialchars($user["name"]) . " (" . htmlspecialchars($user["email"]) . ")"; ?></li>
        <?php endforeach; ?>
    </ul>
</body>
</html>
