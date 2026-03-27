<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Nuevo producto | Carlumbre</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
  <?php view('partials/menu'); ?>

  <div class="container mt-5 mb-5" style="max-width: 900px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="mb-0">Nuevo producto</h1>
      <a href="/products" class="btn btn-outline-secondary">Volver</a>
    </div>

    <div class="card shadow-sm border-0">
      <div class="card-body">
        <form action="/products/store" method="POST" enctype="multipart/form-data" class="row g-3">
          <div class="col-md-6">
            <label class="form-label" for="name">Nombre *</label>
            <input type="text" name="name" id="name" class="form-control" required>
          </div>

          <div class="col-md-6">
            <label class="form-label" for="category">Categoría</label>
            <input type="text" name="category" id="category" class="form-control" placeholder="Ej: Motor, Frenos, Suspensión">
          </div>

          <div class="col-12">
            <label class="form-label" for="description">Descripción</label>
            <textarea name="description" id="description" class="form-control" rows="3"></textarea>
          </div>

          <div class="col-12">
            <label class="form-label" for="model_3d">URL modelo 3D (opcional)</label>
            <input type="url" name="model_3d" id="model_3d" class="form-control" placeholder="https://.../modelo.glb">
          </div>

          <div class="col-12">
            <label class="form-label" for="image_2d">Imagen 2D (Cloudinary) *</label>
            <input type="file" name="image_2d" id="image_2d" class="form-control" accept="image/png,image/jpeg,image/jpg,image/webp,image/gif" required>
            <small class="text-muted">Formatos permitidos: JPG, PNG, GIF o WEBP. Máximo 5MB.</small>
          </div>

          <div class="col-12 text-end">
            <a href="/products" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Guardar producto</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <?php view('partials/sweetalert'); ?>
</body>

</html>
