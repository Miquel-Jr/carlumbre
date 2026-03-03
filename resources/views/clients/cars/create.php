<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Agregar Auto | Carlumbre</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <?php view('partials/menu'); ?>

    <div class="container mt-5">

        <h2>Agregar Auto a <?= htmlspecialchars($client['name']) ?></h2>

        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['error'];
                unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form action="/clients/cars/store" method="POST" enctype="multipart/form-data">

            <input type="hidden" name="client_id" value="<?= $client['id'] ?>">

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Marca</label>
                    <input type="text" name="brand" class="form-control" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Modelo</label>
                    <input type="text" name="model" class="form-control" required>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Año</label>
                    <input type="number" name="year" class="form-control">
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Placa</label>
                    <input type="text" name="plate" class="form-control">
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Color</label>
                    <input type="text" name="color" class="form-control">
                </div>

                <div class="col-12 mb-3">
                    <label class="form-label">Imágenes del Auto</label>
                    <input type="file" name="photos[]" class="form-control" multiple accept="image/*"
                        onchange="previewImages(event)">
                </div>

                <div class="col-12 mb-3">
                    <div id="preview" class="d-flex flex-wrap gap-2"></div>
                </div>

            </div>

            <button class="btn btn-success">Guardar Auto</button>
            <a href="/clients/cars?id=<?= $client['id'] ?>" class="btn btn-secondary">Cancelar</a>

        </form>
    </div>

    <script>
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