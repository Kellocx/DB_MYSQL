<?php include 'header.php'; ?>
<?php include 'db.php'; ?>

<h2>Clienti</h2>

<!--Form-->
<div class="card mb-4">

    <div class="card-body">

        <form action="" method="POST">

            <div class="row g-3">

                <div class="col-md-6">
                    <label style="font-weight: 600;" for="">Nome : </label>
                    <input type="text" name="nome" class="form-control" placeholder="Inserisci il nome del cliente..." required>
                </div>

                <div class="col-md-6">
                    <label style="font-weight: 600;" for="">Cognome : </label>
                    <input type="text" name="cognome" class="form-control" placeholder="Inserisci il cognome del cliente..." required>
                </div>

                <div class="col-md-6">
                    <label style="font-weight: 600;" for="">Email : </label>
                    <input type="text" name="email" class="form-control" placeholder="Inserisci l email del cliente..." required>
                </div>

                <div class="col-md-6">
                    <label style="font-weight: 600;" for="">Telefono : </label>
                    <input type="text" name="telefono" class="form-control" placeholder="Inserisci il telefono del cliente..." required>
                </div>

                <div class="col-md-6">
                    <label style="font-weight: 600;" for="">Nazione : </label>
                    <input type="text" name="nazione" class="form-control" placeholder="Inserisci la nazione del cliente..." required>
                </div>

                <div class="col-md-6">
                    <label style="font-weight: 600;" for="">Codice Fiscale : </label>
                    <input type="text" name="codice_fiscale" class="form-control" placeholder="Inserisci il codice fiscale del cliente..." required>
                </div>

                <div class="col-md-6">
                    <label style="font-weight: 600;" for="">Documento : </label>
                    <input type="file" name="documento" class="form-control" placeholder="Inserisci il codice del documento del cliente..." required>
                </div>

                <div class="col-12">
                    <button class="btn btn-success" type="submit">Salva</button>
                </div>

            </div>
        </form>
    </div>
</div>


<!--Tabella-->
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


<?php include 'footer.php'; ?>