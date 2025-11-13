<?php
//importo il file db
require 'db.php';

//salvo in una variabile $result, i risultati della query
$result = mysqli_query($conn, "SELECT * FROM contatti"); // query per prendermi tutta la tabella contatti
?>


<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ecommerce</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css?v<?= time() ?>">
</head>

<body>
    <!-- Messaggio contatto aggiunto con successo -->
    <?php if (isset($_GET['success'])): ?>
        <?php if ($_GET['success'] === 'aggiunto'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                ✅ Contatto aggiunto con successo!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Chiudi"></button>
            </div>
            <!-- Messaggio contatto aeliminato con successo -->
        <?php elseif ($_GET['success'] === 'eliminato'): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                🗑️ Contatto eliminato.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Chiudi"></button>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="container">

        <h1>Rubrica contatti</h1>



        <table class=" table">

            <thead>
                <tr>
                    <th>
                        Nome
                    </th>
                    <th>
                        Telefono
                    </th>
                    <th>
                        Email
                    </th>
                    <th>
                        Actions
                    </th>

                </tr>
            </thead>




            <tbody>
                <!--Ciclo WHILE FINTANTO CHE HO RESULT, MOSTRAMELI IN ROW DEDICATE--->
                <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                    <tr>
                        <td><?= htmlspecialchars($row['nome']) ?></td>
                        <td><?= htmlspecialchars($row['telefono']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td class="actions">
                            <a href="modifica_contatto.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning" title="modifica contatto">🖊️</a>

                            <!-- Bottone che apre il modale -->
                            <button type="button" class="btn btn-sm btn-outline-danger" title="elimina contatto" data-bs-toggle="modal" data-bs-target="#confermaElimina<?= $row['id'] ?> ">
                                🗑️
                            </button>

                            <a href="ordini.php" class="btn btn-sm btn-info" title="aggiungi ordini">📦</a>
                        </td>
                    </tr>

                    <!-- Modale di conferma eliminazione -->
                    <div class="modal fade" id="confermaElimina<?= $row['id'] ?>" tabindex="-1" aria-labelledby="modalLabel<?= $row['id'] ?>" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-warning">
                                    <h5 class="modal-title" id="modalLabel<?= $row['id'] ?>">Conferma eliminazione</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                                </div>
                                <div class="modal-body">
                                    Sei sicuro di voler eliminare <strong><?= htmlspecialchars($row['nome']) ?></strong>?<br>

                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                                    <a href="elimina_contatto.php?id=<?= $row['id'] ?>" class="btn btn-danger">Conferma eliminazione</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </tbody>

        </table>
        <a href="aggiungi_contatto.php" class="btn btn-primary">Aggiungi contatto</a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>

</html>