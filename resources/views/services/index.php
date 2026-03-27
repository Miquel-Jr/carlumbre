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
    if (!empty($services)) {
        foreach ($services as $serviceItem) {
            $servicesPreviewMap[$serviceItem['id']] = [
                'name' => $serviceItem['name'] ?? 'Servicio',
                'description' => $serviceItem['description'] ?? '',
                'price' => isset($serviceItem['price']) ? number_format((float)$serviceItem['price'], 2) : '0.00'
            ];
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
        <form method="POST" action="/services/whatsapp" id="whatsappForm">
          <div class="modal-header">
            <h5 class="modal-title">Enviar WhatsApp</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="service_id" id="modalServiceId">

            <p class="mb-3">
              Servicio seleccionado: <strong id="modalServiceName">-</strong>
            </p>

            <div class="d-flex justify-content-between align-items-center mb-2">
              <strong>Clientes</strong>
              <button type="button" class="btn btn-sm btn-outline-secondary" id="toggleClientsBtn">Desmarcar
                todos</button>
            </div>

            <div class="border rounded p-3" style="max-height: 320px; overflow-y: auto;">
              <?php if (!empty($clients)): ?>
              <?php foreach ($clients as $client): ?>
              <div class="form-check mb-2">
                <input class="form-check-input client-checkbox" type="checkbox" name="client_ids[]"
                  value="<?= $client['id'] ?>" id="client_<?= $client['id'] ?>" checked>
                <label class="form-check-label" for="client_<?= $client['id'] ?>">
                  <?= htmlspecialchars($client['name']) ?>
                  <?php if (!empty($client['phone'])): ?>
                  - <?= htmlspecialchars($client['phone']) ?>
                  <?php else: ?>
                  - <span class="text-danger">Sin teléfono</span>
                  <?php endif; ?>
                </label>
              </div>
              <?php endforeach; ?>
              <?php else: ?>
              <p class="text-muted mb-0">No hay clientes registrados.</p>
              <?php endif; ?>
            </div>

            <div class="mt-3">
              <label for="modalFinalMessage" class="form-label">
                Previsualización del mensaje (editable)
              </label>
              <textarea class="form-control" id="modalFinalMessage" name="final_message" rows="8" required></textarea>
              <small class="text-muted">
                Puedes modificar el texto antes de enviar. Usa {cliente} si quieres que el nombre se reemplace
                automáticamente por cada cliente seleccionado.
              </small>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">Enviar</button>
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
const clientCheckboxes = document.querySelectorAll('.client-checkbox');
const toggleClientsBtn = document.getElementById('toggleClientsBtn');
const servicesPreviewMap = <?= json_encode($servicesPreviewMap, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

function buildDefaultMessage(service) {
  const serviceName = service?.name || 'Servicio';
  const serviceDescription = service?.description || '';
  const servicePrice = service?.price || '0.00';

  return `¡Hola {cliente}! 👋\n\nDesde CarLumbre queremos ofrecerte nuestro servicio de:\n\n🔧 ${serviceName}\n\n${serviceDescription}\n\n💰 Precio: S/ ${servicePrice}\n\nRecuerda traer tu vehículo a tiempo para un mejor servicio.\n\n¿Te interesa? Contáctanos para agendar tu cita.\n\n📱 WhatsApp: +51979701851`;
}

document.querySelectorAll('.btn-whatsapp').forEach((button) => {
  button.addEventListener('click', () => {
    const serviceId = button.getAttribute('data-service-id');
    const serviceName = button.getAttribute('data-service-name');
    const serviceData = servicesPreviewMap[serviceId] || null;

    modalServiceId.value = serviceId;
    modalServiceName.textContent = serviceName;
    modalFinalMessage.value = buildDefaultMessage(serviceData);

    clientCheckboxes.forEach((checkbox) => {
      checkbox.checked = true;
    });

    toggleClientsBtn.textContent = 'Desmarcar todos';

    whatsappModal.show();
  });
});

toggleClientsBtn.addEventListener('click', () => {
  const allChecked = Array.from(clientCheckboxes).every((checkbox) => checkbox.checked);

  clientCheckboxes.forEach((checkbox) => {
    checkbox.checked = !allChecked;
  });

  toggleClientsBtn.textContent = allChecked ? 'Marcar todos' : 'Desmarcar todos';
});

document.getElementById('whatsappForm').addEventListener('submit', function(e) {
  const checkedCount = Array.from(clientCheckboxes).filter((checkbox) => checkbox.checked).length;
  const finalMessage = (modalFinalMessage.value || '').trim();

  if (checkedCount === 0) {
    e.preventDefault();
    Swal.fire({
      icon: 'warning',
      title: 'Selecciona clientes',
      text: 'Debes seleccionar al menos un cliente para enviar WhatsApp.'
    });
    return;
  }

  if (!finalMessage) {
    e.preventDefault();
    Swal.fire({
      icon: 'warning',
      title: 'Mensaje vacío',
      text: 'Debes ingresar el mensaje final antes de enviar.'
    });
  }
});
</script>

</html>
