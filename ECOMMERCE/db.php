<?php

//CONNESSIONE AL DB MYSQL usando MYSQLI

//parametri di connessione al database

$host = "localhost";  //host
$user = "root";       //utente standard di default -> root
$password = "";       // non abbiamo inserito nessuna password ( la chiede durante la installazione di XAMPP )
$database = "ecommerce"; //nome db su phpmyadmin

//creo la connessione
$conn = mysqli_connect($host, $user, $password, $database);

//verifico che la connessione funzioni 

if (!$conn) {

    //se la connessione fallisce  stampa un messaggi di errore e termina lo script
    die("Connessione fallita: " . mysqli_connect());
}
