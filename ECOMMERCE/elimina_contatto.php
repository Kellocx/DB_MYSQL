<?php
require 'db.php';

// Verifica che l'ID sia stato passato e sia numerico
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Errore: ID contatto non specificato o non valido.");
}

$id = (int) $_GET['id'];

// Prepara la query di eliminazione
$query = "DELETE FROM contatti WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $id);
    if (mysqli_stmt_execute($stmt)) {
        // Reindirizza con messaggio di successo
        header("Location: index.php?success=eliminato");
        exit();
    } else {
        die("Errore durante l'eliminazione: " . mysqli_stmt_error($stmt));
    }
} else {
    die("Errore nella preparazione della query: " . mysqli_error($conn));
}
