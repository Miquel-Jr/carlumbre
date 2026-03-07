<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Facturación | Carlumbre</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

  <?php view('partials/menu'); ?>

  <div class="container mt-5">
    <h2 class="mb-4">Facturación</h2>

    <div class="d-flex justify-content-between mb-3">
      <form method="GET" action="/billing" class="d-flex">
        <input type="text" name="search" class="form-control me-2" placeholder="Buscar factura..." data-debounce-search
          data-debounce-ms="500" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        <button class="btn btn-primary">Buscar</button>
        <button class="btn btn-secondary" type="button" onclick="window.location='/billing'">Limpiar</button>
      </form>

      <a href="/warranties" class="btn btn-outline-primary">Vigencia de garantías</a>
    </div>

    <div class="card shadow">
      <div class="card-body">
        <table class="table table-bordered table-striped align-middle table-mobile-cards">
          <thead class="table-dark">
            <tr>
              <th># Factura</th>
              <th>N° Factura Real</th>
              <th>OT</th>
              <th>Presupuesto</th>
              <th>Cliente</th>
              <th>Total</th>
              <th>Estado</th>
              <th>Fecha</th>
              <th width="280">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($invoices)): ?>
            <?php foreach ($invoices as $invoice): ?>
            <tr>
              <td><?= (int) $invoice['id'] ?></td>
              <td>
                <?php if (!empty($invoice['invoice_number'])): ?>
                <strong><?= htmlspecialchars($invoice['invoice_number']) ?></strong>
                <?php else: ?>
                <span class="text-muted">Sin asignar</span>
                <?php endif; ?>
              </td>
              <td>#<?= (int) $invoice['work_order_number'] ?></td>
              <td>#<?= (int) $invoice['quote_number'] ?></td>
              <td><?= htmlspecialchars($invoice['client_name']) ?></td>
              <td>S/ <?= number_format((float) $invoice['total'], 2) ?></td>
              <td>
                <?php if (($invoice['status'] ?? 'issued') === 'paid'): ?>
                <span class="badge bg-success">Pagada</span>
                <?php elseif (($invoice['status'] ?? 'issued') === 'cancelled'): ?>
                <span class="badge bg-secondary">Anulada</span>
                <?php else: ?>
                <span class="badge bg-warning text-dark">Emitida</span>
                <?php endif; ?>
              </td>
              <td><?= date('d/m/Y', strtotime($invoice['issued_at'])) ?></td>
              <td>
                <?php $status = $invoice['status'] ?? 'issued'; ?>
                <a href="/billing/show?id=<?= (int) $invoice['id'] ?>" class="btn btn-sm btn-dark">Ver</a>
                <?php if ($status === 'issued'): ?>
                <form method="POST" action="/billing/update-status" class="d-inline"
                  onsubmit="return confirmStatusChange(this, 'pagada')">
                  <input type="hidden" name="id" value="<?= (int) $invoice['id'] ?>">
                  <input type="hidden" name="status" value="paid">
                  <input type="hidden" name="redirect_to" value="/billing">
                  <button type="submit" class="btn btn-sm btn-outline-success">Pagada</button>
                </form>
                <form method="POST" action="/billing/update-status" class="d-inline"
                  onsubmit="return confirmStatusChange(this, 'anulada')">
                  <input type="hidden" name="id" value="<?= (int) $invoice['id'] ?>">
                  <input type="hidden" name="status" value="cancelled">
                  <input type="hidden" name="redirect_to" value="/billing">
                  <button type="submit" class="btn btn-sm btn-outline-secondary">Anulada</button>
                </form>
                <?php endif; ?>
                <a class="btn btn-sm btn-danger" onclick="deleteInvoice('<?= (int) $invoice['id'] ?>')">
                  Eliminar
                </a>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php else: ?>
            <tr>
              <td colspan="9" class="text-center">No hay facturas registradas.</td>
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
  function deleteInvoice(id) {
    Swal.fire({
      title: '¿Eliminar factura?',
      text: 'Esta acción no se puede deshacer.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = `/billing/delete?id=${id}`;
      }
    });
  }

  function confirmStatusChange(form, targetStatus) {
    Swal.fire({
      title: '¿Cambiar estado de factura?',
      text: `La factura pasará a ${targetStatus}.`,
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Sí, cambiar',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        form.submit();
      }
    });

    return false;
  }
  </script>
</body>

</html>
