<?php


require 'db.php';


//prendo l ID del contatto a cui legare l ordine
$contatto_id = $_GET['id'];


if ($_SERVER["REQUEST_METHOD"] == "POST") {


    //RECUPERO I DATI DAL FORM DI INSERIMENTO DEL ORDINE
    $prodotto = $_POST['prodotto'];
    $quantita = $_POST['quantita'];
    $data = $_POST['data'];

    //query
    $sql = "INSERT INTO ordini (contatto_id, prodotto, quantita, data_di_ordine)
                    VALUES ('$contatto_id', '$prodotto', '$quantita', '$data')";


    //eseguo la query
    mysqli_query($conn, $sql);

    //reindirizzo
    header("Location: ordini.php?id=$contatto_id");
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aggiungi ordine</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css?v<?= time() ?>">

</head>

<body>

    <div class="container">

        <h2>Aggiungi ordine</h2>


        <form action="" method="POST">


            Prodotto : <input name="prodotto" type="text" required>

            Quantità : <input name="quantita" type="text" required>

            Data di Ordine : <input name="data" type="text" required>


            <button type="submit">Aggiungi Ordine</button>




        </form>


        <a href="ordini.php?id=<?= $contatto_id ?>" class="button">Torna agli ordini</a>



    </div>






    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>

</body>

</html>