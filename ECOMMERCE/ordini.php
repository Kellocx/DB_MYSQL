<?php
require 'db.php';

// Validazione ID
$contatto_id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null;
if (!$contatto_id) {
    die("ID contatto non valido.");
}

// Recupera il nome del contatto
$nome_contatto = null;
$sql_contatto = "SELECT nome FROM contatti WHERE id = ?";
$stmt_contatto = mysqli_prepare($conn, $sql_contatto);
mysqli_stmt_bind_param($stmt_contatto, "i", $contatto_id);
mysqli_stmt_execute($stmt_contatto);
mysqli_stmt_bind_result($stmt_contatto, $nome_contatto);
mysqli_stmt_fetch($stmt_contatto);
mysqli_stmt_close($stmt_contatto);

if (!$nome_contatto) {
    die("Contatto non trovato.");
}

// Recupera gli ordini associati al contatto
$ordini = [];
$sql = "SELECT id, prodotto, quantita, data_di_ordine FROM ordini WHERE contatto_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $contatto_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $ordine_id, $prodotto, $quantita, $data_di_ordine);

while (mysqli_stmt_fetch($stmt)) {
    $ordini[] = [
        'id' => $ordine_id,
        'prodotto' => $prodotto,
        'quantita' => $quantita,
        'data_di_ordine' => $data_di_ordine
    ];
}
mysqli_stmt_close($stmt);

// Messaggio di conferma
$messaggio = '';
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'aggiunto') {
        $messaggio = 'Ordine aggiunto con successo!';
    } elseif ($_GET['success'] === 'eliminato') {
        $messaggio = 'Ordine eliminato.';
    } elseif ($_GET['success'] === 'modificato') {
        $messaggio = 'Ordine modificato con successo.';
    }
}
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Ordini di <?= htmlspecialchars($nome_contatto) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h2>Ordini di <?= htmlspecialchars($nome_contatto) ?></h2>

        <!-- Pulsante per aggiungere nuovo ordine -->
        <a href="aggiungi_ordine.php?id=<?= $contatto_id ?>" class="btn btn-success mb-3">➕ Aggiungi nuovo ordine</a>

        <!-- Modale di conferma -->
        <?php if ($messaggio): ?>
            <div class="modal fade" id="messaggioModal" tabindex="-1" aria-labelledby="messaggioModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content bg-white">
                        <div class="modal-header">
                            <h5 class="modal-title" id="messaggioModalLabel">Operazione completata</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                        </div>
                        <div class="modal-body">
                            <?= htmlspecialchars($messaggio) ?>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Tabella ordini -->
        <?php if (count($ordini) > 0): ?>
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Prodotto</th>
                        <th>Quantità</th>
                        <th>Data</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ordini as $ordine): ?>
                        <tr>
                            <td><?= htmlspecialchars($ordine['prodotto']) ?></td>
                            <td><?= htmlspecialchars($ordine['quantita']) ?></td>
                            <td><?= htmlspecialchars($ordine['data_di_ordine']) ?></td>
                            <td>
                                <a href="modifica_ordine.php?id=<?= $ordine['id'] ?>&contatto=<?= $contatto_id ?>" class="btn btn-sm btn-outline-warning" title="modifica ordine">✏️</a>
                                <a href="elimina_ordine.php?id=<?= $ordine['id'] ?>&contatto=<?= $contatto_id ?>" class="btn btn-sm btn-outline-danger" title="elimina ordine" onclick="return confirm('Sei sicuro di voler eliminare questo ordine?')">🗑️</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Nessun ordine registrato per questo contatto.</p>
        <?php endif; ?>

        <a href="index.php" class="btn btn-secondary mt-3">Torna alla rubrica</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <?php if ($messaggio): ?>
        <script>
            const modal = new bootstrap.Modal(document.getElementById('messaggioModal'));
            modal.show();
        </script>
    <?php endif; ?>
</body>

</html>