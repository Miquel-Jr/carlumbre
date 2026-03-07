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
            data-debounce-search data-debounce-ms="500" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
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
      <table class="table table-striped table-bordered table-mobile-cards">
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
                onclick="openMessageEditor('<?= (int) $notification['id'] ?>', <?= htmlspecialchars(json_encode($notification['message_content'])) ?>)">
                Ver / Editar mensaje
              </button>
            </td>
            <td>
              <?php if ($notification['status'] === 'failed'): ?>
              <button type="button" class="btn btn-sm btn-warning"
                onclick="resendNotification('<?= $notification['id'] ?>', <?= htmlspecialchars(json_encode($notification['message_content'])) ?>, true)">
                Reenviar
              </button>
              <?php endif; ?>

              <?php if ($notification['status'] === 'pending'): ?>
              <button type="button" class="btn btn-sm btn-warning"
                onclick="resendNotification('<?= $notification['id'] ?>', <?= htmlspecialchars(json_encode($notification['message_content'])) ?>, false)">
                Enviar
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

  <div class="modal fade" id="messageEditorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <form method="POST" action="/notifications/update-message" id="messageEditorForm">
          <div class="modal-header">
            <h5 class="modal-title">Editar mensaje de notificación</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="id" id="messageEditorId">
            <div class="mb-0">
              <label for="messageEditorContent" class="form-label">Mensaje</label>
              <textarea class="form-control" name="message_content" id="messageEditorContent" rows="10"
                required></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
          </div>
        </form>
      </div>
    </div>
  </div>

</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php view('partials/sweetalert'); ?>
<?php view('partials/mobile-table-cards'); ?>
<?php view('partials/debounced-search'); ?>
<script>
let messageEditorModal = null;
let messageEditorIdInput = null;
let messageEditorContentInput = null;

function escapeHtml(value) {
  return String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

function initMessageEditorModal() {
  if (messageEditorModal && messageEditorIdInput && messageEditorContentInput) {
    return true;
  }

  const messageEditorModalElement = document.getElementById('messageEditorModal');
  messageEditorIdInput = document.getElementById('messageEditorId');
  messageEditorContentInput = document.getElementById('messageEditorContent');

  if (!window.bootstrap || !messageEditorModalElement || !messageEditorIdInput || !messageEditorContentInput) {
    return false;
  }

  messageEditorModal = bootstrap.Modal.getOrCreateInstance(messageEditorModalElement);
  return true;
}

function openMessageEditor(id, message) {
  if (!initMessageEditorModal()) {
    Swal.fire({
      icon: 'error',
      title: 'No se pudo abrir el editor',
      text: 'Recarga la página e inténtalo nuevamente.'
    });
    return;
  }

  messageEditorIdInput.value = id;
  messageEditorContentInput.value = String(message ?? '');
  messageEditorModal.show();
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

function resendNotification(id, message, isResend = false) {
  const url = `/notifications/resend?id=${id}`;

  const actionText = isResend ? 'reenviar' : 'enviar';
  const safeMessage = escapeHtml(message || '');

  Swal.fire({
    title: `¿Deseas ${actionText} este mensaje?`,
    html: `<div class="text-start"><strong>Previsualización:</strong><div class="border rounded p-2 mt-2 bg-light" style="white-space: pre-line;">${safeMessage}</div></div>`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#d4aa04',
    cancelButtonColor: '#000000',
    confirmButtonText: `Sí, ${actionText}`,
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = url;
    }
  });
}
</script>

</html>
