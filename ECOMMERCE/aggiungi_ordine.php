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








</body>

</html>