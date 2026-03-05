<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>OT #<?= (int) $workOrder['id'] ?> | Carlumbre</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

  <?php view('partials/menu'); ?>

  <div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Orden de Trabajo #<?= (int) $workOrder['id'] ?></h2>
      <a href="/work-orders" class="btn btn-secondary">Volver</a>
    </div>

    <div class="card mb-4 shadow-sm">
      <div class="card-body">
        <div><strong>Presupuesto:</strong> #<?= (int) $workOrder['quote_number'] ?></div>
        <div><strong>Cliente:</strong> <?= htmlspecialchars($workOrder['client_name']) ?></div>
        <div><strong>Teléfono:</strong> <?= htmlspecialchars($workOrder['phone'] ?? '-') ?></div>
        <div><strong>Auto:</strong> <?= htmlspecialchars($workOrder['car_info']) ?></div>
      </div>
    </div>

    <div class="card shadow-sm mb-4">
      <div class="card-header bg-dark text-white">Actividades registradas</div>
      <div class="card-body">
        <table class="table table-bordered table-striped align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Descripción</th>
              <th>Cantidad</th>
              <th>Origen</th>
              <th>F. Registro</th>
              <th>F. Actualización</th>
              <th>Estado</th>
              <th>Acción</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($activities)): ?>
            <?php foreach ($activities as $activity): ?>
            <tr>
              <td><?= (int) $activity['id'] ?></td>
              <td><?= htmlspecialchars($activity['description']) ?></td>
              <td><?= (int) $activity['quantity'] ?></td>
              <td>
                <?php if (($activity['source'] ?? 'manual') === 'quote'): ?>
                <span class="badge bg-info text-dark">Presupuesto</span>
                <?php else: ?>
                <span class="badge bg-secondary">Manual</span>
                <?php endif; ?>
              </td>
              <td><?= htmlspecialchars($activity['created_at'] ?? '-') ?></td>
              <td><?= htmlspecialchars($activity['updated_at'] ?? '-') ?></td>
              <td>
                <?php if (($activity['status'] ?? 'pending') === 'completed'): ?>
                <span class="badge bg-success">Completada</span>
                <?php else: ?>
                <span class="badge bg-warning text-dark">Pendiente</span>
                <?php endif; ?>
              </td>
              <td>
                <form method="POST" action="/work-orders/update-activity-status" class="d-inline">
                  <input type="hidden" name="work_order_id" value="<?= (int) $workOrder['id'] ?>">
                  <input type="hidden" name="activity_id" value="<?= (int) $activity['id'] ?>">

                  <?php if (($activity['status'] ?? 'pending') === 'completed'): ?>
                  <input type="hidden" name="status" value="pending">
                  <button type="submit" class="btn btn-sm btn-outline-secondary">Reabrir</button>
                  <?php else: ?>
                  <input type="hidden" name="status" value="completed">
                  <button type="submit" class="btn btn-sm btn-success">Terminar</button>
                  <?php endif; ?>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php else: ?>
            <tr>
              <td colspan="6" class="text-center">No hay actividades registradas.</td>
            </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="card shadow-sm mb-5">
      <div class="card-header">Agregar actividad adicional</div>
      <div class="card-body">
        <form method="POST" action="/work-orders/add-activity" class="row g-3">
          <input type="hidden" name="work_order_id" value="<?= (int) $workOrder['id'] ?>">

          <div class="col-md-8">
            <label for="description" class="form-label">Descripción *</label>
            <input type="text" id="description" name="description" class="form-control" required
              placeholder="Ej: Limpieza de inyectores">
          </div>

          <div class="col-md-2">
            <label for="quantity" class="form-label">Cantidad</label>
            <input type="number" id="quantity" name="quantity" min="1" value="1" class="form-control">
          </div>

          <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-success w-100">Agregar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <?php view('partials/sweetalert'); ?>
</body>

</html>
