<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Presupuestos | Carlumbre</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <?php view('partials/menu'); ?>

    <div class="container mt-5">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Presupuestos</h2>
            <a href="/quotes/create" class="btn btn-success">
                + Nuevo Presupuesto
            </a>
        </div>

        <div class="card shadow">
            <div class="card-body">

                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Cliente</th>
                            <th>Auto</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th width="250">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php if (!empty($quotes)): ?>
                            <?php foreach ($quotes as $quote): ?>
                                <tr>
                                    <td><?= $quote['id'] ?></td>

                                    <td>
                                        <?= htmlspecialchars($quote['client_name']) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($quote['car_info']) ?>
                                    </td>

                                    <td>
                                        S/ <?= number_format($quote['total'], 2) ?>
                                    </td>

                                    <td>
                                        <?php if ($quote['status'] === 'pending'): ?>
                                            <span class="badge bg-warning text-dark">Pendiente</span>
                                        <?php elseif ($quote['status'] === 'approved'): ?>
                                            <span class="badge bg-success">Aprobado</span>
                                        <?php elseif ($quote['status'] === 'rejected'): ?>
                                            <span class="badge bg-secondary">Rechazado</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?= date('d/m/Y', strtotime($quote['created_at'])) ?>
                                    </td>

                                    <td>

                                        <a href="/quotes/pdf?id=<?= $quote['id'] ?>" class="btn btn-sm btn-warning">
                                            Descargar
                                        </a>

                                        <?php if ($quote['status'] === 'pending'): ?>

                                            <a href="/quotes/edit?id=<?= $quote['id'] ?>" class="btn btn-sm btn-warning">
                                                Editar
                                            </a>

                                            <a class="btn btn-sm btn-success" onclick="approveQuote('<?= $quote['id'] ?>')">
                                                Aprobar
                                            </a>

                                            <a class="btn btn-sm btn-secondary" onclick="rejectQuote('<?= $quote['id'] ?>')">
                                                Rechazar
                                            </a>

                                        <?php endif; ?>

                                        <a class="btn btn-sm btn-danger" onclick="deleteQuote('<?= $quote['id'] ?>')">
                                            Eliminar
                                        </a>

                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>

                            <tr>
                                <td colspan="7" class="text-center">
                                    No hay presupuestos registrados.
                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>
                </table>

            </div>
        </div>

    </div>

</body>

<?php view('partials/sweetalert'); ?>
<script>
    function deleteQuote(id) {
        Swal.fire({
            title: '¿Eliminar presupuesto?',
            text: "Esta acción no se puede deshacer.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `/quotes/delete?id=${id}`;
            }
        });
    }

    function approveQuote(id) {
        Swal.fire({
            title: '¿Aprobar presupuesto?',
            text: "¿Estás seguro de aprobar este presupuesto?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, aprobar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `/quotes/approve?id=${id}`;
            }
        });
    }

    function rejectQuote(id) {
        Swal.fire({
            title: '¿Rechazar presupuesto?',
            text: "¿Estás seguro de rechazar este presupuesto?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#6c757d',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, rechazar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `/quotes/reject?id=${id}`;
            }
        });
    }

    
</script>

</html>