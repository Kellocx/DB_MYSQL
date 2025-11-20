<?php
session_start();
require 'db.php';
require 'header.php';

// Imposta charset in modo OO e verifica connessione
if (!isset($conn) || !($conn instanceof mysqli)) {
    die('Connessione al database non disponibile.');
}
if (!$conn->set_charset('utf8mb4')) {
    error_log('Impossibile impostare charset: ' . $conn->error);
}

// Gestione eliminazione via POST (richiesto dalla modal di conferma)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['elimina_id']) && is_numeric($_POST['elimina_id'])) {
    $del_id = (int) $_POST['elimina_id'];
    $del_stmt = $conn->prepare("DELETE FROM prenotazioni WHERE id = ?");
    if ($del_stmt) {
        $del_stmt->bind_param('i', $del_id);
        if ($del_stmt->execute()) {
            $_SESSION['delete_success'] = 'Prenotazione eliminata con successo.';
        } else {
            $_SESSION['delete_error'] = 'Errore durante l\'eliminazione: ' . $del_stmt->error;
        }
        $del_stmt->close();
    } else {
        $_SESSION['delete_error'] = 'Impossibile preparare l\'eliminazione: ' . $conn->error;
    }
    // Redirect per evitare reinvio dell'azione se si ricarica la pagina
    header('Location: ricerca.php');
    exit;
}

// Recupera lista clienti
$clienti = [];
$sql_clienti = "SELECT id, cognome, nome FROM clienti ORDER BY cognome ASC";
if ($res_clienti = $conn->query($sql_clienti)) {
    while ($row = $res_clienti->fetch_assoc()) {
        $clienti[] = $row;
    }
    $res_clienti->free();
}

// Recupera lista destinazioni
$destinazioni = [];
$sql_dest = "SELECT id, citta FROM destinazioni ORDER BY citta ASC";
if ($res_dest = $conn->query($sql_dest)) {
    while ($row = $res_dest->fetch_assoc()) {
        $destinazioni[] = $row;
    }
    $res_dest->free();
}

// Costruzione query di ricerca
$where = [];
$params = [];
$types  = '';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Cliente
    if (isset($_GET['id_cliente']) && $_GET['id_cliente'] !== '') {
        $id_cliente = (int) $_GET['id_cliente'];
        $where[] = "p.id_cliente = ?";
        $params[] = $id_cliente;
        $types   .= 'i';
    }

    // Destinazione
    if (isset($_GET['id_destinazione']) && $_GET['id_destinazione'] !== '') {
        $id_dest = (int) $_GET['id_destinazione'];
        $where[] = "p.id_destinazione = ?";
        $params[] = $id_dest;
        $types   .= 'i';
    }

    // Data prenotazione (valido in formato YYYY-MM-DD)
    if (!empty($_GET['dataprenotazione'])) {
        $date = $_GET['dataprenotazione'];
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $where[] = "p.dataprenotazione = ?";
            $params[] = $date;
            $types   .= 's';
        }
    }

    // Ricerca libera su cognome, nome, citta
    if (!empty($_GET['search_text'])) {
        $search_raw = trim($_GET['search_text']);
        if ($search_raw !== '') {
            $where[] = "(c.cognome LIKE ? OR c.nome LIKE ? OR d.citta LIKE ?)";
            $search = '%' . $search_raw . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $types   .= 'sss';
        }
    }

    // Acconto (valore preciso)
    if (isset($_GET['acconto']) && $_GET['acconto'] !== '') {
        $ac = $_GET['acconto'];
        if (is_numeric($ac)) {
            $where[] = "p.acconto = ?";
            $params[] = (float) $ac;
            $types   .= 'd';
        }
    }

    // Posti disponibili (minimo)
    if (isset($_GET['posti_disponibili']) && $_GET['posti_disponibili'] !== '') {
        $pd = $_GET['posti_disponibili'];
        if (is_numeric($pd)) {
            $where[] = "d.posti_disponibili >= ?";
            $params[] = (int) $pd;
            $types   .= 'i';
        }
    }

    // Assicurazione (0 o 1)
    if (isset($_GET['assicurazione']) && $_GET['assicurazione'] !== '') {
        if ($_GET['assicurazione'] === '0' || $_GET['assicurazione'] === '1') {
            $ass = (int) $_GET['assicurazione'];
            $where[] = "p.assicurazione = ?";
            $params[] = $ass;
            $types   .= 'i';
        }
    }
}

// Query principale: includiamo d.posti_disponibili
$sql = "SELECT p.id, c.nome, c.cognome, d.citta, d.posti_disponibili, p.dataprenotazione, p.acconto, p.assicurazione
        FROM prenotazioni p
        JOIN clienti c ON p.id_cliente = c.id
        JOIN destinazioni d ON p.id_destinazione = d.id";

if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY p.dataprenotazione DESC";

$prenotazioni = [];

if ($stmt = $conn->prepare($sql)) {
    if ($where && $types !== '') {
        $bind_names = [];
        $bind_types = $types;
        $bind_names[] = &$bind_types;
        for ($i = 0; $i < count($params); $i++) {
            $bind_names[] = &$params[$i];
        }
        call_user_func_array([$stmt, 'bind_param'], $bind_names);
    }

    if (!$stmt->execute()) {
        error_log('Errore esecuzione statement: ' . $stmt->error);
    } else {
        $res = $stmt->get_result();
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $prenotazioni[] = $row;
            }
            $res->free();
        }
    }
    $stmt->close();
} else {
    error_log('Errore prepare SQL: ' . $conn->error);
}

// Costruisci URI corrente per passare come back param ai link di modifica.
$current_request_uri = $_SERVER['REQUEST_URI'];
?>
<?php
if (!empty($_SESSION['delete_success']) || !empty($_SESSION['delete_error'])): ?>
    <!-- Modal Esito Operazione -->
    <div class="modal fade" id="esitoModal" tabindex="-1" aria-labelledby="esitoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="esitoModalLabel">Esito operazione</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body">
                    <?php if (!empty($_SESSION['delete_success'])): ?>
                        <div class="alert alert-success mb-0" role="alert">
                            <?= htmlspecialchars($_SESSION['delete_success'], ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($_SESSION['delete_error'])): ?>
                        <div class="alert alert-danger mb-0" role="alert">
                            <?= htmlspecialchars($_SESSION['delete_error'], ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var el = document.getElementById('esitoModal');
            if (el) {
                var m = new bootstrap.Modal(el);
                m.show();
                el.addEventListener('hidden.bs.modal', function() {
                    window.location.href = 'ricerca.php';
                });
            }
        });
    </script>

<?php
    unset($_SESSION['delete_success'], $_SESSION['delete_error']);
endif;
?>

<div class="container mt-5">
    <h2>Ricerca Prenotazioni</h2>

    <!-- Messaggi successo / errore eliminazione -->
    <?php if (!empty($_SESSION['delete_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['delete_success'], ENT_QUOTES, 'UTF-8') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Chiudi"></button>
        </div>
        <?php unset($_SESSION['delete_success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['delete_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['delete_error'], ENT_QUOTES, 'UTF-8') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Chiudi"></button>
        </div>
        <?php unset($_SESSION['delete_error']); ?>
    <?php endif; ?>

    <!-- Form ricerca (ripristinato alla versione precedente: singoli blocchi mb-3) -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" action="ricerca.php">
                <div class="mb-3">
                    <label class="form-label">Cliente</label>
                    <select name="id_cliente" class="form-select">
                        <option value="">-- Tutti i clienti --</option>
                        <?php foreach ($clienti as $c): ?>
                            <option value="<?= htmlspecialchars($c['id'], ENT_QUOTES, 'UTF-8') ?>"
                                <?= isset($_GET['id_cliente']) && $_GET['id_cliente'] == $c['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['cognome'] . " " . $c['nome'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Destinazione</label>
                    <select name="id_destinazione" class="form-select">
                        <option value="">-- Tutte le destinazioni --</option>
                        <?php foreach ($destinazioni as $d): ?>
                            <option value="<?= htmlspecialchars($d['id'], ENT_QUOTES, 'UTF-8') ?>"
                                <?= isset($_GET['id_destinazione']) && $_GET['id_destinazione'] == $d['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($d['citta'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Data Prenotazione</label>
                    <input type="date" name="dataprenotazione" class="form-control" value="<?= htmlspecialchars($_GET['dataprenotazione'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Ricerca libera (cliente/destinazione)</label>
                    <input type="text" name="search_text" class="form-control" value="<?= htmlspecialchars($_GET['search_text'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Inserisci cognome, nome o città">
                </div>

                <div class="mb-3">
                    <label class="form-label">Acconto (€)</label>
                    <input type="number" step="0.01" name="acconto" class="form-control" value="<?= htmlspecialchars($_GET['acconto'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Valore esatto acconto">
                </div>

                <div class="mb-3">
                    <label class="form-label">Posti disponibili (min)</label>
                    <input type="number" name="posti_disponibili" class="form-control" min="0" value="<?= htmlspecialchars($_GET['posti_disponibili'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Minimo posti disponibili">
                </div>

                <div class="mb-3">
                    <label class="form-label">Assicurazione</label>
                    <select name="assicurazione" class="form-select">
                        <option value="">-- Tutte --</option>
                        <option value="1" <?= isset($_GET['assicurazione']) && $_GET['assicurazione'] === '1' ? 'selected' : '' ?>>Sì</option>
                        <option value="0" <?= isset($_GET['assicurazione']) && $_GET['assicurazione'] === '0' ? 'selected' : '' ?>>No</option>
                    </select>
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-primary">Cerca</button>
                    <a href="ricerca.php" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Risultati: tabella responsive tramite classi Bootstrap -->
    <div class="table-responsive">
        <table class="table table-striped table-hover table-sm align-middle">
            <thead class="table-light">
                <tr>
                    <th>Cliente</th>
                    <th>Destinazione</th>
                    <th>Posti disponibili</th>
                    <th>Data</th>
                    <th>Acconto (€)</th>
                    <th class="d-none d-sm-table-cell">Assicurazione</th>
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($prenotazioni): ?>
                    <?php foreach ($prenotazioni as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['cognome'] . " " . $p['nome'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($p['citta'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((isset($p['posti_disponibili']) ? (int)$p['posti_disponibili'] : '-'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($p['dataprenotazione'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars(number_format((float)$p['acconto'], 2, ',', '.'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="d-none d-sm-table-cell"><?= ((int)$p['assicurazione']) ? 'Sì' : 'No' ?></td>
                            <td>
                                <div class="d-flex flex-column flex-sm-row gap-1">
                                    <a href="modifica_prenotazione.php?id=<?= urlencode($p['id']) ?>&back=<?= urlencode($current_request_uri) ?>" class="btn btn-sm btn-warning" title="Modifica">✏️</a>

                                    <button type="button"
                                        class="btn btn-sm btn-danger btn-delete"
                                        data-id="<?= htmlspecialchars($p['id'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-info="<?= htmlspecialchars($p['cognome'] . ' ' . $p['nome'] . ' — ' . $p['citta'] . ' (' . $p['dataprenotazione'] . ')', ENT_QUOTES, 'UTF-8') ?>"
                                        title="Elimina">
                                        🗑️
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">Nessuna prenotazione trovata</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal di conferma eliminazione -->
<form method="post" id="deleteForm" style="display:none;">
    <input type="hidden" name="elimina_id" id="elimina_id" value="">
</form>

<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmDeleteModalLabel">Conferma Eliminazione</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
            </div>
            <div class="modal-body">
                Sei sicuro di voler eliminare la seguente prenotazione?
                <div class="mt-2"><strong id="delInfo"></strong></div>
                <div class="text-muted small mt-2">Questa operazione non è reversibile.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                <button type="button" id="confirmDeleteBtn" class="btn btn-danger">Elimina</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var deleteButtons = document.querySelectorAll('.btn-delete');
        var deleteModalEl = document.getElementById('confirmDeleteModal');
        var delInfoSpan = document.getElementById('delInfo');
        var eliminaInput = document.getElementById('elimina_id');
        var confirmBtn = document.getElementById('confirmDeleteBtn');

        var bootstrapModal = null;

        deleteButtons.forEach(function(btn) {
            btn.addEventListener('click', function() {
                var id = this.dataset.id || '';
                var info = this.dataset.info || '';
                eliminaInput.value = id;
                delInfoSpan.textContent = info;
                if (!bootstrapModal) {
                    bootstrapModal = new bootstrap.Modal(deleteModalEl);
                }
                bootstrapModal.show();
            });
        });

        confirmBtn.addEventListener('click', function() {
            document.getElementById('deleteForm').submit();
        });
    });
</script>

<?php include 'footer.php'; ?>