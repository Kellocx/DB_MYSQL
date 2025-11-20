<?php
include 'header.php';
include 'db.php';
mysqli_set_charset($conn, 'utf8mb4');

// Configurazione
$perPagina = 10;
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, ['options' => ['default' => 1, 'min_range' => 1]]);
$offset = ($page - 1) * $perPagina;

// Messaggi da query string (visualizzati via modal)
$messaggio = '';
$tipoMessaggio = '';
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'aggiunto':
            $messaggio = "Cliente aggiunto con successo!";
            $tipoMessaggio = "success";
            break;
        case 'eliminato':
            $messaggio = "Cliente eliminato con successo!";
            $tipoMessaggio = "success";
            break;
    }
}
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'insertfail':
            $messaggio = "Errore durante l'inserimento.";
            break;
        case 'deletefail':
            $messaggio = "Impossibile eliminare il cliente (vincoli referenziali o errore DB).";
            break;
        case 'notfound':
            $messaggio = "Cliente non trovato.";
            break;
        case 'invalid':
            $messaggio = "ID cliente non valido.";
            break;
        case 'uploadfail':
            $messaggio = "Caricamento documento fallito.";
            break;
        default:
            $messaggio = "Errore.";
    }
    $tipoMessaggio = "danger";
}

// Gestione eliminazione (redirect dopo l'azione per evitare ri-invio)
if (isset($_GET['elimina'])) {
    $idElimina = filter_input(INPUT_GET, 'elimina', FILTER_VALIDATE_INT);
    if ($idElimina === false || $idElimina === null) {
        header('Location: clienti.php?error=invalid');
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM clienti WHERE id = ?");
    if (!$stmt) {
        header('Location: clienti.php?error=deletefail');
        exit;
    }
    $stmt->bind_param('i', $idElimina);
    if ($stmt->execute()) {
        $stmt->close();
        header('Location: clienti.php?success=eliminato');
        exit;
    } else {
        // Se l'errore è vincolo FK (errno 1451) mostra messaggio chiaro
        $errno = $stmt->errno;
        $stmt->close();
        if ($errno === 1451) {
            header('Location: clienti.php?error=deletefail');
        } else {
            header('Location: clienti.php?error=deletefail');
        }
        exit;
    }
}

// Gestione inserimento cliente (supporto upload documento)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome           = trim($_POST['nome'] ?? '');
    $cognome        = trim($_POST['cognome'] ?? '');
    $data_nascita   = $_POST['data_nascita'] ?? '';
    $email          = trim($_POST['email'] ?? '');
    $telefono       = trim($_POST['telefono'] ?? '');
    $nazione        = trim($_POST['nazione'] ?? '');
    $codice_fiscale = trim($_POST['codice_fiscale'] ?? '');
    $documento_db   = null; // valore salvato in DB (nome file o null)

    // Validazioni base (puoi estenderle)
    if ($nome === '' || $cognome === '' || $data_nascita === '' || $email === '' || $telefono === '' || $nazione === '') {
        header('Location: clienti.php?error=insertfail');
        exit;
    }

    // Gestione file (se inviato)
    if (isset($_FILES['documento']) && $_FILES['documento']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['documento'];
        if ($file['error'] === UPLOAD_ERR_OK) {
            // Limiti e tipi ammessi
            $maxSize = 2 * 1024 * 1024; // 2 MB
            $allowed = [
                'application/pdf' => 'pdf',
                'image/jpeg' => 'jpg',
                'image/png' => 'png'
            ];
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file['tmp_name']);
            if (!array_key_exists($mime, $allowed) || $file['size'] > $maxSize) {
                header('Location: clienti.php?error=uploadfail');
                exit;
            }
            // Crea cartella upload se non esiste
            $uploadDir = __DIR__ . '/uploads/documents';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            // Nome file unico
            $ext = $allowed[$mime];
            $baseName = bin2hex(random_bytes(8)) . '.' . $ext;
            $targetPath = $uploadDir . '/' . $baseName;
            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                header('Location: clienti.php?error=uploadfail');
                exit;
            }
            // Salviamo solo il nome file relativo (non il path assoluto)
            $documento_db = 'uploads/documents/' . $baseName;
        } else {
            header('Location: clienti.php?error=uploadfail');
            exit;
        }
    }

    // Inserimento con prepared statement
    $sql = "INSERT INTO clienti (nome, cognome, data_nascita, email, telefono, nazione, codice_fiscale, documento)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        header('Location: clienti.php?error=insertfail');
        exit;
    }
    // Se documento_db è null, bind_param accetta stringa vuota; convertiamo
    $docToSave = $documento_db ?? '';
    $stmt->bind_param('ssssssss', $nome, $cognome, $data_nascita, $email, $telefono, $nazione, $codice_fiscale, $docToSave);
    if ($stmt->execute()) {
        $stmt->close();
        header('Location: clienti.php?success=aggiunto');
        exit;
    } else {
        // Se abbiamo caricato un file ma l'inserimento fallisce, rimuoviamo il file appena caricato
        if (!empty($documento_db) && file_exists(__DIR__ . '/' . $documento_db)) {
            @unlink(__DIR__ . '/' . $documento_db);
        }
        $stmt->close();
        header('Location: clienti.php?error=insertfail');
        exit;
    }
}

// Conteggio totale clienti
$totalRow = $conn->query("SELECT COUNT(*) AS t FROM clienti");
$total = 0;
if ($totalRow) {
    $total = (int)($totalRow->fetch_assoc()['t'] ?? 0);
}
$totalPages = max(1, (int)ceil($total / $perPagina));

// Recupero clienti per la pagina corrente (i valori sono castati per sicurezza)
$perPagina_i = (int)$perPagina;
$offset_i = (int)$offset;
$sql = "SELECT * FROM clienti ORDER BY cognome ASC LIMIT $perPagina_i OFFSET $offset_i";
$result = $conn->query($sql);
if ($result === false) {
    $messaggio = 'Errore recupero clienti: ' . htmlspecialchars($conn->error, ENT_QUOTES, 'UTF-8');
    $tipoMessaggio = 'danger';
}
?>

<div class="container mt-5">
    <h2>Clienti</h2>

    <!-- Form inserimento (nota: enctype necessario per upload file) -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="clienti.php" method="POST" enctype="multipart/form-data">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="fw-semibold">Nome:</label>
                        <input type="text" name="nome" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-semibold">Cognome:</label>
                        <input type="text" name="cognome" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-semibold">Data di nascita:</label>
                        <input type="date" name="data_nascita" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-semibold">Email:</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-semibold">Telefono:</label>
                        <input type="text" name="telefono" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-semibold">Nazione:</label>
                        <input type="text" name="nazione" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-semibold">Codice Fiscale:</label>
                        <input type="text" name="codice_fiscale" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="fw-semibold">Documento (pdf/jpg/png max 2MB):</label>
                        <input type="file" name="documento" class="form-control" accept=".pdf,image/jpeg,image/png">
                    </div>
                    <div class="col-12">
                        <button class="btn btn-success mt-3" type="submit">Salva</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabella clienti -->
    <table class="table table-striped">
        <thead>
            <tr>
                <th scope="row">ID</th>
                <th scope="row">Nome</th>
                <th scope="row">Cognome</th>
                <th scope="row">Data nascita</th>
                <th scope="row">Email</th>
                <th scope="row">Telefono</th>
                <th scope="row">Nazione</th>
                <th scope="row">Codice Fiscale</th>
                <th scope="row">Documento</th>
                <th scope="row">Azioni</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($row['nome'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($row['cognome'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($row['data_nascita'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($row['telefono'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($row['nazione'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($row['codice_fiscale'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <?php if (!empty($row['documento'])): ?>
                                <a href="<?= htmlspecialchars($row['documento'], ENT_QUOTES, 'UTF-8') ?>" target="_blank">Visualizza</a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="modifica_cliente.php?id=<?= (int)$row['id'] ?>" class="btn btn-sm btn-warning" title="Modifica">✏️</a>
                            <a class="btn btn-sm btn-danger" href="clienti.php?elimina=<?= (int)$row['id'] ?>" onclick="return confirm('Eliminare questo cliente?')">🗑️</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="10" class="text-center">Nessun cliente trovato.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Paginazione -->
    <nav>
        <ul class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                    <a class="page-link" href="clienti.php?page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>

<!-- Modale Bootstrap -->
<?php if (!empty($messaggio)): ?>
    <div class="modal fade" id="messaggioModal" tabindex="-1" aria-labelledby="messaggioModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content bg-white">
                <div class="modal-header">
                    <h5 class="modal-title" id="messaggioModalLabel">Esito operazione</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0 text-<?= $tipoMessaggio === 'success' ? 'success' : 'danger' ?>">
                        <?= htmlspecialchars($messaggio, ENT_QUOTES, 'UTF-8') ?>
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Bootstrap Bundle (necessario per modali) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" defer></script>

<?php if (!empty($messaggio)): ?>
    <script>
        window.addEventListener('DOMContentLoaded', function() {
            const modalEl = document.getElementById('messaggioModal');
            if (modalEl) {
                const modal = new bootstrap.Modal(modalEl);
                modal.show();
                modalEl.addEventListener('hidden.bs.modal', function() {
                    // Ricarica la pagina senza query string (pulita)
                    window.location.href = 'clienti.php';
                });
            }
        });
    </script>
<?php endif; ?>

<?php include 'footer.php'; ?>