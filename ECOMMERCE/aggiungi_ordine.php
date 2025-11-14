<?php
require 'db.php';

// Mostra errori utili in dev (puoi disattivarli in produzione)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$errore = null;

// Valida ID contatto
$contatto_id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null;
if (!$contatto_id) {
    die("ID contatto non valido.");
}

// Recupera nome contatto (senza get_result, compatibile ovunque)
$nome_contatto = "Contatto sconosciuto";
$contatto_stmt = mysqli_prepare($conn, "SELECT nome FROM contatti WHERE id = ?");
mysqli_stmt_bind_param($contatto_stmt, "i", $contatto_id);
mysqli_stmt_execute($contatto_stmt);
mysqli_stmt_bind_result($contatto_stmt, $nome_tmp);
if (mysqli_stmt_fetch($contatto_stmt)) {
    $nome_contatto = $nome_tmp;
}
mysqli_stmt_close($contatto_stmt);

// Se POST: inserisci ordine
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $prodotto = isset($_POST['prodotto']) ? trim($_POST['prodotto']) : '';
    $quantita = isset($_POST['quantita']) ? (int)$_POST['quantita'] : 0;
    $data = isset($_POST['data']) ? trim($_POST['data']) : '';

    // Valida input
    $data_valida = preg_match('/^\d{4}-\d{2}-\d{2}$/', $data); // type="date" -> YYYY-MM-DD

    if ($prodotto !== '' && $quantita > 0 && $data_valida) {
        $stmt = mysqli_prepare($conn, "INSERT INTO ordini (contatto_id, prodotto, quantita, data_di_ordine) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "isis", $contatto_id, $prodotto, $quantita, $data);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // Importante: nessun echo prima del redirect
        header("Location: ordini.php?id=$contatto_id&success=aggiunto");
        exit;
    } else {
        $errore = "Compila tutti i campi correttamente. La quantità deve essere > 0 e la data valida (YYYY-MM-DD).";
    }
}
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Aggiungi ordine di <?= htmlspecialchars($nome_contatto) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
</head>

<body>
    <div class="container mt-5">
        <h2>Aggiungi ordine di <?= htmlspecialchars($nome_contatto) ?></h2>

        <?php if ($errore): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($errore) ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <div class="mb-3">
                <label class="form-label">Prodotto</label>
                <input class="form-control" name="prodotto" type="text" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Quantità</label>
                <input class="form-control" name="quantita" type="number" min="1" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Data</label>
                <input class="form-control" name="data" type="date" required>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="ordini.php?id=<?= $contatto_id ?>" class="btn btn-primary">Torna agli ordini di <?= htmlspecialchars($nome_contatto) ?></a>
                <button type="submit" class="btn btn-secondary">Aggiungi ordine</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>