<?php
require 'db.php';

// IMPORTANT: nessun output prima di header/redirect
mysqli_set_charset($conn, 'utf8mb4');

// Cancellazione prenotazione (prima di qualsiasi output)
if (isset($_GET['elimina'])) {
    $id = (int)$_GET['elimina'];
    if ($id > 0) {
        $stmtDel = $conn->prepare("DELETE FROM prenotazioni WHERE id = ?");
        if ($stmtDel) {
            $stmtDel->bind_param("i", $id);
            $ok = $stmtDel->execute();
            // Uso affected_rows dello statement per verificare la cancellazione
            $rows = $stmtDel->affected_rows;
            $stmtDel->close();

            if ($ok && $rows > 0) {
                header("Location: prenotazioni.php?success=eliminato");
                exit;
            } else {
                header("Location: prenotazioni.php?error=notfound");
                exit;
            }
        } else {
            header("Location: prenotazioni.php?error=insertfail");
            exit;
        }
    } else {
        header("Location: prenotazioni.php?error=invalid");
        exit;
    }
}

require 'header.php';

// Inserimento prenotazione
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validazione base
    $id_cliente       = isset($_POST['id_cliente']) ? (int)$_POST['id_cliente'] : 0;
    $id_destinazione  = isset($_POST['id_destinazione']) ? (int)$_POST['id_destinazione'] : 0;
    $dataprenotazione = isset($_POST['dataprenotazione']) ? trim($_POST['dataprenotazione']) : '';
    $acconto          = isset($_POST['acconto']) ? (float)$_POST['acconto'] : 0.0;
  
    $assicurazione    = isset($_POST['assicurazione']) ? 1 : 0;

    // Controlli minimi lato server
    $errors = [];
    if ($id_cliente <= 0)         $errors[] = 'Cliente non valido.';
    if ($id_destinazione <= 0)    $errors[] = 'Destinazione non valida.';
    if ($dataprenotazione === '') $errors[] = 'Data prenotazione obbligatoria.';
   
    if ($acconto < 0)             $errors[] = 'Acconto non può essere negativo.';

    if (!empty($errors)) {
        header("Location: prenotazioni.php?error=invalid");
        exit;
    }

    $sql  = "INSERT INTO prenotazioni 
             (id_cliente, id_destinazione, dataprenotazione, acconto,  assicurazione)
             VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        header("Location: prenotazioni.php?error=insertfail");
        exit;
    }

    // Tipi: i, i, s, d, i, i
    $stmt->bind_param("iisdi", $id_cliente, $id_destinazione, $dataprenotazione, $acconto,  $assicurazione);

    if ($stmt->execute()) {
        $stmt->close();
        header("Location: prenotazioni.php?success=aggiunto");
        exit;
    } else {
        $stmt->close();
        header("Location: prenotazioni.php?error=insertfail");
        exit;
    }
}

// Messaggi da query string
$messaggio = '';
$tipoMessaggio = '';
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'aggiunto') {
        $messaggio = 'Prenotazione aggiunta con successo!';
        $tipoMessaggio = 'success';
    } elseif ($_GET['success'] === 'modificato') {
        $messaggio = 'Prenotazione modificata con successo!';
        $tipoMessaggio = 'success';
    } elseif ($_GET['success'] === 'eliminato') {
        $messaggio = 'Prenotazione eliminata con successo!';
        $tipoMessaggio = 'success';
    }
}
if (isset($_GET['error'])) {
    if ($_GET['error'] === 'insertfail') {
        $messaggio = 'Errore durante l\'inserimento della prenotazione.';
        $tipoMessaggio = 'danger';
    } elseif ($_GET['error'] === 'notfound') {
        $messaggio = 'Prenotazione non trovata.';
        $tipoMessaggio = 'danger';
    } elseif ($_GET['error'] === 'invalid') {
        $messaggio = 'Dati non validi. Controlla i campi.';
        $tipoMessaggio = 'danger';
    }
}

// Recupera lista clienti
$clienti = [];
$sql_clienti = "SELECT id, cognome, nome FROM clienti ORDER BY cognome ASC";
if ($res_clienti = mysqli_query($conn, $sql_clienti)) {
    while ($row = mysqli_fetch_assoc($res_clienti)) {
        $clienti[] = $row;
    }
    mysqli_free_result($res_clienti);
}

// Recupera lista destinazioni
$destinazioni = [];
$sql_dest = "SELECT id, citta FROM destinazioni ORDER BY citta ASC";
if ($res_dest = mysqli_query($conn, $sql_dest)) {
    while ($row = mysqli_fetch_assoc($res_dest)) {
        $destinazioni[] = $row;
    }
    mysqli_free_result($res_dest);
}

// Recupera tutte le prenotazioni
$prenotazioni = [];
$sql_pren = "SELECT p.id, c.nome, c.cognome, d.citta, p.dataprenotazione, p.acconto,  p.assicurazione
             FROM prenotazioni p
             JOIN clienti c ON p.id_cliente = c.id
             JOIN destinazioni d ON p.id_destinazione = d.id
             ORDER BY p.dataprenotazione DESC";
if ($res_pren = mysqli_query($conn, $sql_pren)) {
    while ($row = mysqli_fetch_assoc($res_pren)) {
        $prenotazioni[] = $row;
    }
    mysqli_free_result($res_pren);
}
?>

<div class="container mt-5">
    <h2>Prenotazioni Clienti</h2>

    <!-- Form Prenotazioni -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Cliente</label>
                    <select name="id_cliente" class="form-select" required>
                        <option value="">-- Seleziona cliente --</option>
                        <?php foreach ($clienti as $c): ?>
                            <option value="<?= (int)$c['id'] ?>">
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
                            <option value="<?= (int)$d['id'] ?>">
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
                    <input type="number" step="0.01" name="acconto" class="form-control" required min="0">
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" name="assicurazione" class="form-check-input" id="assicurazione">
                    <label class="form-check-label" for="assicurazione">Assicurazione</label>
                </div>
                <button type="submit" class="btn btn-primary">Aggiungi Prenotazione</button>
            </form>
        </div>
    </div>

    <!-- Tabella Prenotazioni -->
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Destinazione</th>
                <th>Data</th>
                <th>Acconto (€)</th>
                
                <th>Assicurazione</th>
                <th>Azioni</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($prenotazioni as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['id'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($p['cognome'] . " " . $p['nome'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($p['citta'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($p['dataprenotazione'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($p['acconto'], ENT_QUOTES, 'UTF-8') ?></td>
                    
                    <td><?= ($p['assicurazione'] ? 'Sì' : 'No') ?></td>
                    <td>
                        <a href="modifica_prenotazione.php?id=<?= (int)$p['id'] ?>" class="btn btn-sm btn-warning">✏️</a>
                        <a class="btn btn-sm btn-danger" href="?elimina=<?= (int)$p['id'] ?>" onclick="return confirm('Eliminare questa prenotazione?')">🗑️</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modale Bootstrap per messaggi -->
<?php if (!empty($messaggio)): ?>
    <div class="modal fade" id="messaggioModal" tabindex="-1" aria-labelledby="messaggioModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-<?= htmlspecialchars($tipoMessaggio, ENT_QUOTES, 'UTF-8') ?>">
                    <h5 class="modal-title" id="messaggioModalLabel">Esito operazione</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body">
                    <?= htmlspecialchars($messaggio, ENT_QUOTES, 'UTF-8') ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var myModal = new bootstrap.Modal(document.getElementById("messaggioModal"));
            myModal.show();
        });
    </script>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<?php require 'footer.php'; ?>