<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Servicios | Carlumbre</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <?php view('partials/menu'); ?>

    <div class="container mt-5">

        <h1 class="mb-4">Servicios del Taller</h1>

        <div class="d-flex justify-content-between mb-3">
            <form method="GET" action="/services" class="d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="Buscar servicio..."
                    value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                <button class="btn btn-primary">Buscar</button>
            </form>

            <a href="/services/create" class="btn btn-success">
                + Nuevo Servicio
            </a>
        </div>

        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Servicio</th>
                    <th>Descripción</th>
                    <th>Precio Base</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($services)): ?>
                    <?php foreach ($services as $service): ?>
                        <tr>
                            <td><?= $service['id'] ?></td>
                            <td><?= htmlspecialchars($service['name']) ?></td>
                            <td><?= htmlspecialchars($service['description']) ?></td>
                            <td>S/ <?= number_format($service['price'], 2) ?></td>
                            <td>
                                <?php if ($service['status']): ?>
                                    <span class="badge bg-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="/services/edit?id=<?= $service['id'] ?>" class="btn btn-sm btn-warning">Editar</a>

                                <a class="btn btn-danger btn-sm btn-delete" onclick="deleteService('<?= $service['id'] ?>')">
                                    Eliminar
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">
                            No hay servicios registrados.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

    </div>

</body>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function deleteService(id) {
        const url = `/services/delete?id=${id}`;

        Swal.fire({
            title: '¿Estás seguro de eliminar este servicio?',
            text: "¡No podrás revertir esta acción!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    };

    <?php if (isset($_SESSION['success'])): ?>
        Swal.fire({
            icon: 'success',
            title: '¡Éxito!',
            text: '<?= $_SESSION['success'] ?>'
        });
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
</script>

</html>