<?php
include 'db.php';
mysqli_set_charset($conn, 'utf8mb4');

$messaggio = '';
$tipoMessaggio = '';

$cliente_id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null;
if (!$cliente_id) {
    die("ID cliente non valido.");
}

// Recupera dati cliente
$sql = "SELECT nome, cognome, data_nascita, email, telefono, nazione, codice_fiscale, documento 
        FROM clienti WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $cliente_id);
$stmt->execute();
$stmt->bind_result($nome, $cognome, $data_nascita, $email, $telefono, $nazione, $codice_fiscale, $documento);
$stmt->fetch();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome           = $_POST['nome'] ?? '';
    $cognome        = $_POST['cognome'] ?? '';
    $data_nascita   = $_POST['data_nascita'] ?? '';
    $email          = $_POST['email'] ?? '';
    $telefono       = $_POST['telefono'] ?? '';
    $nazione        = $_POST['nazione'] ?? '';
    $codice_fiscale = $_POST['codice_fiscale'] ?? '';
    $documento      = $_POST['documento'] ?? '';

    $sql = "UPDATE clienti 
            SET nome=?, cognome=?, data_nascita=?, email=?, telefono=?, nazione=?, codice_fiscale=?, documento=? 
            WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssi", $nome, $cognome, $data_nascita, $email, $telefono, $nazione, $codice_fiscale, $documento, $cliente_id);

    if ($stmt->execute()) {
        $messaggio = "Cliente modificato con successo!";
        $tipoMessaggio = "success";
    } else {
        $messaggio = "Errore: " . htmlspecialchars($stmt->error, ENT_QUOTES, 'UTF-8');
        $tipoMessaggio = "danger";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Modifica Cliente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h2>Modifica Cliente</h2>
        <form method="post">
            <div class="mb-3"><label>Nome</label><input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($nome) ?>" required></div>
            <div class="mb-3"><label>Cognome</label><input type="text" name="cognome" class="form-control" value="<?= htmlspecialchars($cognome) ?>" required></div>
            <div class="mb-3"><label>Data di nascita</label><input type="date" name="data_nascita" class="form-control" value="<?= htmlspecialchars($data_nascita) ?>" required></div>
            <div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>" required></div>
            <div class="mb-3"><label>Telefono</label><input type="text" name="telefono" class="form-control" value="<?= htmlspecialchars($telefono) ?>" required></div>
            <div class="mb-3"><label>Nazione</label><input type="text" name="nazione" class="form-control" value="<?= htmlspecialchars($nazione) ?>" required></div>
            <div class="mb-3"><label>Codice Fiscale</label><input type="text" name="codice_fiscale" class="form-control" value="<?= htmlspecialchars($codice_fiscale) ?>" required></div>
            <div class="mb-3"><label>Documento</label><input type="text" name="documento" class="form-control" value="<?= htmlspecialchars($documento) ?>"></div>
            <button type="submit" class="btn btn-warning">Aggiorna</button>
            <a href="index.php" class="btn btn-secondary">Annulla</a>
        </form>
    </div>

    <!-- Modale -->
    <?php if ($messaggio): ?>
        <div class="modal fade" id="messaggioModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content bg-white">
                    <div class="modal-header">
                        <h5 class="modal-title">Esito operazione</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-<?= $tipoMessaggio === 'success' ? 'success' : 'danger' ?>">
                        <?= htmlspecialchars($messaggio) ?>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button></div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <?php if ($messaggio): ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                new bootstrap.Modal(document.getElementById('messaggioModal')).show();
            });
        </script>
    <?php endif; ?>
</body>

</html>