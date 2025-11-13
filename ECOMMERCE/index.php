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

    <div class="container">

        <h1>Rubrica contatti</h1>
        <a href="aggiungi_contatto.php" class="btn btn-primary">Aggiungi contatto</a>


        <table>

            <thead>
                <tr>
                    <th>
                        Nome :
                    </th>
                    <th>
                        Telefono :
                    </th>
                    <th>
                        Email :
                    </th>
                    <th>
                        Actions :
                    </th>

                </tr>
            </thead>




            <tbody>
                <!--Ciclo WHILE FINTANTO CHE HO RESULT, MOSTRAMELI IN ROW DEDICATE--->
                <?php while ($row = mysqli_fetch_assoc($result)) :     ?>
                    <tr>
                        <td>
                            <!--HTMLSPECIALCHARS aggiunge alla pagina html parti di codice-->
                            <?= htmlspecialchars($row['nome']) ?> <!--mostra nome-->
                        </td>
                        <td>
                            <?= htmlspecialchars($row['telefono']) ?> <!--mostra telefono-->
                        </td>
                        <td>
                            <?= htmlspecialchars($row['email']) ?> <!--mostra email-->
                        </td>

                        <td class="actions">

                            <a href="modifica_contatto.php?id=<?= $row['id'] ?>">🖊️</a>
                            <a href="elimina_contatto.php?id=<?= $row['id'] ?>">🗑️</a>
                            <a href="ordini.php">📦</a>

                        </td>

                    </tr>

                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>

</html>