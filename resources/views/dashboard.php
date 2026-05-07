<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Dashboard | Carlumbre</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

  <?php view('partials/menu'); ?>

  <div class="container mt-4 mb-5">
    <h1 class="mb-4">Dashboard operativo</h1>

    <div class="row g-3 mb-4">
      <div class="col-md-6 col-xl-3">
        <div class="card text-bg-success h-100">
          <div class="card-body">
            <h6 class="card-title">Facturas pagadas (mes)</h6>
            <h2 class="mb-0"><?= (int) ($paidInvoicesMonth ?? 0) ?></h2>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-xl-3">
        <div class="card text-bg-primary h-100">
          <div class="card-body">
            <h6 class="card-title">Ingresos del mes</h6>
            <h2 class="mb-0">S/ <?= number_format((float) ($monthlyRevenue ?? 0), 2) ?></h2>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-xl-3">
        <div class="card text-bg-warning h-100">
          <div class="card-body">
            <h6 class="card-title">OT en progreso</h6>
            <h2 class="mb-0"><?= (int) ($workOrdersInProgress ?? 0) ?></h2>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-xl-3">
        <div class="card text-bg-danger h-100">
          <div class="card-body">
            <h6 class="card-title">Garantías por vencer (7 días)</h6>
            <h2 class="mb-0"><?= (int) ($warrantiesExpiringSoon ?? 0) ?></h2>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-lg-6">
        <div class="card h-100 shadow-sm">
          <div class="card-header bg-dark text-white">Resumen de presupuestos</div>
          <div class="card-body">
            <div class="row text-center g-3">
              <div class="col-6 col-md-3">
                <div class="border rounded p-2">
                  <div class="small text-muted">Total</div>
                  <div class="h5 mb-0"><?= (int) ($quoteSummary['total'] ?? 0) ?></div>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div class="border rounded p-2">
                  <div class="small text-muted">Aprobados</div>
                  <div class="h5 mb-0 text-success"><?= (int) ($quoteSummary['approved'] ?? 0) ?></div>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div class="border rounded p-2">
                  <div class="small text-muted">Pendientes</div>
                  <div class="h5 mb-0 text-warning"><?= (int) ($quoteSummary['pending'] ?? 0) ?></div>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div class="border rounded p-2">
                  <div class="small text-muted">Rechazados</div>
                  <div class="h5 mb-0 text-danger"><?= (int) ($quoteSummary['rejected'] ?? 0) ?></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="card h-100 shadow-sm">
          <div class="card-header bg-dark text-white">Estado de notificaciones</div>
          <div class="card-body">
            <div class="row text-center g-3">
              <div class="col-4">
                <div class="border rounded p-2">
                  <div class="small text-muted">Enviadas</div>
                  <div class="h5 mb-0 text-success\"><?= (int) ($notificationsStats['sent'] ?? 0) ?></div>
                </div>
              </div>
              <div class="col-4">
                <div class="border rounded p-2">
                  <div class="small text-muted">Pendientes</div>
                  <div class="h5 mb-0 text-warning"><?= (int) ($notificationsStats['pending'] ?? 0) ?></div>
                </div>
              </div>
              <div class="col-4">
                <div class="border rounded p-2">
                  <div class="small text-muted">Fallidas</div>
                  <div class="h5 mb-0 text-danger"><?= (int) ($notificationsStats['failed'] ?? 0) ?></div>
                </div>
              </div>
            </div>
            <div class="mt-3 text-end">
              <a href="/notifications" class="btn btn-sm btn-outline-primary">Ir a notificaciones</a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
      <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
          <h2 class="h5 mb-0">Ingresos por mes (<?= (int) ($selectedYear ?? date('Y')) ?>)</h2>
          <form method="GET" action="/dashboard" class="d-flex align-items-center gap-2">
            <label for="year" class="form-label mb-0 small text-muted">Año:</label>
            <select name="year" id="year" class="form-select form-select-sm" onchange="this.form.submit()">
              <?php foreach (($availableYears ?? []) as $yearOption): ?>
              <option value="<?= (int) $yearOption ?>"
                <?= (int) $yearOption === (int) ($selectedYear ?? 0) ? 'selected' : '' ?>>
                <?= (int) $yearOption ?>
              </option>
              <?php endforeach; ?>
            </select>
          </form>
        </div>
        <canvas id="monthlyRevenueChart" height="90"></canvas>
      </div>
    </div>

    <div class="row g-3">
      <div class="col-xl-6">
        <div class="card shadow-sm">
          <div class="card-header bg-dark text-white">Garantías por vencer (7 días)</div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-striped table-bordered mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Factura</th>
                    <th>Cliente</th>
                    <th>Servicio</th>
                    <th>Vence</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!empty($expiringWarranties)): ?>
                  <?php foreach ($expiringWarranties as $item): ?>
                  <tr>
                    <td><a
                        href="/billing/show?id=<?= (int) $item['invoice_id'] ?>">#<?= (int) $item['invoice_id'] ?></a>
                    </td>
                    <td><?= htmlspecialchars($item['client_name'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($item['service_description'] ?? '-') ?></td>
                    <td><?= !empty($item['expires_at']) ? date('d/m/Y', strtotime($item['expires_at'])) : '-' ?></td>
                  </tr>
                  <?php endforeach; ?>
                  <?php else: ?>
                  <tr>
                    <td colspan="4" class="text-center">No hay garantías por vencer en los próximos 7 días.</td>
                  </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-6">
        <div class="card shadow-sm">
          <div class="card-header bg-dark text-white">Notificaciones por atender</div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-striped table-bordered mb-0">
                <thead class="table-light">
                  <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Estado</th>
                    <th>Detalle</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!empty($actionableNotifications)): ?>
                  <?php foreach ($actionableNotifications as $item): ?>
                  <tr>
                    <td>#<?= (int) $item['id'] ?></td>
                    <td><?= htmlspecialchars($item['client_name'] ?? '-') ?></td>
                    <td>
                      <?php if (($item['status'] ?? '') === 'failed'): ?>
                      <span class="badge bg-danger">Fallida</span>
                      <?php else: ?>
                      <span class="badge bg-warning text-dark">Pendiente</span>
                      <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($item['error_message'] ?? 'Pendiente de envío') ?></td>
                  </tr>
                  <?php endforeach; ?>
                  <?php else: ?>
                  <tr>
                    <td colspan="4" class="text-center">No hay notificaciones pendientes o fallidas.</td>
                  </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    (function() {
      const canvas = document.getElementById('monthlyRevenueChart');
      if (!canvas || typeof Chart === 'undefined') {
        return;
      }

      const labels = <?= json_encode($monthLabels ?? [], JSON_UNESCAPED_UNICODE) ?>;
      const data = <?= json_encode($monthlyRevenueChart ?? [], JSON_NUMERIC_CHECK) ?>;

      new Chart(canvas, {
        type: 'bar',
        data: {
          labels,
          datasets: [{
            label: 'Ingresos (S/) ',
            data,
            borderWidth: 1,
            borderRadius: 6,
            backgroundColor: 'rgba(13, 110, 253, 0.7)',
            borderColor: 'rgba(13, 110, 253, 1)'
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: true,
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                callback: function(value) {
                  return 'S/ ' + Number(value).toLocaleString('es-PE', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                  });
                }
              }
            }
          },
          plugins: {
            tooltip: {
              callbacks: {
                label: function(context) {
                  const amount = Number(context.raw || 0);
                  return 'Ingresos: S/ ' + amount.toLocaleString('es-PE', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                  });
                }
              }
            },
            legend: {
              display: false
            }
          }
        }
      });
    })();
    </script>
  </div>
</body>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php view('partials/sweetalert'); ?>

</html>
