<?php
require 'db.php';
mysqli_set_charset($conn, 'utf8mb4');

$id_prenotazione = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null;
if (!$id_prenotazione) {
    header("Location: prenotazioni.php?error=invalid");
    exit;
}

// Recupera dati prenotazione per conferma
$sql = "SELECT p.id, c.nome, c.cognome, d.citta 
        FROM prenotazioni p
        JOIN clienti c ON p.id_cliente = c.id
        JOIN destinazioni d ON p.id_destinazione = d.id
        WHERE p.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_prenotazione);
$stmt->execute();
$stmt->bind_result($id, $nome, $cognome, $citta);
if (!$stmt->fetch()) {
    $stmt->close();
    header("Location: prenotazioni.php?error=notfound");
    exit;
}
$stmt->close();

// Se conferma POST → elimina
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "DELETE FROM prenotazioni WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_prenotazione);

    if ($stmt->execute()) {
        header("Location: prenotazioni.php?success=eliminato");
        exit;
    } else {
        header("Location: prenotazioni.php?error=deletefail");
        exit;
    }
    $stmt->close();
}

include 'header.php';
?>

<div class="container mt-5">
    <h2>Elimina Prenotazione</h2>

    <!-- Modale Bootstrap -->
    <div class="modal fade show" id="confermaElimina" tabindex="-1" aria-labelledby="confermaEliminaLabel" aria-modal="true" style="display:block;">
        <div class="modal-dialog">
            <div class="modal-content bg-white">
                <div class="modal-header">
                    <h5 class="modal-title" id="confermaEliminaLabel">Conferma eliminazione</h5>
                </div>
                <div class="modal-body">
                    Sei sicuro di voler eliminare la prenotazione di
                    <strong><?= htmlspecialchars($cognome . " " . $nome) ?></strong> per <strong><?= htmlspecialchars($citta) ?></strong>?
                </div>
                <div class="modal-footer">
                    <form method="post">
                        <button type="submit" class="btn btn-danger">Elimina</button>
                        <a href="prenotazioni.php" class="btn btn-secondary">Annulla</a>
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