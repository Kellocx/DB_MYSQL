<?php
require 'db.php';

// Prendo l'ID del contatto
$contatto_id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null;
if (!$contatto_id) {
    die("ID contatto non valido.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recupero i dati dal form
    $prodotto = $_POST['prodotto'];
    $quantita = $_POST['quantita'];
    $data = $_POST['data']; // formato: YYYY-MM-DDTHH:MM

    // Query sicura
    $stmt = mysqli_prepare($conn, "INSERT INTO ordini (contatto_id, prodotto, quantita, data_di_ordine) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "isis", $contatto_id, $prodotto, $quantita, $data);
    mysqli_stmt_execute($stmt);

    // Reindirizzo
    header("Location: ordini.php?id=$contatto_id&success=aggiunto");
    exit;
}
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Aggiungi ordine</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
</head>

<body>
    <div class="container mt-5">
        <h2>Aggiungi ordine</h2>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Prodotto</label>
                <input class="form-control" name="prodotto" type="text" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Quantità</label>
                <input class="form-control" name="quantita" type="number" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Data</label>
                <input class="form-control" name="data" type="date" required>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="ordini.php?id=<?= $contatto_id ?>" class="btn btn-primary">Torna agli ordini</a>
                <button type="submit" class="btn btn-secondary">Aggiungi Ordine</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>