<?php
require 'db.php';

// Messaggio di conferma
$messaggio = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recupera i dati dal form
    $citta = isset($_POST['citta']) ? $_POST['citta'] : '';
    $paese = isset($_POST['paese']) ? $_POST['paese'] : '';
    $prezzo = isset($_POST['prezzo']) ? (float)$_POST['prezzo'] : 0;
    $data_partenza = isset($_POST['data_partenza']) ? $_POST['data_partenza'] : '';
    $data_ritorno = isset($_POST['data_ritorno']) ? $_POST['data_ritorno'] : '';

    // Validazione dei dati
    if (empty($citta) || empty($paese) || $prezzo <= 0 || empty($data_partenza) || empty($data_ritorno)) {
        $messaggio = 'Tutti i campi devono essere compilati correttamente.';
    } else {
        // Inserisci i dati nel database
        $sql = "INSERT INTO destinazioni (citta, paese, prezzo, data_partenza, data_ritorno) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssiss", $citta, $paese, $prezzo, $data_partenza, $data_ritorno);

        if (mysqli_stmt_execute($stmt)) {
            $messaggio = 'Destinazione aggiunta con successo!';
        } else {
            $messaggio = 'Errore durante l\'inserimento della destinazione: ' . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<?php include 'header.php'; ?>
<?php include 'db.php'; ?>

<body>

    <div class="container mt-5">
        <h2>Aggiungi una nuova destinazione</h2>

        <!-- Messaggio di conferma -->
        <?php if ($messaggio): ?>
            <div class="alert alert-info"><?= htmlspecialchars($messaggio) ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="mb-3">
                <label for="citta" class="form-label">Città</label>
                <input type="text" class="form-control" id="citta" name="citta" placeholder="" required>
            </div>
            <div class="mb-3">
                <label for="paese" class="form-label">Paese</label>
                <input type="text" class="form-control" id="paese" name="paese" placeholder="" required>
            </div>
            <div class="mb-3">
                <label for="prezzo" class="form-label">Prezzo</label>
                <input type="number" class="form-control" id="prezzo" name="prezzo" step="0.01" placeholder="" required>
            </div>
            <div class="mb-3">
                <label for="data_partenza" class="form-label">Data di Partenza</label>
                <input type="date" class="form-control" id="data_partenza" name="data_partenza" placeholder="" required>
            </div>
            <div class="mb-3">
                <label for="data_ritorno" class="form-label">Data di Ritorno</label>
                <input type="date" class="form-control" id="data_ritorno" name="data_ritorno" placeholder="" required>
            </div>
            <button type="submit" class="btn btn-primary">Salva</button>
        </form>

        <a href="index.php" class="btn btn-secondary mt-3">Torna alla home</a>
    </div>
    <table class="table table-striped">
        <thead>

            <tr>

                <th>ID</th>
                <th>Nome</th>
                <th>Cognome</th>
                <th>Email</th>
                <th>Telefono</th>
                <th>Nazione</th>
                <th>Codice Fiscale</th>
                <th>Documento</th>
                <th>Azioni</th>

            </tr>

        </thead>

        <tbody>


        </tbody>

    </table>
    </div>
    <?php include 'footer.php'; ?>