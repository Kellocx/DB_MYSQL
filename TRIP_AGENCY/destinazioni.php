<?php
require 'db.php';
mysqli_set_charset($conn, 'utf8mb4');

// Messaggio di conferma
$messaggio = '';
$tipoMessaggio = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $citta             = trim($_POST['citta'] ?? '');
    $paese             = trim($_POST['paese'] ?? '');
    $prezzo            = (float)($_POST['prezzo'] ?? 0);
    $data_partenza     = $_POST['data_partenza'] ?? '';
    $data_ritorno      = $_POST['data_ritorno'] ?? '';
    $posti_disponibili = (int)($_POST['posti_disponibili'] ?? 0);

    if (empty($citta) || empty($paese) || $prezzo <= 0 || empty($data_partenza) || empty($data_ritorno) || $posti_disponibili <= 0) {
        $messaggio = 'Tutti i campi devono essere compilati correttamente.';
        $tipoMessaggio = 'danger';
    } else {
        $sql = "INSERT INTO destinazioni (citta, paese, prezzo, data_partenza, data_ritorno, posti_disponibili) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdssi", $citta, $paese, $prezzo, $data_partenza, $data_ritorno, $posti_disponibili);

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
$sql_dest = "SELECT id, citta, paese, prezzo, data_partenza, data_ritorno, posti_disponibili 
             FROM destinazioni ORDER BY citta ASC";
$res_dest = mysqli_query($conn, $sql_dest);
while ($row = mysqli_fetch_assoc($res_dest)) {
    $destinazioni[] = $row;
}
mysqli_free_result($res_dest);

include 'header.php';
?>

<div class="container mt-5">
    <h2>Destinazioni</h2>

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
            <input type="number" min="1" step="0.01" class="form-control" id="prezzo" name="prezzo" required>
        </div>
        <div class="mb-3">
            <label for="data_partenza" class="form-label">Data di Partenza</label>
            <input type="date" class="form-control" id="data_partenza" name="data_partenza" min="<?= date('Y-m-d') ?>" required>
        </div>
        <div class=" mb-3">
            <label for="data_ritorno" class="form-label">Data di Ritorno</label>
            <input type="date" class="form-control" id="data_ritorno" name="data_ritorno" required>
        </div>
        <div class="mb-3">
            <label for="posti_disponibili" class="form-label">Posti Disponibili</label>
            <input type="number" min="1" class="form-control" id="posti_disponibili" name="posti_disponibili" required>
        </div>
        <button type="submit" class="btn btn-primary">Salva</button>
        <a href="index.php" class="btn btn-secondary">Torna alla home</a>
    </form>
</div>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $citta             = trim($_POST['citta'] ?? '');
    $paese             = trim($_POST['paese'] ?? '');
    $prezzo            = (float)($_POST['prezzo'] ?? 0);
    $data_partenza     = $_POST['data_partenza'] ?? '';
    $data_ritorno      = $_POST['data_ritorno'] ?? '';
    $posti_disponibili = (int)($_POST['posti_disponibili'] ?? 0);

    $oggi = date('Y-m-d');

    if (empty($citta) || empty($paese) || $prezzo <= 0 || empty($data_partenza) || empty($data_ritorno) || $posti_disponibili <= 0) {
        $messaggio = 'Tutti i campi devono essere compilati correttamente.';
        $tipoMessaggio = 'danger';
    } elseif ($data_partenza < $oggi) {
        $messaggio = 'La data di partenza deve essere uguale o successiva a oggi.';
        $tipoMessaggio = 'danger';
    } elseif ($data_ritorno < $data_partenza) {
        $messaggio = 'La data di ritorno deve essere uguale o successiva alla data di partenza.';
        $tipoMessaggio = 'danger';
    } else {
        $sql = "INSERT INTO destinazioni (citta, paese, prezzo, data_partenza, data_ritorno, posti_disponibili) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdssi", $citta, $paese, $prezzo, $data_partenza, $data_ritorno, $posti_disponibili);

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
?>
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
                <th>Posti Disponibili</th>
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
                    <td><?= htmlspecialchars($d['posti_disponibili']) ?></td>
                    <td>
                        <a href="modifica_destinazioni.php?id=<?= (int)$d['id'] ?>" class="btn btn-sm btn-warning">✏️</a>
                        <a href="elimina_destinazioni.php?id=<?= (int)$d['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Eliminare questa destinazione?')">🗑️</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
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