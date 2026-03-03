<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Notificaciones WhatsApp | Carlumbre</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <?php view('partials/menu'); ?>

    <div class="container mt-5">

        <h1 class="mb-4">Historial de Notificaciones WhatsApp</h1>

        <!-- Estadísticas -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-bg-primary">
                    <div class="card-body">
                        <h5 class="card-title">Total</h5>
                        <h2><?= $statistics['total'] ?? 0 ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-bg-success">
                    <div class="card-body">
                        <h5 class="card-title">Enviados</h5>
                        <h2><?= $statistics['sent'] ?? 0 ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-bg-danger">
                    <div class="card-body">
                        <h5 class="card-title">Fallidos</h5>
                        <h2><?= $statistics['failed'] ?? 0 ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-bg-warning">
                    <div class="card-body">
                        <h5 class="card-title">Pendientes</h5>
                        <h2><?= $statistics['pending'] ?? 0 ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <form method="GET" action="/notifications" class="mb-3">
            <div class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Buscar por cliente o teléfono"
                        value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Todos los estados</option>
                        <option value="sent" <?= ($currentStatus === 'sent') ? 'selected' : '' ?>>Enviados</option>
                        <option value="failed" <?= ($currentStatus === 'failed') ? 'selected' : '' ?>>Fallidos</option>
                        <option value="pending" <?= ($currentStatus === 'pending') ? 'selected' : '' ?>>Pendientes</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100">Filtrar</button>
                </div>
                <div class="col-md-3">
                    <a href="/notifications" class="btn btn-secondary w-100">Limpiar</a>
                </div>
            </div>
        </form>

        <!-- Tabla de notificaciones -->
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Servicio</th>
                        <th>Teléfono</th>
                        <th>Estado</th>
                        <th>Fecha Envío</th>
                        <th>Mensaje</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($notifications)): ?>
                        <?php foreach ($notifications as $notification): ?>
                            <tr>
                                <td><?= $notification['id'] ?></td>
                                <td><?= htmlspecialchars($notification['client_name'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($notification['service_name'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($notification['phone_number']) ?></td>
                                <td>
                                    <?php if ($notification['status'] === 'sent'): ?>
                                        <span class="badge bg-success">Enviado</span>
                                    <?php elseif ($notification['status'] === 'failed'): ?>
                                        <span class="badge bg-danger" 
                                            title="<?= htmlspecialchars($notification['error_message'] ?? 'Sin detalle') ?>">
                                            Fallido
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Pendiente</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($notification['sent_at']): ?>
                                        <?= date('d/m/Y H:i', strtotime($notification['sent_at'])) ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" 
                                        onclick="showMessage(<?= htmlspecialchars(json_encode($notification['message_content'])) ?>)">
                                        Ver mensaje
                                    </button>
                                </td>
                                <td>
                                    <?php if ($notification['status'] === 'failed'): ?>
                                        <button type="button" class="btn btn-sm btn-warning" 
                                            onclick="resendNotification('<?= $notification['id'] ?>')">
                                            Reenviar
                                        </button>
                                    <?php endif; ?>
                                    
                                    <button type="button" class="btn btn-danger btn-sm" 
                                        onclick="deleteNotification('<?= $notification['id'] ?>')">
                                        Eliminar
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">
                                No hay notificaciones registradas.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</body>

    <?php view('partials/sweetalert'); ?>
    <script>
        function showMessage(message) {
            Swal.fire({
                title: 'Contenido del mensaje',
                text: message,
                icon: 'info',
                confirmButtonText: 'Cerrar'
            });
        }

        function deleteNotification(id) {
            const url = `/notifications/delete?id=${id}`;

            Swal.fire({
                title: '¿Estás seguro de eliminar esta notificación?',
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
        }

        function resendNotification(id) {
            const url = `/notifications/resend?id=${id}`;

            Swal.fire({
                title: '¿Deseas reenviar este mensaje?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#d4aa04',
                cancelButtonColor: '#000000',
                confirmButtonText: 'Sí, reenviar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        }

    </script>

</html>
