<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Agregar Cliente | Carlumbre</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <?php view('partials/menu'); ?>

    <div class="container mt-5">
        <h1>Agregar Cliente</h1>

        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error'];
                                            unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <form method="POST" action="/clients/store">
            <div class="mb-3">
                <label>Nombre</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Correo</label>
                <input type="email" name="email" class="form-control">
            </div>
            <div class="mb-3">
                <label>Tipo de documento</label>
                <select name="document_type" class="form-control" required>
                    <option value="">Seleccione</option>
                    <option value="1">DNI</option>
                    <option value="2">RUC</option>
                    <option value="3">Pasaporte</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Número de documento</label>
                <input type="text" name="document_number" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Teléfono</label>
                <input type="text" name="phone" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Direccion</label>
                <input type="text" name="address" class="form-control">
            </div>
            <button class="btn btn-success">Guardar Cliente</button>
            <a href="/clients" class="btn btn-secondary">Volver</a>
        </form>
    </div>
</body>

</html>