<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Agregar Servicio | Carlumbre</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <?php view('partials/menu'); ?>

    <div class="container mt-5">
        <h1>Agregar Servicio</h1>

        <form action="/services/store" method="POST">

            <div class="mb-3">
                <label class="form-label">Nombre del Servicio *</label>
                <input type="text" name="name" class="form-control" required placeholder="Ej: Cambio de aceite">
            </div>

            <div class="mb-3">
                <label class="form-label">Descripción</label>
                <textarea name="description" class="form-control" rows="3"
                    placeholder="Detalle del servicio..."></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Precio Base *</label>
                <div class="input-group">
                    <span class="input-group-text">S/</span>
                    <input type="number" name="price" step="0.01" min="0" class="form-control" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Estado</label>
                <select name="status" class="form-select">
                    <option value="1" selected>Activo</option>
                    <option value="0">Inactivo</option>
                </select>
            </div>

            <button class="btn btn-success">Guardar Servicio</button>
            <a href="/services" class="btn btn-secondary">Volver</a>

        </form>

    </div>

</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php view('partials/sweetalert'); ?>

</html>