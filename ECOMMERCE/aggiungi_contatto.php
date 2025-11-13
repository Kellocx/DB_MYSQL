<?php

require 'db.php';

//se il form è stato inviato tramite il metodo POST

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nome = $_POST['nome'];
    $telefono = $_POST['telefono'];
    $email = $_POST['email'];

    //query
    $sql = "INSERT INTO contatti( nome, telefono, email ) VALUES('$nome','$telefono','$email')";


    //eseguo la query
    mysqli_query($conn, $sql);

    //rendirizzamento utente alla index post inserimento
    header("Location: index.php?success=aggiunto");
}
?>



<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aggiungi Contatto</title>
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v<?= time() ?>">
</head>

<body>


    <div class="container">

        <h1>Aggiungi contatto</h1>

        <form action="" method="POST">


            Nome : <input name="nome" type="text" required>

            Telefono : <input name="telefono" type="text" required>

            Email : <input name="email" type="text" required>

            <button type="submit">Salva</button>


        </form>

        <a href="index.php" class="button">Torna alla lista</a>

    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</body>

</html>