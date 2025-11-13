<?php
require 'db.php';

$errore = null;
$contatto = null;

// Verifica che l'ID sia stato passato correttamente
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Errore: ID contatto non specificato o non valido.");
}

$id = (int) $_GET['id'];

// Se il form è stato inviato
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $telefono = trim($_POST['telefono']);
    $email = trim($_POST['email']);

    if ($nome && $telefono && $email) {
        $updateQuery = "UPDATE contatti SET nome = ?, telefono = ?, email = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $updateQuery);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sssi", $nome, $telefono, $email, $id);
            if (mysqli_stmt_execute($stmt)) {
                header("Location: index.php?success=modifica");
                exit();
            } else {
                $errore = "Errore durante l'aggiornamento: " . mysqli_stmt_error($stmt);
            }
        } else {
            $errore = "Errore nella preparazione della query: " . mysqli_error($conn);
        }
    } else {
        $errore = "Tutti i campi sono obbligatori.";
    }
}

// Recupera i dati del contatto da modificare (solo se GET o errore POST)
if (!$contatto) {
    $selectQuery = "SELECT * FROM contatti WHERE id = ?";
    $stmt = mysqli_prepare($conn, $selectQuery);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $contatto = mysqli_fetch_assoc($result);
        if (!$contatto) {
            die("Contatto non trovato.");
        }
    } else {
        die("Errore nella preparazione della query: " . mysqli_error($conn));
    }
}
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Modifica Contatto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h1>Modifica Contatto</h1>

        <?php if ($errore): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($errore) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="nome" class="form-label">Nome</label>
                <input type="text" class="form-control" id="nome" name="nome" value="<?= htmlspecialchars($contatto['nome']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="telefono" class="form-label">Telefono</label>
                <input type="text" class="form-control" id="telefono" name="telefono" value="<?= htmlspecialchars($contatto['telefono']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($contatto['email']) ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Salva Modifiche</button>
            <a href="index.php" class="btn btn-secondary">Annulla</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>