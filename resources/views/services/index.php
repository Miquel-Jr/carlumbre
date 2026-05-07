<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Servicios | Carlumbre</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

  <?php
    $servicesPreviewMap = [];
    $whatsappEligibleClients = [];

    if (!empty($services)) {
        foreach ($services as $serviceItem) {
            $servicesPreviewMap[$serviceItem['id']] = [
                'name' => $serviceItem['name'] ?? 'Servicio',
                'description' => $serviceItem['description'] ?? '',
                'price' => isset($serviceItem['price']) ? number_format((float)$serviceItem['price'], 2) : '0.00'
            ];
        }
    }

    if (!empty($clients)) {
      foreach ($clients as $clientItem) {
        $phoneDigits = preg_replace('/\D+/', '', (string) ($clientItem['phone'] ?? ''));

        if (strlen($phoneDigits) === 11 && str_starts_with($phoneDigits, '51')) {
          $phoneDigits = substr($phoneDigits, 2);
        }

        if (preg_match('/^\d{9}$/', $phoneDigits)) {
          $whatsappEligibleClients[] = $clientItem;
        }
      }
    }
    ?>

  <?php view('partials/menu'); ?>

  <div class="container mt-5">

    <h1 class="mb-4">Servicios del Taller</h1>

    <div class="d-flex justify-content-between mb-3">
      <form method="GET" action="/services" class="d-flex">
        <input type="text" name="search" class="form-control me-2" placeholder="Buscar servicio..." data-debounce-search
          data-debounce-ms="500" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        <button class="btn btn-primary">Buscar</button>
        <button class="btn btn-secondary" type="button" onclick="window.location='/services'">Limpiar</button>
      </form>

      <a href="/services/create" class="btn btn-success">
        + Nuevo Servicio
      </a>
    </div>

    <table class="table table-striped table-bordered table-mobile-cards">
      <thead class="table-dark">
        <tr>
          <th>ID</th>
          <th>Servicio</th>
          <th>Descripción</th>
          <th>Precio Base</th>
          <th>Garantía</th>
          <th>Tiempo Base</th>
          <th>Estado</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($services)): ?>
        <?php foreach ($services as $service): ?>
        <tr>
          <td><?= $service['id'] ?></td>
          <td><?= htmlspecialchars($service['name']) ?></td>
          <td><?= htmlspecialchars($service['description']) ?></td>
          <td>S/ <?= number_format($service['price'], 2) ?></td>
          <td>
            <?php if (($service['has_warranty'] ?? 0) == 1): ?>
            <span class="badge bg-success">Sí</span>
            <?php else: ?>
            <span class="badge bg-secondary">No</span>
            <?php endif; ?>
          </td>
          <td>
            <?php if (!empty($service['warranty_time_base'])): ?>
            <?= (int) $service['warranty_time_base'] ?> días
            <?php else: ?>
            -
            <?php endif; ?>
          </td>
          <td>
            <?php if ($service['status']): ?>
            <span class="badge bg-success">Activo</span>
            <?php else: ?>
            <span class="badge bg-secondary">Inactivo</span>
            <?php endif; ?>
          </td>
          <td>
            <a href="/services/edit?id=<?= $service['id'] ?>" class="btn btn-sm btn-warning">Editar</a>

            <button type="button" class="btn btn-danger btn-sm btn-delete"
              onclick="deleteService('<?= $service['id'] ?>')">
              Eliminar
            </button>

            <button type="button" class="btn btn-sm btn-info btn-whatsapp" data-service-id="<?= $service['id'] ?>"
              data-service-name="<?= htmlspecialchars($service['name']) ?>">
              Enviar WhatsApp
            </button>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php else: ?>
        <tr>
          <td colspan="8" class="text-center">
            No hay servicios registrados.
          </td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>

  </div>

  <div class="modal fade" id="whatsappModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <form method="POST" action="/services/whatsapp/prepare" id="whatsappForm">
          <div class="modal-header">
            <h5 class="modal-title">Abrir chats por WhatsApp</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="service_id" id="modalServiceId">

            <p class="mb-3">
              Servicio seleccionado: <strong id="modalServiceName">-</strong>
            </p>

            <div class="mb-2">
              <strong>Cliente</strong>
            </div>

            <div class="mb-2">
              <input type="text" class="form-control" id="clientSearchInput"
                placeholder="Buscar cliente por nombre o teléfono...">
            </div>

            <div class="border rounded p-3" style="max-height: 320px; overflow-y: auto;">
              <?php if (!empty($whatsappEligibleClients)): ?>
              <?php foreach ($whatsappEligibleClients as $client): ?>
              <div class="form-check mb-2 client-item">
                <input class="form-check-input client-radio" type="radio" name="client_id" value="<?= $client['id'] ?>"
                  id="client_<?= $client['id'] ?>"
                  data-phone="<?= htmlspecialchars((string) ($client['phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                <label class="form-check-label" for="client_<?= $client['id'] ?>">
                  <?= htmlspecialchars($client['name']) ?>
                  - <?= htmlspecialchars((string) ($client['phone'] ?? '')) ?>
                </label>
              </div>
              <?php endforeach; ?>
              <?php else: ?>
              <p class="text-muted mb-0">No hay clientes con teléfono válido de 9 dígitos.</p>
              <?php endif; ?>
            </div>

            <div class="mt-3">
              <label for="modalFinalMessage" class="form-label">
                Previsualización del mensaje (editable)
              </label>
              <textarea class="form-control" id="modalFinalMessage" name="final_message" rows="8" required></textarea>
              <small class="text-muted">
                Selecciona un solo cliente. El teléfono debe tener formato de 9 dígitos (ejemplo: 987654321).
                Puedes usar {cliente} para reemplazar el nombre automáticamente.
              </small>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">Continuar</button>
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
function deleteService(id) {
  const url = `/services/delete?id=${id}`;

  Swal.fire({
    title: '¿Estás seguro de eliminar este servicio?',
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
};

const whatsappModalElement = document.getElementById('whatsappModal');
const whatsappModal = new bootstrap.Modal(whatsappModalElement);
const modalServiceId = document.getElementById('modalServiceId');
const modalServiceName = document.getElementById('modalServiceName');
const modalFinalMessage = document.getElementById('modalFinalMessage');
const clientRadios = document.querySelectorAll('.client-radio');
const clientItems = document.querySelectorAll('.client-item');
const clientSearchInput = document.getElementById('clientSearchInput');
const whatsappForm = document.getElementById('whatsappForm');
const servicesPreviewMap = <?= json_encode($servicesPreviewMap, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const pendingConfirmationStorageKey = 'pendingWhatsappNotificationId';

function buildDefaultMessage(service) {
  const serviceName = service?.name || 'Servicio';
  const serviceDescription = service?.description ? `Descripción: ${service.description}\n\n` : '';
  const servicePrice = service?.price || '0.00';
  const businessPhone = <?= json_encode(whatsappBusinessPhone(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
  return `¡Hola {cliente}!\n\nDesde CarLumbre queremos ofrecerte nuestro servicio de:\n\nServicio: ${serviceName}\n\n${serviceDescription}Precio: S/ ${servicePrice}\n\nRecuerda traer tu vehículo a tiempo para un mejor servicio.\n\n¿Te interesa? Contáctanos para agendar tu cita.\n\nWhatsApp: ${businessPhone}`;
}

document.querySelectorAll('.btn-whatsapp').forEach((button) => {
  button.addEventListener('click', () => {
    const serviceId = button.getAttribute('data-service-id');
    const serviceName = button.getAttribute('data-service-name');
    const serviceData = servicesPreviewMap[serviceId] || null;

    modalServiceId.value = serviceId;
    modalServiceName.textContent = serviceName;
    modalFinalMessage.value = buildDefaultMessage(serviceData);
    clientRadios.forEach((radio) => {
      radio.checked = false;
    });
    if (clientSearchInput) {
      clientSearchInput.value = '';
      filterClients('');
    }

    whatsappModal.show();
  });
});

function normalizeClientPhone(phone) {
  const digits = String(phone || '').replace(/\D+/g, '');
  if (!digits) {
    return '';
  }

  if (digits.length === 11 && digits.startsWith('51')) {
    return digits.slice(2);
  }

  return digits;
}

function filterClients(term) {
  const normalizedTerm = String(term || '').toLowerCase().trim();

  clientItems.forEach((item) => {
    const label = item.querySelector('label');
    const text = String(label?.textContent || '').toLowerCase();
    item.style.display = text.includes(normalizedTerm) ? '' : 'none';
  });
}

clientSearchInput?.addEventListener('input', (event) => {
  filterClients(event.target.value);
});

whatsappForm.addEventListener('submit', async function(e) {
  const selectedClient = Array.from(clientRadios).find((radio) => radio.checked);
  const finalMessage = (modalFinalMessage.value || '').trim();

  e.preventDefault();

  if (!selectedClient) {
    Swal.fire({
      icon: 'warning',
      title: 'Selecciona un cliente',
      text: 'Debes seleccionar un cliente para abrir WhatsApp.'
    });
    return;
  }

  const normalizedPhone = normalizeClientPhone(selectedClient.dataset.phone || '');
  if (!/^\d{9}$/.test(normalizedPhone)) {
    Swal.fire({
      icon: 'warning',
      title: 'Teléfono inválido',
      text: 'El cliente debe tener un número válido de 9 dígitos.'
    });
    return;
  }

  if (!finalMessage) {
    Swal.fire({
      icon: 'warning',
      title: 'Mensaje vacío',
      text: 'Debes ingresar el mensaje final antes de continuar.'
    });
    return;
  }

  try {
    const formData = new FormData(whatsappForm);
    const response = await fetch('/services/whatsapp/prepare', {
      method: 'POST',
      body: formData,
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    });

    const result = await response.json();

    if (!response.ok || !(result?.success)) {
      Swal.fire({
        icon: 'error',
        title: 'No se pudo abrir WhatsApp',
        text: result?.message || 'Ocurrió un error al preparar el envío.'
      });
      return;
    }

    const notificationId = Number(result?.notification_id || 0);
    if (!notificationId) {
      Swal.fire({
        icon: 'error',
        title: 'Error interno',
        text: 'No se pudo registrar la notificación pendiente.'
      });
      return;
    }

    sessionStorage.setItem(pendingConfirmationStorageKey, String(notificationId));

    const whatsappWindow = window.open(result.url, '_blank');
    whatsappModal.hide();

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
      promptNotificationConfirmation(notificationId);
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
});

async function updateNotificationAfterWhatsapp(notificationId, wasSent) {
  const body = new URLSearchParams({
    notification_id: String(notificationId),
    was_sent: wasSent ? '1' : '0'
  });

  const response = await fetch('/services/whatsapp/confirm', {
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

  sessionStorage.removeItem(pendingConfirmationStorageKey);
  return result;
}

async function promptNotificationConfirmation(notificationId) {
  const result = await Swal.fire({
    icon: 'question',
    title: '¿Llegaste a enviar el mensaje?',
    text: 'Confirma si el mensaje se envió por WhatsApp para actualizar el estado.',
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
    const updateResult = await updateNotificationAfterWhatsapp(notificationId, wasSent);

    Swal.fire({
      icon: 'success',
      title: wasSent ? 'Marcado como enviado' : 'Marcado como pendiente',
      text: updateResult?.message || 'Estado actualizado correctamente.'
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
  const pendingId = Number(sessionStorage.getItem(pendingConfirmationStorageKey) || 0);
  if (pendingId > 0) {
    promptNotificationConfirmation(pendingId);
  }
});
</script>

</html>
