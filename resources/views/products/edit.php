<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Editar producto | Carlumbre</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
  <?php view('partials/menu'); ?>

  <div class="container mt-5 mb-5" style="max-width: 900px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="mb-0">Editar producto</h1>
      <a href="/products" class="btn btn-outline-secondary">Volver</a>
    </div>

    <div class="card shadow-sm border-0">
      <div class="card-body">
        <form action="/products/update" method="POST" enctype="multipart/form-data" class="row g-3">
          <input type="hidden" name="id" value="<?= (int) ($product['id'] ?? 0) ?>">

          <div class="col-md-6">
            <label class="form-label" for="name">Nombre *</label>
            <input type="text" name="name" id="name" class="form-control" value="<?= htmlspecialchars($product['name'] ?? '') ?>" required>
          </div>

          <div class="col-md-6">
            <label class="form-label" for="category">Categoría</label>
            <input type="text" name="category" id="category" class="form-control" value="<?= htmlspecialchars($product['category'] ?? '') ?>">
          </div>

          <div class="col-12">
            <label class="form-label" for="description">Descripción</label>
            <textarea name="description" id="description" class="form-control" rows="3"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
          </div>

          <div class="col-12">
            <label class="form-label" for="model_3d">URL modelo 3D (opcional)</label>
            <input type="url" name="model_3d" id="model_3d" class="form-control" value="<?= htmlspecialchars($product['model_3d'] ?? '') ?>" placeholder="https://.../modelo.glb">
          </div>

          <div class="col-12">
            <label class="form-label d-block">Imagen 2D actual</label>
            <?php if (!empty($product['image_2d'])): ?>
            <img src="<?= htmlspecialchars($product['image_2d']) ?>" alt="Imagen actual" class="img-thumbnail" style="max-height: 240px;">
            <?php else: ?>
            <div class="alert alert-light border mb-0">Este producto aún no tiene imagen 2D.</div>
            <?php endif; ?>
          </div>

          <div class="col-12">
            <label class="form-label" for="image_2d">Nueva imagen 2D (Cloudinary)</label>
            <input type="file" name="image_2d" id="image_2d" class="form-control" accept="image/png,image/jpeg,image/jpg,image/webp,image/gif">
            <small class="text-muted">Opcional. Si subes una nueva, reemplazará la imagen actual.</small>
          </div>

          <div class="col-12 text-end">
            <a href="/products" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Actualizar producto</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <?php view('partials/sweetalert'); ?>
</body>

</html>
