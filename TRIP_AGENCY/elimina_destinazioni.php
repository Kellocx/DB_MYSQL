<?php
require 'db.php';
mysqli_set_charset($conn, 'utf8mb4');

$id_citta = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null;
if (!$id_citta) {
    header("Location: destinazioni.php?error=invalid");
    exit;
}

// Recupera dati destinazione per mostrarli nella conferma
$sql = "SELECT citta, paese FROM destinazioni WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_citta);
$stmt->execute();
$stmt->bind_result($citta, $paese);
if (!$stmt->fetch()) {
    $stmt->close();
    header("Location: destinazioni.php?error=notfound");
    exit;
}
$stmt->close();

// Se conferma POST → elimina
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "DELETE FROM destinazioni WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_citta);

    if ($stmt->execute()) {
        $stmt->close();
        header("Location: destinazioni.php?success=eliminata");
        exit;
    } else {
        $stmt->close();
        header("Location: destinazioni.php?error=deletefail");
        exit;
    }
}

include 'header.php';
?>

<div class="container mt-5">
    <h2>Elimina Destinazione</h2>

    <!-- Modale Bootstrap -->
    <div class="modal fade" id="confermaElimina" tabindex="-1" aria-labelledby="confermaEliminaLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content bg-white">
                <div class="modal-header">
                    <h5 class="modal-title" id="confermaEliminaLabel">Conferma eliminazione</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Sei sicuro di voler eliminare la destinazione
                    <strong><?= htmlspecialchars($citta) ?> (<?= htmlspecialchars($paese) ?>)</strong>?
                </div>
                <div class="modal-footer">
                    <form method="post">
                        <button type="submit" class="btn btn-danger">Elimina</button>
                        <a href="destinazioni.php" class="btn btn-secondary">Annulla</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modalEl = document.getElementById('confermaElimina');
        if (modalEl) {
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    });
</script>

<?php include 'footer.php'; ?>