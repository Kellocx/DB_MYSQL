<?php
require 'db.php';
mysqli_set_charset($conn, 'utf8mb4');

$messaggio = '';
$tipoMessaggio = '';

$id_destinazione = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null;
if (!$id_destinazione) {
    header("Location: destinazioni.php?error=invalid");
    exit;
}

// Recupera dati destinazione
$sql = "SELECT citta, paese, prezzo, data_partenza, data_ritorno FROM destinazioni WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_destinazione);
$stmt->execute();
$stmt->bind_result($citta, $paese, $prezzo, $data_partenza, $data_ritorno);
if (!$stmt->fetch()) {
    $stmt->close();
    header("Location: destinazioni.php?error=notfound");
    exit;
}
$stmt->close();

// Se arriva POST: aggiorna
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $citta         = trim($_POST['citta'] ?? '');
    $paese         = trim($_POST['paese'] ?? '');
    $prezzo        = (float)($_POST['prezzo'] ?? 0);
    $data_partenza = $_POST['data_partenza'] ?? '';
    $data_ritorno  = $_POST['data_ritorno'] ?? '';

    $sql = "UPDATE destinazioni 
            SET citta=?, paese=?, prezzo=?, data_partenza=?, data_ritorno=? 
            WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdssi", $citta, $paese, $prezzo, $data_partenza, $data_ritorno, $id_destinazione);

    if ($stmt->execute()) {
        $messaggio = "Destinazione modificata con successo!";
        $tipoMessaggio = "success";
    } else {
        $messaggio = "Errore: " . htmlspecialchars($stmt->error, ENT_QUOTES, 'UTF-8');
        $tipoMessaggio = "danger";
    }
    $stmt->close();
}

include 'header.php';
?>

<div class="container mt-5">
    <h2>Modifica Destinazione</h2>
    <form method="post">
        <div class="mb-3">
            <label class="form-label">Città</label>
            <input type="text" name="citta" class="form-control" value="<?= htmlspecialchars($citta) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Paese</label>
            <input type="text" name="paese" class="form-control" value="<?= htmlspecialchars($paese) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Prezzo (€)</label>
            <input type="number" step="0.01" name="prezzo" class="form-control" value="<?= htmlspecialchars($prezzo) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Data Partenza</label>
            <input type="date" name="data_partenza" class="form-control" value="<?= htmlspecialchars($data_partenza) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Data Ritorno</label>
            <input type="date" name="data_ritorno" class="form-control" value="<?= htmlspecialchars($data_ritorno) ?>" required>
        </div>
        <button type="submit" class="btn btn-warning">Aggiorna</button>
        <a href="destinazioni.php" class="btn btn-secondary">Annulla</a>
    </form>
</div>

<?php if (!empty($messaggio)): ?>
    <div class="alert alert-<?= $tipoMessaggio ?> mt-3"><?= htmlspecialchars($messaggio) ?></div>
<?php endif; ?>

<?php include 'footer.php'; ?>