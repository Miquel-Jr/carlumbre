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

    <h1>Autos de <?= htmlspecialchars($client['name']) ?></h1>

    <a href="/clients/cars/create?client_id=<?= $client['id'] ?>" class="btn btn-success mb-3">Agregar Auto</a>

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
            <a href="/clients/cars/edit?id=<?= $car['id'] ?>&client_id=<?= $client['id'] ?>"
              class="btn btn-sm btn-warning">Editar</a>
            <a onclick="deleteCar(<?= $car['id'] ?>, <?= $client['id'] ?>)" class="btn btn-sm btn-danger">Eliminar</a>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php else: ?>
        <tr>
          <td colspan="6" class="text-center">No hay autos registrados para este cliente.</td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>

    <br>
    <a href="/clients" class="btn btn-sm btn-warning">Volver al inicio</a>
  </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php view('partials/sweetalert'); ?>
<?php view('partials/mobile-table-cards'); ?>
<script>
function deleteCar(id, clientId) {
  const url = `/clients/cars/delete?id=${id}&client_id=${clientId}`;

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
</script>

</html>
