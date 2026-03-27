<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Órdenes de Trabajo | Carlumbre</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

  <?php view('partials/menu'); ?>

  <div class="container mt-5">
    <h2 class="mb-4">Órdenes de Trabajo</h2>

    <div class="d-flex justify-content-between mb-3">
      <form method="GET" action="/work-orders" class="d-flex">
        <input type="text" name="search" class="form-control me-2" placeholder="Buscar orden de trabajo..."
          data-debounce-search data-debounce-ms="500" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        <button class="btn btn-primary">Buscar</button>
        <button class="btn btn-secondary" type="button" onclick="window.location='/work-orders'">Limpiar</button>
      </form>
    </div>

    <div class="card shadow">
      <div class="card-body">
        <table class="table table-bordered table-striped align-middle table-mobile-cards">
          <thead class="table-dark">
            <tr>
              <th># OT</th>
              <th>Presupuesto</th>
              <th>Cliente</th>
              <th>Auto</th>
              <th>Estado</th>
              <th>Fecha</th>
              <th width="200">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($workOrders)): ?>
            <?php foreach ($workOrders as $workOrder): ?>
            <tr>
              <td><?= (int) $workOrder['id'] ?></td>
              <td>#<?= (int) $workOrder['quote_number'] ?></td>
              <td><?= htmlspecialchars($workOrder['client_name']) ?></td>
              <td><?= htmlspecialchars($workOrder['car_info']) ?></td>
              <td>
                <?php if (($workOrder['status'] ?? 'pending') === 'completed'): ?>
                <span class="badge bg-success">Completada</span>
                <?php elseif (($workOrder['status'] ?? 'pending') === 'in_progress'): ?>
                <span class="badge bg-primary">En progreso</span>
                <?php else: ?>
                <span class="badge bg-warning text-dark">Pendiente</span>
                <?php endif; ?>
              </td>
              <td><?= date('d/m/Y', strtotime($workOrder['created_at'])) ?></td>
              <td>
                <a href="/work-orders/show?id=<?= (int) $workOrder['id'] ?>" class="btn btn-sm btn-dark">Ver</a>
                <a class="btn btn-sm btn-danger" onclick="deleteWorkOrder('<?= (int) $workOrder['id'] ?>')">Eliminar</a>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php else: ?>
            <tr>
              <td colspan="7" class="text-center">Aún no hay órdenes de trabajo generadas.</td>
            </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <?php view('partials/sweetalert'); ?>
  <?php view('partials/mobile-table-cards'); ?>
  <?php view('partials/debounced-search'); ?>
  <script>
  function deleteWorkOrder(id) {
    Swal.fire({
      title: '¿Eliminar orden de trabajo?',
      text: 'Se eliminarán también sus actividades registradas.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = `/work-orders/delete?id=${id}`;
      }
    });
  }
  </script>
</body>

</html>
