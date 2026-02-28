<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Editar Auto | Carlumbre</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <?php view('partials/menu'); ?>

    <div class="container mt-5">

        <h2>Editar Auto de <?= htmlspecialchars($client['name']) ?></h2>

        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['error'];
                unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form action="/clients/cars/update" method="POST" enctype="multipart/form-data">

            <input type="hidden" name="car_id" value="<?= $car['id'] ?>">
            <input type="hidden" name="client_id" value="<?= $client['id'] ?>">

            <div class="row">

                <div class="col-md-6 mb-3">
                    <label class="form-label">Marca</label>
                    <input type="text" name="brand" class="form-control" value="<?= htmlspecialchars($car['brand']) ?>"
                        required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Modelo</label>
                    <input type="text" name="model" class="form-control" value="<?= htmlspecialchars($car['model']) ?>"
                        required>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Año</label>
                    <input type="number" name="year" class="form-control" value="<?= htmlspecialchars($car['year']) ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Placa</label>
                    <input type="text" name="plate" class="form-control" value="<?= htmlspecialchars($car['plate']) ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Color</label>
                    <input type="text" name="color" class="form-control"
                        value="<?= htmlspecialchars($car['color'] ?? '') ?>">
                </div>

            </div>

            <hr>

            <h5>Imágenes actuales</h5>

            <div class="row mb-4">
                <?php if (!empty($photos)): ?>
                    <?php foreach ($photos as $photo): ?>
                        <div class="col-md-3 text-center mb-3">
                            <img src="<?= $photo['photo_path'] ?>" class="img-fluid rounded border mb-2"
                                style="height:150px; object-fit:cover;">

                            <br>

                            <a href="#" class="btn btn-sm btn-danger"
                                onclick="deletePhoto(<?= $photo['id'] ?>, <?= $client['id'] ?>, <?= $car['id'] ?>)">
                                Eliminar
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No hay imágenes registradas.</p>
                <?php endif; ?>
            </div>

            <hr>

            <h5>Agregar nuevas imágenes</h5>

            <div class="mb-3">
                <input type="file" name="photos[]" class="form-control" multiple accept="image/*"
                    onchange="previewImages(event)">
            </div>

            <div id="preview" class="d-flex flex-wrap gap-2 mb-3"></div>

            <button class="btn btn-primary">Actualizar Auto</button>
            <a href="/clients/cars?id=<?= $client['id'] ?>" class="btn btn-secondary">Cancelar</a>

        </form>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function deletePhoto(photoId, clientId, carId) {

            const url = `/clients/cars/delete-photo?id=${photoId}&client_id=${clientId}&car_id=${carId}`;

            Swal.fire({
                title: '¿Estás seguro de eliminar esta foto?',
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

        function previewImages(event) {
            const preview = document.getElementById('preview');
            preview.innerHTML = '';

            const files = event.target.files;

            for (let i = 0; i < files.length; i++) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.width = 120;
                    img.classList.add('rounded', 'border');
                    preview.appendChild(img);
                }

                reader.readAsDataURL(files[i]);
            }
        }
    </script>

</body>

</html>