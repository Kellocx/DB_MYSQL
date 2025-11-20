<?php
require 'db.php';
mysqli_set_charset($conn, 'utf8mb4');

$messaggio = '';
$tipoMessaggio = '';

// Gestione della cancellazione (usa redirect per evitare doppie esecuzioni su refresh)
if (isset($_GET['elimina'])) {
    // Valida l'id come intero
    $id = filter_input(INPUT_GET, 'elimina', FILTER_VALIDATE_INT);
    if ($id === false || $id === null) {
        header('Location: destinazioni.php?error=invalid');
        exit;
    }

    // Prepara la query e verifica eventuali errori
    $stmt = $conn->prepare("DELETE FROM destinazioni WHERE id = ?");
    if (!$stmt) {
        // Errore di prepare
        header('Location: destinazioni.php?error=deletefail');
        exit;
    }

    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $stmt->close();
        // Redirect con flag di successo per mostrare il modal in GET
        header('Location: destinazioni.php?success=eliminata');
        exit;
    } else {
        $stmt->close();
        header('Location: destinazioni.php?error=deletefail');
        exit;
    }
}

// Gestione dei messaggi (da parametri GET)
if (isset($_GET['success']) && $_GET['success'] === 'eliminata') {
    $messaggio = "Destinazione eliminata con successo!";
    $tipoMessaggio = "success";
} elseif (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'invalid':
            $messaggio = "ID non valido.";
            break;
        case 'notfound':
            $messaggio = "Destinazione non trovata.";
            break;
        case 'deletefail':
        default:
            $messaggio = "Errore durante l'eliminazione.";
            break;
    }
    $tipoMessaggio = "danger";
}

// Valori form (per preservare i dati in caso di errore)
$val_citta = '';
$val_paese = '';
$val_prezzo = '';
$val_data_partenza = '';
$val_data_ritorno = '';
$val_posti_disponibili = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $citta             = trim($_POST['citta'] ?? '');
    $paese             = trim($_POST['paese'] ?? '');
    $prezzo            = (float)($_POST['prezzo'] ?? 0);
    $data_partenza     = $_POST['data_partenza'] ?? '';
    $data_ritorno      = $_POST['data_ritorno'] ?? '';
    $posti_disponibili = (int)($_POST['posti_disponibili'] ?? 0);

    // Preserva i valori per il form
    $val_citta = $citta;
    $val_paese = $paese;
    $val_prezzo = $_POST['prezzo'] ?? '';
    $val_data_partenza = $data_partenza;
    $val_data_ritorno = $data_ritorno;
    $val_posti_disponibili = $_POST['posti_disponibili'] ?? '';

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
        if (!$stmt) {
            $messaggio = 'Errore durante la preparazione della query: ' . htmlspecialchars($conn->error, ENT_QUOTES, 'UTF-8');
            $tipoMessaggio = 'danger';
        } else {
            // types: s (citta), s (paese), d (prezzo), s (data_partenza), s (data_ritorno), i (posti_disponibili)
            $stmt->bind_param("ssdssi", $citta, $paese, $prezzo, $data_partenza, $data_ritorno, $posti_disponibili);

            if ($stmt->execute()) {
                $messaggio = 'Destinazione aggiunta con successo!';
                $tipoMessaggio = 'success';
                // Svuota i valori form solo in caso di successo
                $val_citta = $val_paese = $val_prezzo = $val_data_partenza = $val_data_ritorno = $val_posti_disponibili = '';
            } else {
                $messaggio = 'Errore durante l\'inserimento: ' . htmlspecialchars($stmt->error, ENT_QUOTES, 'UTF-8');
                $tipoMessaggio = 'danger';
            }
            $stmt->close();
        }
    }
}

// Recupero tutte le destinazioni
$destinazioni = [];
$sql_dest = "SELECT id, citta, paese, prezzo, data_partenza, data_ritorno, posti_disponibili 
             FROM destinazioni ORDER BY citta ASC";
$res_dest = mysqli_query($conn, $sql_dest);
if ($res_dest) {
    while ($row = mysqli_fetch_assoc($res_dest)) {
        $destinazioni[] = $row;
    }
    // libera solo se è un risultato valido
    if ($res_dest instanceof mysqli_result) {
        mysqli_free_result($res_dest);
    }
} else {
    $messaggio = 'Errore recupero destinazioni: ' . htmlspecialchars(mysqli_error($conn), ENT_QUOTES, 'UTF-8');
    $tipoMessaggio = 'danger';
}

include 'header.php';
?>

<div class="container mt-5">
    <h2>Destinazioni</h2>

    <form method="POST" onsubmit="return validaDate();">
        <div class="mb-3">
            <label for="citta" class="form-label">Città</label>
            <input type="text" class="form-control" id="citta" name="citta" value="<?= htmlspecialchars($val_citta, ENT_QUOTES, 'UTF-8') ?>" required>
        </div>
        <div class="mb-3">
            <label for="paese" class="form-label">Paese</label>
            <input type="text" class="form-control" id="paese" name="paese" value="<?= htmlspecialchars($val_paese, ENT_QUOTES, 'UTF-8') ?>" required>
        </div>
        <div class="mb-3">
            <label for="prezzo" class="form-label">Prezzo (€)</label>
            <input type="number" min="1" step="0.01" class="form-control" id="prezzo" name="prezzo" value="<?= htmlspecialchars($val_prezzo, ENT_QUOTES, 'UTF-8') ?>" required>
        </div>
        <div class="mb-3">
            <label for="data_partenza" class="form-label">Data di Partenza</label>
            <input type="date" class="form-control" id="data_partenza" name="data_partenza" min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($val_data_partenza, ENT_QUOTES, 'UTF-8') ?>" required>
        </div>
        <div class="mb-3">
            <label for="data_ritorno" class="form-label">Data di Ritorno</label>
            <input type="date" class="form-control" id="data_ritorno" name="data_ritorno" value="<?= htmlspecialchars($val_data_ritorno, ENT_QUOTES, 'UTF-8') ?>" required>
        </div>
        <div class="mb-3">
            <label for="posti_disponibili" class="form-label">Posti Disponibili</label>
            <input type="number" min="1" class="form-control" id="posti_disponibili" name="posti_disponibili" value="<?= htmlspecialchars($val_posti_disponibili, ENT_QUOTES, 'UTF-8') ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Salva</button>
        <a href="index.php" class="btn btn-secondary">Torna alla home</a>
    </form>
</div>

<div class="container mt-5 ">
    <h2>Elenco Destinazioni</h2>
    <div class="table-responsive-sm">
        <table class="table table-striped table-responsive">
            <thead>
                <tr>
                    <th scope="row">ID</th>
                    <th scope="row">Città</th>
                    <th scope="row">Paese</th>
                    <th scope="row">Prezzo (€)</th>
                    <th scope="row">Data Partenza</th>
                    <th scope="row">Data Ritorno</th>
                    <th scope="row">Posti Disponibili</th>
                    <th scope="row">Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($destinazioni as $d): ?>
                    <tr>
                        <td><?= htmlspecialchars($d['id'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($d['citta'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($d['paese'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($d['prezzo'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($d['data_partenza'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($d['data_ritorno'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($d['posti_disponibili'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <a href="modifica_destinazioni.php?id=<?= (int)$d['id'] ?>"
                                class="btn btn-sm btn-warning"
                                title="Modifica destinazione" aria-label="Modifica destinazione">✏️</a>

                            <a href="?elimina=<?= (int)$d['id'] ?>"
                                class="btn btn-sm btn-danger"
                                title="Elimina destinazione" aria-label="Elimina destinazione"
                                onclick="return confirm('Eliminare questa destinazione?')">🗑️</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (!empty($messaggio)): ?>
    <div class="modal fade" id="messaggioModal" tabindex="-1" aria-labelledby="messaggioModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content bg-white">
                <div class="modal-header">
                    <h5 class="modal-title" id="messaggioModalLabel">Esito operazione</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body <?= $tipoMessaggio === 'success' ? 'text-success' : ($tipoMessaggio === 'danger' ? 'text-danger' : 'text-warning') ?>">
                    <?= htmlspecialchars($messaggio, ENT_QUOTES, 'UTF-8') ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Modale Bootstrap errore JS -->
<div class="modal fade" id="erroreModal" tabindex="-1" aria-labelledby="erroreModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-white">
            <div class="modal-header">
                <h5 class="modal-title" id="erroreModalLabel">Correzione richiesta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
            </div>
            <div class="modal-body text-danger" id="erroreModalBody">
                <!-- Testo errore inserito via JS -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Ok, correggo</button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" defer></script>

<?php if (!empty($messaggio)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modalEl = document.getElementById('messaggioModal');
            if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const modal = new bootstrap.Modal(modalEl);
                modal.show();

                // Redirect opzionale SOLO se success
                <?php if ($tipoMessaggio === 'success'): ?>
                    modalEl.addEventListener('hidden.bs.modal', () => {
                        // Ricarica la pagina per pulire eventuali GET param
                        location.href = 'destinazioni.php';
                    });
                <?php endif; ?>
            }
        });
    </script>
<?php endif; ?>

<!-- Funzioni JavaScript per validare le date e preservare i dati nel form -->
<script>
    function validaDate() {
        const oggi = new Date().toISOString().split('T')[0];
        const partenzaEl = document.getElementById('data_partenza');
        const ritornoEl = document.getElementById('data_ritorno');

        // Se gli elementi non esistono, non bloccare il submit
        if (!partenzaEl || !ritornoEl) return true;

        const partenza = partenzaEl.value;
        const ritorno = ritornoEl.value;

        if (partenza && partenza < oggi) {
            mostraErrore("La data di partenza deve essere uguale o successiva a oggi.");
            return false;
        }
        if (partenza && ritorno && ritorno < partenza) {
            mostraErrore("La data di ritorno deve essere uguale o successiva alla data di partenza.");
            return false;
        }
        return true;
    }

    // Mostra un modal Bootstrap con errore senza perdere i dati del form
    function mostraErrore(testo) {
        const bodyEl = document.getElementById('erroreModalBody');
        const modalWrap = document.getElementById('erroreModal');
        if (!bodyEl || !modalWrap || typeof bootstrap === 'undefined' || !bootstrap.Modal) return;

        bodyEl.textContent = testo;
        const modal = new bootstrap.Modal(modalWrap);
        modal.show();
    }

    // Imposta automaticamente il min della data di ritorno in base alla partenza
    function impostaMinRitorno() {
        const partenzaEl = document.getElementById('data_partenza');
        const ritornoEl = document.getElementById('data_ritorno');
        if (partenzaEl && ritornoEl && partenzaEl.value) {
            ritornoEl.setAttribute('min', partenzaEl.value);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        impostaMinRitorno();
        const partenzaEl = document.getElementById('data_partenza');
        if (partenzaEl) {
            partenzaEl.addEventListener('change', impostaMinRitorno);
        }
    });
</script>
<?php include 'footer.php'; ?>