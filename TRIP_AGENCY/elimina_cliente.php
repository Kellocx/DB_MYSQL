<?php
require 'db.php';
mysqli_set_charset($conn, 'utf8mb4');

// Validazione ID cliente
$cliente_id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null;
if (!$cliente_id) {
    // Se l'ID non è valido → torna a index con messaggio di errore
    header("Location: index.php?error=invalid");
    exit;
}

// Controllo esistenza cliente
$sql = "SELECT id FROM clienti WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $cliente_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $stmt->close();
    // Se non trovato → torna a index con messaggio
    header("Location: index.php?error=notfound");
    exit;
}
$stmt->close();

// Eliminazione cliente
$sql = "DELETE FROM clienti WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $cliente_id);

if ($stmt->execute()) {
    // Redirect immediato alla pagina index.php con messaggio di conferma
    header("Location: clienti.php?success=eliminato");
    exit;
} else {
    // Se errore nell'eliminazione
    header("Location: index.php?error=deletefail");
    exit;
}

$stmt->close();
$conn->close();
