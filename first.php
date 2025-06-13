<?php
$ime = $_POST['ime'] ?? null;
$prezime = $_POST['prezime'] ?? null;
$oib = $_POST['oib'] ?? null;

$oibError = null;
$isValid = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validacija OIB-a
    if (!$oib) {
        $oibError = "OIB je obavezan.";
    } elseif (!preg_match('/^\d{11}$/', $oib)) {
        $oibError = "OIB mora imati točno 11 znamenki i samo brojeve.";
    } else {
        $isValid = true;
    }
}

// --- Primjeri rada s nizovima, petljama i funkcijama ---

// Funkcija koja prima niz i vraća string s imenima odvojena zarezom
function ispisiImena(array $imena): string {
    return implode(", ", $imena);
}

// Niz imena za prikaz
$primjerImena = ["Ana", "Marko", "Ivana", "Petar"];

// Petlja za ispis brojeva od 1 do 5
$brojeviIspis = "";
for ($i = 1; $i <= 5; $i++) {
    $brojeviIspis .= $i . " ";
}
?>

<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <title>Unos podataka</title>
    <style>
        .error { color: red; }
        .code-block {
            background: #f4f4f4;
            border: 1px solid #ccc;
            padding: 10px;
            margin-top: 20px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <h2>Unesi svoje podatke</h2>

    <form method="post" action="">
        <label for="ime">Ime:</label>
        <input type="text" id="ime" name="ime" value="<?= htmlspecialchars($ime) ?>" required><br><br>

        <label for="prezime">Prezime:</label>
        <input type="text" id="prezime" name="prezime" value="<?= htmlspecialchars($prezime) ?>" required><br><br>

        <label for="oib">OIB:</label>
        <input type="text" id="oib" name="oib" value="<?= htmlspecialchars($oib) ?>" required maxlength="11" minlength="11"><br>
        <?php if ($oibError): ?>
            <span class="error"><?= $oibError ?></span><br>
        <?php endif; ?>
        <br>

        <button type="submit">Pošalji</button>
    </form>

    <?php if ($isValid && $ime && $prezime): ?>
        <h3>Bok, <?= htmlspecialchars($ime) ?> <?= htmlspecialchars($prezime) ?>!</h3>
        <p>Tvoj OIB je: <?= htmlspecialchars($oib) ?></p>
    <?php endif; ?>

    <!-- Primjeri rada s array, petljama i funkcijama -->
    <div class="code-block">
        <h3>Primjer rada s nizovima, petljama i funkcijama</h3>

        <strong>Niz imena:</strong> <?= ispisiImena($primjerImena) ?><br><br>

        <strong>Brojevi od 1 do 5 (for petlja):</strong> <?= $brojeviIspis ?>
    </div>
</body>
</html>
