<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Clientes | Carlumbre</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        tr.clickable:hover {
            cursor: pointer;
            background-color: #f8f9fa;
        }
    </style>
</head>

<body>
    <?php view('partials/menu'); ?>

    <div class="container mt-5">
        <h1 class="mb-4">Clientes</h1>

        <!-- Buscador -->
        <form method="GET" action="/clients" class="mb-3 row g-2">
            <div class="col-md-6">
                <input type="text" name="search" class="form-control" placeholder="Buscar por nombre, email o teléfono"
                    value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100">Buscar</button>
            </div>
        </form>

        <a href="/clients/create" class="btn btn-success mb-3">Agregar Cliente</a>

        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Tipo de Documento</th>
                    <th>Número de Documento</th>
                    <th>Teléfono</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($clients)): ?>
                    <?php foreach ($clients as $client): ?>
                        <tr class="clickable">
                            <td onclick="window.location='/clients/cars?id=<?= $client['id'] ?>'"><?= $client['id'] ?></td>
                            <td onclick="window.location='/clients/cars?id=<?= $client['id'] ?>'">
                                <?= htmlspecialchars($client['name']) ?></td>
                            <td onclick="window.location='/clients/cars?id=<?= $client['id'] ?>'">
                                <?= htmlspecialchars($client['email']) ?></td>
                            <td onclick="window.location='/clients/cars?id=<?= $client['id'] ?>'">
                                <?= htmlspecialchars($client['document_type'] === '1' ? 'DNI' : ($client['document_type'] === '2' ? 'RUC' : 'Pasaporte')) ?>
                            </td>
                            <td onclick="window.location='/clients/cars?id=<?= $client['id'] ?>'">
                                <?= htmlspecialchars($client['document_number']) ?></td>
                            <td onclick="window.location='/clients/cars?id=<?= $client['id'] ?>'">
                                <?= htmlspecialchars($client['phone']) ?></td>
                            <td>
                                <a href="/clients/cars?id=<?= $client['id'] ?>" class="btn btn-sm btn-primary">Ver Autos</a>
                                <a href="/clients/edit?id=<?= $client['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                                <a class="btn btn-danger btn-sm btn-delete" onclick="deleteClient('<?= $client['id'] ?>')">
                                    Eliminar
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">No hay clientes registrados.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function deleteClient(id) {
            const url = `/clients/delete?id=${id}`;

            Swal.fire({
                title: '¿Estás seguro de eliminar este cliente?',
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

</body>

</html>