<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Mi Perfil | Carlumbre</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <?php view('partials/menu'); ?>

    <div class="container mt-4">
        <h1>Mi perfil</h1>

        <div class="card mt-3">
            <div class="card-body">
                <h5 class="card-title">Información de usuario</h5>
                <p class="mb-1"><strong>Nombre:</strong> <?= htmlspecialchars($user['name'] ?? '') ?></p>
                <p class="mb-0"><strong>Correo:</strong> <?= htmlspecialchars($user['email'] ?? '') ?></p>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                <h5 class="card-title">Actualizar contraseña</h5>

                <form method="POST" action="/profile/update-password" class="mt-3">
                    <div class="mb-3">
                        <label class="form-label">Contraseña actual</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nueva contraseña</label>
                        <input type="password" name="new_password" class="form-control" minlength="8" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Confirmar nueva contraseña</label>
                        <input type="password" name="confirm_password" class="form-control" minlength="8" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Actualizar contraseña</button>
                </form>
            </div>
        </div>
    </div>
</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php view('partials/sweetalert'); ?>

</html>
