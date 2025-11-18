<?php
require 'db.php';
mysqli_set_charset($conn, 'utf8mb4');

// Recupera ID destinazione
$id_citta = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null;
if (!$id_citta) {
    header("Location: destinazioni.php?error=invalid");
    exit;
}

// Elimina destinazione
$sql = "DELETE FROM destinazioni WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_citta);

if ($stmt->execute()) {
    $stmt->close();
    header("Location: destinazioni.php?success=eliminata");
    exit;
} else {
    $stmt->close();
    header("Location: destinazioni.php?error=deletefail");
    exit;
}
