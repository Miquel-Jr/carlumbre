<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Factura #<?= (int) $invoice['id'] ?> | Carlumbre</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

  <?php view('partials/menu'); ?>

  <div class="container mt-5 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Factura #<?= (int) $invoice['id'] ?></h2>
      <a href="/billing" class="btn btn-secondary">Volver</a>
    </div>

    <!-- Formulario para editar número de factura -->
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-dark text-white">
        <strong>Número de Factura</strong>
      </div>
      <div class="card-body">
        <form method="POST" action="/billing/update-invoice-number" class="row g-3 align-items-end">
          <div class="col-md-8">
            <label for="invoice_number" class="form-label">N° de Factura del Sistema de Facturación:</label>
            <input type="hidden" name="id" value="<?= (int) $invoice['id'] ?>">
            <input type="text" class="form-control" id="invoice_number" name="invoice_number"
              value="<?= htmlspecialchars($invoice['invoice_number'] ?? '') ?>" placeholder="Ej: F001-00001234">
          </div>
          <div class="col-md-4">
            <button type="submit" class="btn btn-success w-100">
              <i class="bi bi-save"></i> Guardar Número de Factura
            </button>
          </div>
        </form>
        <?php if (!empty($invoice['invoice_number'])): ?>
        <div class="mt-3">
          <div class="alert alert-info mb-0">
            <strong>N° de Factura Actual:</strong> <?= htmlspecialchars($invoice['invoice_number']) ?>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="card shadow-sm mb-4">
      <div class="card-body">
        <div><strong>OT:</strong> #<?= (int) $invoice['work_order_number'] ?></div>
        <div><strong>Presupuesto:</strong> #<?= (int) $invoice['quote_number'] ?></div>
        <div><strong>Cliente:</strong> <?= htmlspecialchars($invoice['client_name']) ?></div>
        <div><strong>Documento:</strong> <?= htmlspecialchars($invoice['document_number'] ?? '-') ?></div>
        <div><strong>Teléfono:</strong> <?= htmlspecialchars($invoice['phone'] ?? '-') ?></div>
        <div><strong>Auto:</strong> <?= htmlspecialchars($invoice['car_info'] ?? '-') ?></div>
        <div><strong>Emitida:</strong> <?= date('d/m/Y H:i', strtotime($invoice['issued_at'])) ?></div>
      </div>
    </div>

    <div class="card shadow-sm">
      <div class="card-header bg-dark text-white">Detalle de facturación</div>
      <div class="card-body">
        <table class="table table-bordered table-striped align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Descripción</th>
              <th>Cantidad</th>
              <th>P. Unit.</th>
              <th>Subtotal</th>
              <th>Origen</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($items)): ?>
            <?php foreach ($items as $item): ?>
            <tr>
              <td><?= (int) $item['id'] ?></td>
              <td><?= htmlspecialchars($item['description']) ?></td>
              <td><?= (int) $item['quantity'] ?></td>
              <td>S/ <?= number_format((float) $item['unit_price'], 2) ?></td>
              <td>S/ <?= number_format((float) $item['subtotal'], 2) ?></td>
              <td>
                <?php if (($item['source'] ?? 'manual') === 'quote'): ?>
                <span class="badge bg-info text-dark">Presupuesto</span>
                <?php else: ?>
                <span class="badge bg-secondary">Manual</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php else: ?>
            <tr>
              <td colspan="6" class="text-center">No hay ítems en esta factura.</td>
            </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="text-end mt-3">
      <h4 class="mb-0">TOTAL: S/ <?= number_format((float) $invoice['total'], 2) ?></h4>
    </div>
  </div>

  <?php view('partials/sweetalert'); ?>
</body>

</html>
