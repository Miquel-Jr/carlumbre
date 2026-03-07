<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Vigencia de Garantías | Carlumbre</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

  <?php view('partials/menu'); ?>

  <div class="container mt-5 mb-5">
    <h2 class="mb-4">Vigencia de garantías</h2>

    <div class="d-flex justify-content-between mb-3">
      <form method="GET" action="/warranties" class="row g-2 align-items-center">
        <div class="col-auto">
          <input type="text" name="search" class="form-control" placeholder="Buscar factura, cliente, placa o servicio"
            data-debounce-search data-debounce-ms="500" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        </div>
        <div class="col-auto">
          <select name="status" class="form-select">
            <option value="">Todos</option>
            <option value="active" <?= ($currentStatus ?? '') === 'active' ? 'selected' : '' ?>>Vigentes</option>
            <option value="expired" <?= ($currentStatus ?? '') === 'expired' ? 'selected' : '' ?>>Vencidas</option>
          </select>
        </div>
        <div class="col-auto">
          <button class="btn btn-primary">Filtrar</button>
          <a href="/warranties" class="btn btn-secondary">Limpiar</a>
        </div>
      </form>

      <a href="/billing" class="btn btn-outline-dark">Volver a facturación</a>
    </div>

    <div class="card shadow-sm">
      <div class="card-body">
        <table class="table table-bordered table-striped align-middle table-mobile-cards mb-0">
          <thead class="table-dark">
            <tr>
              <th>ID</th>
              <th>Factura</th>
              <th>Cliente</th>
              <th>Auto</th>
              <th>Servicio</th>
              <th>Garantía</th>
              <th>Inicio</th>
              <th>Vence</th>
              <th>Estado</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($warranties)): ?>
            <?php foreach ($warranties as $warranty): ?>
            <tr>
              <td><?= (int) ($warranty['id'] ?? 0) ?></td>
              <td>
                <a href="/billing/show?id=<?= (int) ($warranty['invoice_id'] ?? 0) ?>">
                  #<?= htmlspecialchars($warranty['invoice_number'] ?? $warranty['invoice_id']) ?>
                </a>
              </td>
              <td>
                <?= htmlspecialchars($warranty['client_name'] ?? '-') ?><br>
                <small class="text-muted"><?= htmlspecialchars($warranty['client_phone'] ?? '-') ?></small>
              </td>
              <td><?= htmlspecialchars($warranty['car_info'] ?? '-') ?></td>
              <td><?= htmlspecialchars($warranty['service_description'] ?? '-') ?></td>
              <td><?= (int) ($warranty['warranty_months'] ?? 0) ?> meses</td>
              <td>
                <?= !empty($warranty['starts_at']) ? date('d/m/Y', strtotime($warranty['starts_at'])) : '-' ?>
              </td>
              <td>
                <?= !empty($warranty['expires_at']) ? date('d/m/Y', strtotime($warranty['expires_at'])) : '-' ?>
              </td>
              <td>
                <?php if (($warranty['status'] ?? '') === 'active'): ?>
                <span class="badge bg-success">Vigente</span>
                <?php else: ?>
                <span class="badge bg-danger">Vencida</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php else: ?>
            <tr>
              <td colspan="9" class="text-center">No hay garantías registradas.</td>
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
</body>

</html>
