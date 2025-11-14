<?php include 'header.php' ?>

<div class="text-center py-4">
    <h1 class="disply-5">Benvenuta nella nostra Trip Agency</h1>
    <p class="lead">Esplora le nostre destinazioni, gestisci i clienti emonitora le prenotazioni</p>
</div>




<div class="d-flex justify-content-center align-items-center mb-5 flex-wrap gap-4">
    <img src="img2.png" alt="" class="img-fluid" style="max-height: 300px;">
    <img src="img4.png" alt="" class="img-fluid" style="max-height: 300px;">
</div>

<!--sezione card-->
<div class="row justify-content-center mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <h5 class="card-title">Clienti</h5>
                <p class="card-text">Gestisci le informazioni dei tuoi clienti</p>
                <a href="clienti.php" class="btn btn-primary">Clienti</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <h5 class="card-title">Destinazioni</h5>
                <p class="card-text">Esplora le nostre nuove mete turistiche</p>
                <a href="destinazioni.php" class="btn btn-warning">Destinazioni</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <h5 class="card-title">Prenotazioni</h5>
                <p class="card-text">Visualiza e registra le prenotazioni dei tuoi clienti</p>
                <a href="prenotazioni.php" class="btn btn-success">prenotazioni</a>
            </div>
        </div>
    </div>
</div>


<?php include 'footer.php' ?>