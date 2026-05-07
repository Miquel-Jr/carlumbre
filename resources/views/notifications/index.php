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
            <option value="sent" <?= ($currentStatus === 'opened' || $currentStatus === 'sent') ? 'selected' : '' ?>>
              Enviados</option>
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
              <?php if (in_array($notification['status'], ['opened', 'sent'], true)): ?>
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
              <?php $isSent = in_array($notification['status'], ['opened', 'sent'], true); ?>
              <button type="button" class="btn btn-sm btn-outline-secondary"
                onclick="openMessageEditor('<?= (int) $notification['id'] ?>', <?= htmlspecialchars(json_encode($notification['message_content'])) ?>, <?= $isSent ? 'false' : 'true' ?>)">
                <?= $isSent ? 'Ver mensaje' : 'Ver / Editar mensaje' ?>
              </button>
            </td>
            <td>
              <?php if ($notification['status'] === 'failed'): ?>
              <button type="button" class="btn btn-sm btn-warning"
                onclick="resendNotification('<?= $notification['id'] ?>', <?= htmlspecialchars(json_encode($notification['message_content'])) ?>, true)">
                Reabrir chat
              </button>
              <?php endif; ?>

              <?php if ($notification['status'] === 'pending'): ?>
              <button type="button" class="btn btn-sm btn-warning"
                onclick="resendNotification('<?= $notification['id'] ?>', <?= htmlspecialchars(json_encode($notification['message_content'])) ?>, false)">
                Abrir chat
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
let messageEditorSubmitButton = null;
let messageEditorModalTitle = null;
const pendingNotificationStorageKey = 'pendingNotificationDeliveryId';

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
  messageEditorSubmitButton = document.querySelector('#messageEditorForm button[type="submit"]');
  messageEditorModalTitle = messageEditorModalElement?.querySelector('.modal-title');

  if (!window.bootstrap || !messageEditorModalElement || !messageEditorIdInput || !messageEditorContentInput || !
    messageEditorSubmitButton || !messageEditorModalTitle) {
    return false;
  }

  messageEditorModal = bootstrap.Modal.getOrCreateInstance(messageEditorModalElement);
  return true;
}

function openMessageEditor(id, message, canEdit = true) {
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
  messageEditorContentInput.readOnly = !canEdit;
  messageEditorSubmitButton.style.display = canEdit ? '' : 'none';
  messageEditorModalTitle.textContent = canEdit ? 'Editar mensaje de notificación' : 'Mensaje de notificación';
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
  const actionText = isResend ? 'abrir nuevamente' : 'abrir';
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
      prepareNotificationDelivery(id);
    }
  });
}

async function prepareNotificationDelivery(id) {
  try {
    const body = new URLSearchParams({
      id: String(id)
    });

    const response = await fetch('/notifications/prepare-resend', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body
    });

    const result = await response.json();

    if (!response.ok || !(result?.success)) {
      Swal.fire({
        icon: 'error',
        title: 'No se pudo abrir WhatsApp',
        text: result?.message || 'Ocurrió un error al preparar el chat.'
      });
      return;
    }

    const notificationId = Number(result?.notification_id || 0);
    if (!notificationId || !result?.url) {
      Swal.fire({
        icon: 'error',
        title: 'Error interno',
        text: 'No se pudo preparar la confirmación de la notificación.'
      });
      return;
    }

    sessionStorage.setItem(pendingNotificationStorageKey, String(notificationId));

    const whatsappWindow = window.open(result.url, '_blank');
    if (!whatsappWindow) {
      window.location.href = result.url;
      return;
    }

    let hasLostFocus = false;

    const onBlur = () => {
      hasLostFocus = true;
    };

    const onFocus = () => {
      if (!hasLostFocus) {
        return;
      }

      window.removeEventListener('focus', onFocus);
      promptNotificationDeliveryConfirmation(notificationId);
    };

    window.addEventListener('blur', onBlur, {
      once: true
    });
    window.addEventListener('focus', onFocus);
  } catch (error) {
    Swal.fire({
      icon: 'error',
      title: 'Error de conexión',
      text: 'No se pudo completar la solicitud. Inténtalo nuevamente.'
    });
  }
}

async function confirmNotificationDelivery(notificationId, wasSent) {
  const body = new URLSearchParams({
    notification_id: String(notificationId),
    was_sent: wasSent ? '1' : '0'
  });

  const response = await fetch('/notifications/confirm-delivery', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
      'X-Requested-With': 'XMLHttpRequest'
    },
    body
  });

  const result = await response.json();

  if (!response.ok || !(result?.success)) {
    throw new Error(result?.message || 'No se pudo actualizar la notificación.');
  }

  sessionStorage.removeItem(pendingNotificationStorageKey);
  return result;
}

async function promptNotificationDeliveryConfirmation(notificationId) {
  const result = await Swal.fire({
    icon: 'question',
    title: '¿Llegaste a enviar el mensaje?',
    text: 'Confirma el estado final de este envío de WhatsApp.',
    showCancelButton: true,
    showDenyButton: true,
    confirmButtonText: 'Sí, enviado',
    denyButtonText: 'No, faltó enviar',
    cancelButtonText: 'Más tarde'
  });

  if (result.isDismissed) {
    return;
  }

  try {
    const wasSent = result.isConfirmed;
    const updateResult = await confirmNotificationDelivery(notificationId, wasSent);

    Swal.fire({
      icon: 'success',
      title: wasSent ? 'Marcado como enviado' : 'Marcado como pendiente',
      text: updateResult?.message || 'Estado actualizado correctamente.'
    }).then(() => {
      window.location.reload();
    });
  } catch (error) {
    Swal.fire({
      icon: 'error',
      title: 'No se pudo actualizar el estado',
      text: error?.message || 'Ocurrió un error inesperado.'
    });
  }
}

window.addEventListener('load', () => {
  const pendingId = Number(sessionStorage.getItem(pendingNotificationStorageKey) || 0);
  if (pendingId > 0) {
    promptNotificationDeliveryConfirmation(pendingId);
  }
});
</script>

</html>
