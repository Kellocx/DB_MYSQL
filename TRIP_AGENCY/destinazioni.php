<?php
require 'db.php';
mysqli_set_charset($conn, 'utf8mb4');

// Messaggio di conferma
$messaggio = '';
$tipoMessaggio = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $citta         = trim($_POST['citta'] ?? '');
    $paese         = trim($_POST['paese'] ?? '');
    $prezzo        = (float)($_POST['prezzo'] ?? 0);
    $data_partenza = $_POST['data_partenza'] ?? '';
    $data_ritorno  = $_POST['data_ritorno'] ?? '';

    if (empty($citta) || empty($paese) || $prezzo <= 0 || empty($data_partenza) || empty($data_ritorno)) {
        $messaggio = 'Tutti i campi devono essere compilati correttamente.';
        $tipoMessaggio = 'danger';
    } else {
        $sql = "INSERT INTO destinazioni (citta, paese, prezzo, data_partenza, data_ritorno) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdss", $citta, $paese, $prezzo, $data_partenza, $data_ritorno);

        if ($stmt->execute()) {
            $messaggio = 'Destinazione aggiunta con successo!';
            $tipoMessaggio = 'success';
        } else {
            $messaggio = 'Errore durante l\'inserimento: ' . htmlspecialchars($stmt->error, ENT_QUOTES, 'UTF-8');
            $tipoMessaggio = 'danger';
        }
        $stmt->close();
    }
}

// Recupera tutte le destinazioni
$destinazioni = [];
$sql_dest = "SELECT id, citta, paese, prezzo, data_partenza, data_ritorno FROM destinazioni ORDER BY citta ASC";
$res_dest = mysqli_query($conn, $sql_dest);
while ($row = mysqli_fetch_assoc($res_dest)) {
    $destinazioni[] = $row;
}
mysqli_free_result($res_dest);

include 'header.php';
?>

<div class="container mt-5">
    <h2>Aggiungi una nuova destinazione</h2>

    <!-- Messaggio di conferma -->
    <?php if ($messaggio): ?>
        <div class="alert alert-<?= $tipoMessaggio ?>"><?= htmlspecialchars($messaggio) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label for="citta" class="form-label">Città</label>
            <input type="text" class="form-control" id="citta" name="citta" required>
        </div>
        <div class="mb-3">
            <label for="paese" class="form-label">Paese</label>
            <input type="text" class="form-control" id="paese" name="paese" required>
        </div>
        <div class="mb-3">
            <label for="prezzo" class="form-label">Prezzo (€)</label>
            <input type="number" class="form-control" id="prezzo" name="prezzo" step="0.01" required>
        </div>
        <div class="mb-3">
            <label for="data_partenza" class="form-label">Data di Partenza</label>
            <input type="date" class="form-control" id="data_partenza" name="data_partenza" required>
        </div>
        <div class="mb-3">
            <label for="data_ritorno" class="form-label">Data di Ritorno</label>
            <input type="date" class="form-control" id="data_ritorno" name="data_ritorno" required>
        </div>
        <button type="submit" class="btn btn-primary">Salva</button>
        <a href="index.php" class="btn btn-secondary">Torna alla home</a>
    </form>
</div>

<div class="container mt-5">
    <h2>Elenco Destinazioni</h2>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Città</th>
                <th>Paese</th>
                <th>Prezzo (€)</th>
                <th>Data Partenza</th>
                <th>Data Ritorno</th>
                <th>Azioni</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($destinazioni as $d): ?>
                <tr>
                    <td><?= htmlspecialchars($d['id']) ?></td>
                    <td><?= htmlspecialchars($d['citta']) ?></td>
                    <td><?= htmlspecialchars($d['paese']) ?></td>
                    <td><?= htmlspecialchars($d['prezzo']) ?></td>
                    <td><?= htmlspecialchars($d['data_partenza']) ?></td>
                    <td><?= htmlspecialchars($d['data_ritorno']) ?></td>
                    <td>
                        <a href="modifica_destinazione.php?id=<?= (int)$d['id'] ?>" class="btn btn-sm btn-warning">✏️</a>
                        <a href="elimina_destinazione.php?id=<?= (int)$d['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Eliminare questa destinazione?')">🗑️</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>