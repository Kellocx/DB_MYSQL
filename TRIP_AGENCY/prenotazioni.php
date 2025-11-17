<?php
require 'db.php';
mysqli_set_charset($conn, 'utf8mb4');

// Se il form è stato inviato
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_cliente      = (int)$_POST['id_cliente'];
    $id_destinazione = (int)$_POST['id_destinazione'];
    $dataprenotazione = $_POST['dataprenotazione'];
    $acconto         = (float)$_POST['acconto'];
    $numero_persone  = (int)$_POST['numero_persone'];
    $assicurazione   = isset($_POST['assicurazione']) ? 1 : 0;

    $sql = "INSERT INTO prenotazioni (id_cliente, id_destinazione, dataprenotazione, acconto, numero_persone, assicurazione)
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iisdis", $id_cliente, $id_destinazione, $dataprenotazione, $acconto, $numero_persone, $assicurazione);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: prenotazioni.php?success=aggiunto");
    exit;
}

// Messaggio di conferma
$messaggio = '';
if (isset($_GET['success']) && $_GET['success'] === 'aggiunto') {
    $messaggio = 'Prenotazione aggiunta con successo!';
}

// Recupera lista clienti (con cognome)
$clienti = [];
$sql_clienti = "SELECT id, cognome, nome FROM clienti ORDER BY cognome ASC";
$res_clienti = mysqli_query($conn, $sql_clienti);
while ($row = mysqli_fetch_assoc($res_clienti)) {
    $clienti[] = $row;
}
mysqli_free_result($res_clienti);

// Recupera lista destinazioni
$destinazioni = [];
$sql_dest = "SELECT id, citta FROM destinazioni ORDER BY citta ASC";
$res_dest = mysqli_query($conn, $sql_dest);
while ($row = mysqli_fetch_assoc($res_dest)) {
    $destinazioni[] = $row;
}
mysqli_free_result($res_dest);
?>
<?php include 'header.php'; ?>

<body>
    <div class="container mt-5">
        <h2>Prenotazioni Clienti</h2>

        <?php if ($messaggio): ?>
            <div class="alert alert-success"><?= htmlspecialchars($messaggio) ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="mb-3">
                <label class="form-label">Cliente</label>
                <select name="id_cliente" class="form-select" required>
                    <option value="">-- Seleziona cliente --</option>
                    <?php foreach ($clienti as $c): ?>
                        <option value="<?= $c['id'] ?>">
                            <?= htmlspecialchars($c['cognome'] . " " . $c['nome'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Destinazione</label>
                <select name="id_destinazione" class="form-select" required>
                    <option value="">-- Seleziona destinazione --</option>
                    <?php foreach ($destinazioni as $d): ?>
                        <option value="<?= $d['id'] ?>">
                            <?= htmlspecialchars($d['citta'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Data Prenotazione</label>
                <input type="date" name="dataprenotazione" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Acconto (€)</label>
                <input type="number" step="0.01" name="acconto" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Numero Persone</label>
                <input type="number" name="numero_persone" class="form-control" required>
            </div>
            <div class="form-check mb-3">
                <input type="checkbox" name="assicurazione" class="form-check-input" id="assicurazione">
                <label class="form-check-label" for="assicurazione">Assicurazione inclusa</label>
            </div>
            <div class="mb-3">
                <button type="submit" class="btn btn-primary">Salva Prenotazione</button>
                <a href="index.php" class="btn btn-secondary">Annulla</a>
            </div>
        </form>
    </div>
    <?php include 'footer.php'; ?>
