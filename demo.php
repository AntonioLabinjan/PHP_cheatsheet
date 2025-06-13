<?php
// Osnovni primjer PHP aplikacije s formom, sesijama i SQLite bazom podataka

session_start();

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
    $stmt = $db->prepare("INSERT INTO users (name, email) VALUES (?, ?)");
    $stmt->execute([$_POST["name"], $_POST["email"]]);
    $_SESSION["message"] = "Korisnik dodan uspješno!";
    header("Location: " . $_SERVER["PHP_SELF"]); // redirect da spriječi ponavljanje unosa
    exit;
}

// Dohvati sve korisnike iz baze
$users = $db->query("SELECT * FROM users")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>PHP Demo App</title>
</head>
<body>
    <h1>PHP Demo Aplikacija</h1>

    <?php if (!empty($_SESSION["message"])): ?>
        <p style="color: green;"><?php echo $_SESSION["message"]; unset($_SESSION["message"]); ?></p>
    <?php endif; ?>

    <form method="POST">
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
