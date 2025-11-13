<?php
require 'db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$contatto_id = isset($_GET['contatto']) ? (int)$_GET['contatto'] : null;
$errore = null;

if (!$id || !$contatto_id) {
    die("ID ordine o contatto non valido.");
}

// Recupera ordine
$query = "SELECT * FROM ordini WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$ordine = mysqli_fetch_assoc($result);

if (!$ordine) {
    die("Ordine non trovato.");
}

// Salva modifiche
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prodotto = trim($_POST['prodotto']);
    $quantita = (int) $_POST['quantita'];
    $data = trim($_POST['data']);

    if ($prodotto && $quantita && $data) {
        $update = "UPDATE ordini SET prodotto = ?, quantita = ?, data = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update);
        mysqli_stmt_bind_param($stmt, "sisi", $prodotto, $quantita, $data, $id);
        mysqli_stmt_execute($stmt);
        header("Location: ordini.php?id=$contatto_id&success=modificato");
        exit();
    } else {
        $errore = "Tutti i campi sono obbligatori.";
    }
}
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Modifica Ordine</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h2>Modifica ordine</h2>

        <?php if ($errore): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($errore) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label>Prodotto</label>
                <input name="prodotto" type="text" class="form-control" value="<?= htmlspecialchars($ordine['prodotto']) ?>" required>
            </div>
            <div class="mb-3">
                <label>Quantità</label>
                <input name="quantita" type="number" class="form-control" value="<?= htmlspecialchars($ordine['quantita']) ?>" required>
            </div>
            <div class="mb-3">
                <label>Data</label>
                <input name="data" type="date" class="form-control" value="<?= htmlspecialchars($ordine['data']) ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Salva modifiche</button>
            <a href="ordini.php?id=<?= $contatto_id ?>" class="btn btn-secondary">Annulla</a>
        </form>
    </div>
</body>

</html>