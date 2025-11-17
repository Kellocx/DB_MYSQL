<?php
include 'db.php';
mysqli_set_charset($conn, 'utf8mb4');

// Impaginazione
$perPagina = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $perPagina;

// Messaggi da query string
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
            $tipoMessaggio = "danger";
            break;
        case 'notfound':
            $messaggio = "Cliente non trovato.";
            $tipoMessaggio = "danger";
            break;
        case 'invalid':
            $messaggio = "ID cliente non valido.";
            $tipoMessaggio = "danger";
            break;
    }
}

// Inserimento cliente con redirect per mostrare la modale
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome           = $_POST['nome'] ?? '';
    $cognome        = $_POST['cognome'] ?? '';
    $data_nascita   = $_POST['data_nascita'] ?? '';
    $email          = $_POST['email'] ?? '';
    $telefono       = $_POST['telefono'] ?? '';
    $nazione        = $_POST['nazione'] ?? '';
    $codice_fiscale = $_POST['codice_fiscale'] ?? '';
    // Se vuoi gestire file, usa $_FILES e move_uploaded_file. Per ora lo salviamo come testo:
    $documento      = $_POST['documento'] ?? '';

    $sql = "INSERT INTO clienti (nome, cognome, data_nascita, email, telefono, nazione, codice_fiscale, documento)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssss", $nome, $cognome, $data_nascita, $email, $telefono, $nazione, $codice_fiscale, $documento);

    if ($stmt->execute()) {
        // Redirect per attivare la modale via GET
        header("Location: clienti.php?success=aggiunto");
        exit;
    } else {
        header("Location: clienti.php?error=insertfail");
        exit;
    }
}

// Conteggio totale clienti
$totalRow = $conn->query("SELECT COUNT(*) AS t FROM clienti")->fetch_assoc();
$total = (int)($totalRow['t'] ?? 0);
$totalPages = max(1, (int)ceil($total / $perPagina));

// Recupero clienti per pagina
$result = $conn->query("SELECT * FROM clienti ORDER BY cognome ASC LIMIT $perPagina OFFSET $offset");
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Clienti</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h2>Clienti</h2>

        <!-- Form inserimento -->
        <div class="card mb-4">
            <div class="card-body">
                <form action="" method="POST">
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
                            <label class="fw-semibold">Documento:</label>
                            <input type="text" name="documento" class="form-control" placeholder="Es. tipo/numero documento">
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
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Cognome</th>
                    <th>Data nascita</th>
                    <th>Email</th>
                    <th>Telefono</th>
                    <th>Nazione</th>
                    <th>Codice Fiscale</th>
                    <th>Documento</th>
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td><?= htmlspecialchars($row['nome']) ?></td>
                        <td><?= htmlspecialchars($row['cognome']) ?></td>
                        <td><?= htmlspecialchars($row['data_nascita']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['telefono']) ?></td>
                        <td><?= htmlspecialchars($row['nazione']) ?></td>
                        <td><?= htmlspecialchars($row['codice_fiscale']) ?></td>
                        <td><?= htmlspecialchars($row['documento']) ?></td>
                        <td>
                            <a href="modifica_cliente.php?id=<?= (int)$row['id'] ?>" class="btn btn-sm btn-warning">✏️</a>
                            <a href="elimina_cliente.php?id=<?= (int)$row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Eliminare questo cliente?')">🗑️</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Paginazione -->
        <nav>
            <ul class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
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
                            <?= htmlspecialchars($messaggio) ?>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <?php if (!empty($messaggio)): ?>
        <script>
            // Mostra la modale dopo che il DOM e Bootstrap sono pronti
            window.addEventListener('DOMContentLoaded', function() {
                const modalEl = document.getElementById('messaggioModal');
                if (modalEl) {
                    const modal = new bootstrap.Modal(modalEl);
                    modal.show();
                }
            });
        </script>
        <?php if (!empty($messaggio)): ?>
            <script>
                window.addEventListener('DOMContentLoaded', function() {
                    const modalEl = document.getElementById('messaggioModal');
                    if (modalEl) {
                        modalEl.addEventListener('hidden.bs.modal', () => {
                            // Rimuove query string e ricarica la pagina pulita
                            window.location.href = 'clienti.php';
                        });
                    }
                });
            </script>
        <?php endif; ?>

    <?php endif; ?>
</body>

</html>