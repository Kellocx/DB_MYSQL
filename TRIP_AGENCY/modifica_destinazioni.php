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
$sql = "SELECT citta, paese, prezzo, data_partenza, data_ritorno, posti_disponibili 
        FROM destinazioni WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_destinazione);
$stmt->execute();
$stmt->bind_result($citta, $paese, $prezzo, $data_partenza, $data_ritorno, $posti_disponibili);
if (!$stmt->fetch()) {
    $stmt->close();
    header("Location: destinazioni.php?error=notfound");
    exit;
}
$stmt->close();

// Se arriva POST: aggiorna
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $citta             = trim($_POST['citta'] ?? '');
    $paese             = trim($_POST['paese'] ?? '');
    $prezzo            = (float)($_POST['prezzo'] ?? 0);
    $data_partenza     = $_POST['data_partenza'] ?? '';
    $data_ritorno      = $_POST['data_ritorno'] ?? '';
    $posti_disponibili = (int)($_POST['posti_disponibili'] ?? 0);

    $sql = "UPDATE destinazioni 
            SET citta=?, paese=?, prezzo=?, data_partenza=?, data_ritorno=?, posti_disponibili=? 
            WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdssii", $citta, $paese, $prezzo, $data_partenza, $data_ritorno, $posti_disponibili, $id_destinazione);

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
        <div class="mb-3">
            <label class="form-label">Posti Disponibili</label>
            <input type="number" name="posti_disponibili" class="form-control" min="1" value="<?= htmlspecialchars($posti_disponibili) ?>" required>
        </div>
        <button type="submit" class="btn btn-warning">Aggiorna</button>
        <a href="destinazioni.php" class="btn btn-secondary">Annulla</a>
    </form>
</div>

<!-- Modale Bootstrap -->
<?php if (!empty($messaggio)): ?>
    <div class="modal fade" id="messaggioModal" tabindex="-1" aria-labelledby="messaggioModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content bg-white">
                <div class="modal-header">
                    <h5 class="modal-title" id="messaggioModalLabel">Esito operazione</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-<?= $tipoMessaggio ?>">
                    <?= htmlspecialchars($messaggio) ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<?php if (!empty($messaggio)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modalEl = document.getElementById('messaggioModal');
            if (modalEl) {
                const modal = new bootstrap.Modal(modalEl);
                modal.show();
                // Dopo chiusura modale → redirect per pulire la query string
                modalEl.addEventListener('hidden.bs.modal', () => {
                    window.location.href = 'destinazioni.php';
                });
            }
        });
    </script>
<?php endif; ?>

<?php include 'footer.php'; ?>