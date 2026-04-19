<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Autos Cliente | Carlumbre</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>

  </style>
</head>

<body>

  <?php view('partials/menu'); ?>

  <div class="container mt-5">

    <h1 class="mb-4">Autos</h1>

    <!-- Buscador -->
    <form method="GET" action="/clients/cars/plates" class="mb-3 row g-2">
      <div class="col-md-6">
        <input type="text" name="search" class="form-control" placeholder="Buscar por nombre, email o teléfono"
          data-debounce-search data-debounce-ms="500" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
      </div>
      <div class="col-md-2">
        <button class="btn btn-primary w-100">Buscar</button>
      </div>
      <div class="col-md-2">
        <button class="btn btn-secondary w-100" type="button"
          onclick="window.location='/clients/cars/plates'">Limpiar</button>
      </div>
    </form>

    <a href="/clients" class="btn btn-success mb-3">Ver Cliente</a>

    <table class="table table-striped table-bordered table-mobile-cards">
      <thead class="table-dark">
        <tr>
          <th>ID</th>
          <th>Marca</th>
          <th>Modelo</th>
          <th>Placa</th>
          <th>Año</th>
          <th>Color</th>
          <th>Fotos</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($cars)): ?>
        <?php foreach ($cars as $car): ?>
        <tr>
          <td><?= $car['id'] ?></td>
          <td><?= htmlspecialchars($car['brand']) ?></td>
          <td><?= htmlspecialchars($car['model']) ?></td>
          <td><?= htmlspecialchars($car['plate']) ?></td>
          <td>
            <?php if (!empty($car['photos'])): ?>
            <!-- Botón que abre el modal -->
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
              data-bs-target="#photosModal<?= $car['id'] ?>">
              Ver fotos
            </button>

            <!-- Modal con carrusel -->
            <div class="modal fade" id="photosModal<?= $car['id'] ?>" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title">Fotos del auto <?= htmlspecialchars($car['model']) ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                  </div>
                  <div class="modal-body">
                    <div id="carousel<?= $car['id'] ?>" class="carousel slide" data-bs-ride="carousel">
                      <div class="carousel-inner">
                        <?php foreach ($car['photos'] as $index => $photo): ?>
                        <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                          <img src="<?= htmlspecialchars($photo['photo_path']) ?>" class="d-block w-100"
                            alt="Foto del auto">
                        </div>
                        <?php endforeach; ?>
                      </div>
                      <button class="carousel-control-prev" type="button" data-bs-target="#carousel<?= $car['id'] ?>"
                        data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Anterior</span>
                      </button>
                      <button class="carousel-control-next" type="button" data-bs-target="#carousel<?= $car['id'] ?>"
                        data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Siguiente</span>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <?php else: ?>
            No hay fotos
            <?php endif; ?>
          </td>

          <td><?= htmlspecialchars($car['year']) ?></td>

          <td><?= htmlspecialchars($car['color']) ?></td>

          <td>
            <a href="/clients/cars/quotes?car_id=<?= $car['car_id'] ?>" class="btn btn-sm btn-success">
              Presupuestos</a>
            <a href="/clients/cars/edit?id=<?= $car['car_id'] ?>&client_id=<?= $car['client_id'] ?>"
              class="btn btn-sm btn-warning">Editar</a>
            <a onclick="deleteCar(<?= $car['car_id'] ?>, <?= $car['client_id'] ?>)"
              class="btn btn-sm btn-danger">Eliminar</a>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php else: ?>
        <tr>
          <td colspan="7" class="text-center">No hay autos registrados para este cliente.</td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>

    <br>

    <br>
    <a href="/clients" class="btn btn-sm btn-warning">Volver al inicio</a>
  </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php view('partials/sweetalert'); ?>
<?php view('partials/mobile-table-cards'); ?>
<script>
function deleteCar(id, clientId) {
  const url = `/clients/cars/delete?id=${id}&client_id=${clientId}&plate=true`;

  Swal.fire({
    title: '¿Estás seguro de eliminar este auto?',
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

function deleteQuote(id) {
  Swal.fire({
    title: '¿Eliminar presupuesto?',
    text: "Esta acción no se puede deshacer.",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = `/quotes/delete?id=${id}`;
    }
  });
}

function approveQuote(id) {
  Swal.fire({
    title: '¿Aprobar presupuesto?',
    text: "¿Estás seguro de aprobar este presupuesto?",
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#28a745',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Sí, aprobar',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = `/quotes/approve?id=${id}`;
    }
  });
}

function rejectQuote(id) {
  Swal.fire({
    title: '¿Rechazar presupuesto?',
    text: "¿Estás seguro de rechazar este presupuesto?",
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#6c757d',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Sí, rechazar',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = `/quotes/reject?id=${id}`;
    }
  });
}

function createWorkOrder(id) {
  Swal.fire({
    title: '¿Crear orden de trabajo?',
    text: 'Se generará una OT con las actividades del presupuesto aprobado.',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#212529',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Sí, crear OT',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = `/quotes/create-work-order?id=${id}`;
    }
  });
}
</script>

</html>