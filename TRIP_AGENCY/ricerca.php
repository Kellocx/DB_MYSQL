<?php
require 'db.php';
require 'header.php';
mysqli_set_charset($conn, 'utf8mb4');

// Recupera lista clienti
$clienti = [];
$sql_clienti = "SELECT id, cognome, nome FROM clienti ORDER BY cognome ASC";
$res_clienti = mysqli_query($conn, $sql_clienti);
while ($row = mysqli_fetch_assoc($res_clienti)) {
    $clienti[] = $row;
}
mysqli_free_result($res_clienti);

// Recupera lista destinazioni
$destinazioni = [];
$sql_dest = "SELECT id, citta FROM destinazioni ORDER BY citta ASC";
$res_dest = mysqli_query($conn, $sql_dest);
while ($row = mysqli_fetch_assoc($res_dest)) {
    $destinazioni[] = $row;
}
mysqli_free_result($res_dest);

// Costruzione query di ricerca
$where = [];
$params = [];
$types  = '';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!empty($_GET['id_cliente'])) {
        $where[] = "p.id_cliente = ?";
        $params[] = (int)$_GET['id_cliente'];
        $types   .= 'i';
    }
    if (!empty($_GET['id_destinazione'])) {
        $where[] = "p.id_destinazione = ?";
        $params[] = (int)$_GET['id_destinazione'];
        $types   .= 'i';
    }
    if (!empty($_GET['dataprenotazione'])) {
        $where[] = "p.dataprenotazione = ?";
        $params[] = $_GET['dataprenotazione'];
        $types   .= 's';
    }
    if (!empty($_GET['search_text'])) {
        $where[] = "(c.cognome LIKE ? OR c.nome LIKE ? OR d.citta LIKE ?)";
        $search = "%" . $_GET['search_text'] . "%";
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
        $types   .= 'sss';
    }
    if (!empty($_GET['acconto_min'])) {
        $where[] = "p.acconto >= ?";
        $params[] = (float)$_GET['acconto_min'];
        $types   .= 'd';
    }
    if (!empty($_GET['acconto_max'])) {
        $where[] = "p.acconto <= ?";
        $params[] = (float)$_GET['acconto_max'];
        $types   .= 'd';
    }
    if (isset($_GET['assicurazione']) && $_GET['assicurazione'] !== '') {
        $where[] = "p.assicurazione = ?";
        $params[] = (int)$_GET['assicurazione'];
        $types   .= 'i';
    }
}

$sql = "SELECT p.id, c.nome, c.cognome, d.citta, p.dataprenotazione, p.acconto, p.numero_persone, p.assicurazione
        FROM prenotazioni p
        JOIN clienti c ON p.id_cliente = c.id
        JOIN destinazioni d ON p.id_destinazione = d.id";

if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY p.dataprenotazione DESC";

$stmt = $conn->prepare($sql);
if ($where) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$res = $stmt->get_result();

$prenotazioni = [];
while ($row = $res->fetch_assoc()) {
    $prenotazioni[] = $row;
}
$stmt->close();
?>

<div class="container mt-5">
    <h2>Ricerca Prenotazioni</h2>

    <!-- Form ricerca -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="get">
                <div class="mb-3">
                    <label class="form-label">Cliente</label>
                    <select name="id_cliente" class="form-select">
                        <option value="">-- Tutti i clienti --</option>
                        <?php foreach ($clienti as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= isset($_GET['id_cliente']) && $_GET['id_cliente'] == $c['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['cognome'] . " " . $c['nome'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Destinazione</label>
                    <select name="id_destinazione" class="form-select">
                        <option value="">-- Tutte le destinazioni --</option>
                        <?php foreach ($destinazioni as $d): ?>
                            <option value="<?= $d['id'] ?>" <?= isset($_GET['id_destinazione']) && $_GET['id_destinazione'] == $d['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($d['citta'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Data Prenotazione</label>
                    <input type="date" name="dataprenotazione" class="form-control" value="<?= $_GET['dataprenotazione'] ?? '' ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Ricerca libera (cliente/destinazione)</label>
                    <input type="text" name="search_text" class="form-control" value="<?= $_GET['search_text'] ?? '' ?>" placeholder="Inserisci cognome, nome o città">
                </div>
                <div class="mb-3">
                    <label class="form-label">Acconto minimo (€)</label>
                    <input type="number" step="0.01" name="acconto_min" class="form-control" value="<?= $_GET['acconto_min'] ?? '' ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Acconto massimo (€)</label>
                    <input type="number" step="0.01" name="acconto_max" class="form-control" value="<?= $_GET['acconto_max'] ?? '' ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Assicurazione</label>
                    <select name="assicurazione" class="form-select">
                        <option value="">-- Tutte --</option>
                        <option value="1" <?= isset($_GET['assicurazione']) && $_GET['assicurazione'] == '1' ? 'selected' : '' ?>>Sì</option>
                        <option value="0" <?= isset($_GET['assicurazione']) && $_GET['assicurazione'] == '0' ? 'selected' : '' ?>>No</option>
                    </select>
                </div>
                <div class="mb-3">
                    <button type="submit" class="btn btn-primary">Cerca</button>
                    <a href="ricerche.php" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Risultati -->
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Destinazione</th>
                <th>Data</th>
                <th>Acconto (€)</th>
                <th>Persone</th>
                <th>Assicurazione</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($prenotazioni): ?>
                <?php foreach ($prenotazioni as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['id']) ?></td>
                        <td><?= htmlspecialchars($p['cognome'] . " " . $p['nome']) ?></td>
                        <td><?= htmlspecialchars($p['citta']) ?></td>
                        <td><?= htmlspecialchars($p['dataprenotazione']) ?></td>
                        <td><?= htmlspecialchars($p['acconto']) ?></td>
                        <td><?= htmlspecialchars($p['numero_persone']) ?></td>
                        <td><?= $p['assicurazione'] ? 'Sì' : 'No' ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center">Nessuna prenotazione trovata</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>