<?php
require 'db.php';
mysqli_set_charset($conn, 'utf8mb4');

$messaggio = '';
$tipoMessaggio = '';

$id_prenotazione = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null;
if (!$id_prenotazione) {
    header("Location: prenotazioni.php?error=invalid");
    exit;
}

// Recupera dati prenotazione
$sql = "SELECT id_cliente, id_destinazione,  acconto,  assicurazione 
        FROM prenotazioni WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_prenotazione);
$stmt->execute();
$stmt->bind_result($id_cliente, $id_destinazione, $acconto, $assicurazione);
if (!$stmt->fetch()) {
    $stmt->close();
    header("Location: prenotazioni.php?error=notfound");
    exit;
}
$stmt->close();

// Se arriva POST: aggiorna
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_cliente      = (int)$_POST['id_cliente'];
    $id_destinazione = (int)$_POST['id_destinazione'];

    $acconto         = (float)$_POST['acconto'];

    $assicurazione   = isset($_POST['assicurazione']) ? 1 : 0;

    $sql = "UPDATE prenotazioni 
            SET id_cliente=?, id_destinazione=?,  acconto=?,  assicurazione=? 
            WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iidii", $id_cliente, $id_destinazione,  $acconto, $assicurazione, $id_prenotazione);

    if ($stmt->execute()) {
        $messaggio = "Anagrafica prenotazione modificata con successo!";
        $tipoMessaggio = "success";
    } else {
        $messaggio = "Errore: " . htmlspecialchars($stmt->error, ENT_QUOTES, 'UTF-8');
        $tipoMessaggio = "danger";
    }
    $stmt->close();
}

// Recupera lista clienti
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

// Determina l'URL di ritorno (back): preferisce il parametro 'back' se è un percorso relativo,
// altrimenti usa HTTP_REFERER solo se è dello stesso host; altrimenti fallback a null.
$backUrl = null;
if (!empty($_GET['back'])) {
    $backCandidate = $_GET['back'];
    // accettiamo solo percorsi relativi che iniziano con '/' per evitare open redirects
    if (strpos($backCandidate, '/') === 0 && strpos($backCandidate, '//') !== 0) {
        $backUrl = $backCandidate;
    }
} elseif (!empty($_SERVER['HTTP_REFERER'])) {
    $ref = $_SERVER['HTTP_REFERER'];
    $parts = parse_url($ref);
    if (isset($parts['host']) && $parts['host'] === $_SERVER['HTTP_HOST']) {
        $path = $parts['path'] ?? '/';
        $query = isset($parts['query']) ? '?' . $parts['query'] : '';
        $backUrl = $path . $query;
    }
}

include 'header.php';
?>

<div class="container mt-5">
    <div class="d-flex align-items-center mb-3">
        <h2 class="me-auto">Modifica Prenotazione</h2>

        <!-- Bottone Back: se esiste $backUrl è un link sicuro, altrimenti usa history.back() -->
        <?php if ($backUrl): ?>
            <a href="<?= htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline-secondary me-2">
                &larr; Torna indietro
            </a>
        <?php else: ?>
            <button type="button" class="btn btn-outline-secondary me-2" onclick="history.back();">
                &larr; Torna indietro
            </button>
        <?php endif; ?>
    </div>

    <form method="post">
        <div class="mb-3">
            <label class="form-label">Cliente</label>
            <select name="id_cliente" class="form-select" required>
                <?php foreach ($clienti as $c): ?>
                    <option value="<?= htmlspecialchars($c['id'], ENT_QUOTES, 'UTF-8') ?>" <?= $c['id'] == $id_cliente ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['cognome'] . " " . $c['nome'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Destinazione</label>
            <select name="id_destinazione" class="form-select" required>
                <?php foreach ($destinazioni as $d): ?>
                    <option value="<?= htmlspecialchars($d['id'], ENT_QUOTES, 'UTF-8') ?>" <?= $d['id'] == $id_destinazione ? 'selected' : '' ?>>
                        <?= htmlspecialchars($d['citta'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Acconto (€)</label>
            <input type="number" step="0.01" name="acconto" class="form-control" value="<?= htmlspecialchars($acconto, ENT_QUOTES, 'UTF-8') ?>" required>
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" name="assicurazione" class="form-check-input" id="assicurazione" <?= $assicurazione ? 'checked' : '' ?>>
            <label class="form-check-label" for="assicurazione">Assicurazione inclusa</label>
        </div>
        <div class="mb-3">
            <button type="submit" class="btn btn-warning">Aggiorna Prenotazione</button>

            <!-- Pulsante Annulla: se abbiamo un backUrl lo usiamo, altrimenti ritorniamo alla lista prenotazioni -->
            <?php if ($backUrl): ?>
                <a href="<?= htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-secondary ms-2">Annulla</a>
            <?php else: ?>
                <a href="prenotazioni.php" class="btn btn-secondary ms-2">Annulla</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Modale Bootstrap per messaggio esito operazione -->
<?php if (!empty($messaggio)): ?>
    <div class="modal fade" id="messaggioModal" tabindex="-1" aria-labelledby="messaggioModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content bg-white">
                <div class="modal-header">
                    <h5 class="modal-title" id="messaggioModalLabel">Esito operazione</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-<?= $tipoMessaggio ?>">
                    <?= htmlspecialchars($messaggio, ENT_QUOTES, 'UTF-8') ?>
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
                modalEl.addEventListener('hidden.bs.modal', () => {
                    // dopo la chiusura ritorniamo alla lista prenotazioni
                    window.location.href = 'prenotazioni.php';
                });
            }
        });
    </script>
<?php endif; ?>

<script>
    // Protezione: controlliamo che gli elementi esistano prima di registrarvi gli event listener
    if (document.getElementById('btnConfermaElimina')) {
        let idDaEliminare = null;

        function confermaEliminazione(id) {
            idDaEliminare = id;
            const modal = new bootstrap.Modal(document.getElementById('confermaModal'));
            modal.show();
        }

        document.getElementById('btnConfermaElimina').addEventListener('click', () => {
            if (!idDaEliminare) return;

            // Chiamata AJAX a elimina_destinazioni.php
            fetch('elimina_destinazioni.php?id=' + encodeURIComponent(idDaEliminare), {
                    method: 'GET'
                })
                .then(response => response.text())
                .then(data => {
                    // Non possiamo trovare la riga in modo affidabile qui senza markup specifico, quindi
                    // suggeriamo di ricaricare la pagina per aggiornare la tabella.
                    location.reload();
                })
                .catch(err => {
                    alert("Errore durante l'eliminazione: " + err);
                });
        });
    }
</script>

<?php include 'footer.php'; ?>