<?php
require 'db.php';
require 'header.php';
mysqli_set_charset($conn, 'utf8mb4');

// Inserimento prenotazione
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_cliente      = (int)$_POST['id_cliente'];
    $id_destinazione = (int)$_POST['id_destinazione'];
    $dataprenotazione = $_POST['dataprenotazione'];
    $acconto         = (float)$_POST['acconto'];
    $numero_persone  = (int)$_POST['numero_persone'];
    $assicurazione   = isset($_POST['assicurazione']) ? 1 : 0;

    $sql = "INSERT INTO prenotazioni (id_cliente, id_destinazione, dataprenotazione, acconto, numero_persone, assicurazione)
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisdis", $id_cliente, $id_destinazione, $dataprenotazione, $acconto, $numero_persone, $assicurazione);

    if ($stmt->execute()) {
        header("Location: prenotazioni.php?success=aggiunto");
        exit;
    } else {
        header("Location: prenotazioni.php?error=insertfail");
        exit;
    }
    $stmt->close();
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
        $messaggio = 'ID prenotazione non valido.';
        $tipoMessaggio = 'danger';
    }
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

// Recupera tutte le prenotazioni
$prenotazioni = [];
$sql_pren = "SELECT p.id, c.nome, c.cognome, d.citta, p.dataprenotazione, p.acconto, p.numero_persone, p.assicurazione
             FROM prenotazioni p
             JOIN clienti c ON p.id_cliente = c.id
             JOIN destinazioni d ON p.id_destinazione = d.id
             ORDER BY p.dataprenotazione DESC";
$res_pren = mysqli_query($conn, $sql_pren);
while ($row = mysqli_fetch_assoc($res_pren)) {
    $prenotazioni[] = $row;
}
mysqli_free_result($res_pren);


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
                    <button type="submit" class="btn btn-primary">Aggiungi</button>
                    <a href="index.php" class="btn btn-secondary">Annulla</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabella prenotazioni -->
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Destinazione</th>
                <th>Data</th>
                <th>Acconto (€)</th>
                <th>Persone</th>
                <th>Assicurazione</th>
                <th>Azioni</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($prenotazioni as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['id']) ?></td>
                    <td><?= htmlspecialchars($p['cognome'] . " " . $p['nome']) ?></td>
                    <td><?= htmlspecialchars($p['citta']) ?></td>
                    <td><?= htmlspecialchars($p['dataprenotazione']) ?></td>
                    <td><?= htmlspecialchars($p['acconto']) ?></td>
                    <td><?= htmlspecialchars($p['numero_persone']) ?></td>
                    <td><?= $p['assicurazione'] ? 'Sì' : 'No' ?></td>
                    <td>
                        <a href="modifica_prenotazione.php?id=<?= (int)$p['id'] ?>" class="btn btn-sm btn-warning">✏️</a>
                        <a class="btn btn-sm btn-danger" href="?elimina=<?= htmlspecialchars($p['id']) ?>" onclick="return confirm('Eliminare questa prenotazione?')">🗑️</a>
                       
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


<?php

// CANCELLAZIONE CLIENTE
if (isset($_GET['elimina'])) {

    $id = intval($_GET['elimina']);
    $conn->query("DELETE FROM prenotazioni WHERE id = $id");

    // Messaggio di conferma
    $messaggio = "Cliente eliminato con successo!";
    $tipoMessaggio = "success";
}
?>
<?php if (!empty($messaggio)): ?>
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-<?php echo $tipoMessaggio; ?>">
                    <h5 class="modal-title" id="deleteModalLabel">Notifica</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body">
                    <?php echo $messaggio; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var myModal = new bootstrap.Modal(document.getElementById("deleteModal"));
            myModal.show();
        });
    </script>
<?php endif; ?>















<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<?php include 'footer.php'; ?>