<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Actualizar Servicio | Carlumbre</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

  <?php view('partials/menu'); ?>

  <div class="container mt-5">
    <h1>Actualizar Servicio</h1>


    <form action="/services/update" method="POST">

      <input type="hidden" name="id" value="<?= $service['id'] ?? '' ?>">

      <div class="mb-3">
        <label for="name" class="form-label">Nombre del Servicio *</label>
        <input type="text" id="name" name="name" class="form-control" value="<?= $service['name'] ?? '' ?>" required
          placeholder="Ej: Cambio de aceite">
      </div>

      <div class="mb-3">
        <label for="description" class="form-label">Descripción</label>
        <textarea id="description" name="description" class="form-control" rows="3"
          placeholder="Detalle del servicio..."><?= $service['description'] ?? '' ?></textarea>
      </div>

      <div class="mb-3">
        <label for="price" class="form-label">Precio Base *</label>
        <div class="input-group">
          <span class="input-group-text">S/</span>
          <input type="number" id="price" name="price" step="0.01" min="0" class="form-control"
            value="<?= $service['price'] ?? '' ?>" required>
        </div>
      </div>

      <div class="mb-3">
        <label for="status" class="form-label">Estado</label>
        <select id="status" name="status" class="form-select">
          <option value="1" <?= ($service['status'] ?? 1) == 1 ? 'selected' : '' ?>>Activo</option>
          <option value="0" <?= ($service['status'] ?? 1) == 0 ? 'selected' : '' ?>>Inactivo</option>
        </select>
      </div>

      <div class="mb-3">
        <label for="hasWarranty" class="form-label">¿Tiene garantía?</label>
        <select name="has_warranty" id="hasWarranty" class="form-select">
          <option value="0" <?= ($service['has_warranty'] ?? 0) == 0 ? 'selected' : '' ?>>No</option>
          <option value="1" <?= ($service['has_warranty'] ?? 0) == 1 ? 'selected' : '' ?>>Sí</option>
        </select>
      </div>

      <div class="mb-3">
        <label for="warrantyTimeBase" class="form-label">Tiempo base de garantía (Meses) - opcional</label>
        <input type="number" name="warranty_time_base" id="warrantyTimeBase" min="1" class="form-control"
          placeholder="Ej: 12" value="<?= htmlspecialchars($service['warranty_time_base'] ?? '') ?>"
          <?= ($service['has_warranty'] ?? 0) == 1 ? '' : 'disabled' ?>>
      </div>

      <button class="btn btn-warning">Actualizar Servicio</button>
      <a href="/services" class="btn btn-secondary">Volver</a>

    </form>

  </div>

</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php view('partials/sweetalert'); ?>
<script>
(function() {
  const hasWarrantySelect = document.getElementById('hasWarranty');
  const warrantyTimeBaseInput = document.getElementById('warrantyTimeBase');

  if (!hasWarrantySelect || !warrantyTimeBaseInput) {
    return;
  }

  const toggleWarrantyInput = () => {
    const enabled = hasWarrantySelect.value === '1';
    warrantyTimeBaseInput.disabled = !enabled;

    if (!enabled) {
      warrantyTimeBaseInput.value = '';
    }
  };

  hasWarrantySelect.addEventListener('change', toggleWarrantyInput);
  toggleWarrantyInput();
})();
</script>

</html>
