<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Autopartes | Carlumbre</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script type="module" src="https://unpkg.com/@google/model-viewer/dist/model-viewer.min.js"></script>
  <style>
  .part-image-2d {
    width: 100%;
    height: 240px;
    object-fit: cover;
    border-radius: 0.5rem;
    border: 1px solid #dee2e6;
    cursor: zoom-in;
  }

  model-viewer {
    width: 100%;
    height: 240px;
    background: #f8f9fa;
    border-radius: 0.5rem;
    border: 1px solid #dee2e6;
  }

  .part-view-3d {
    cursor: zoom-in;
  }

  #previewModal .modal-content {
    background: #111;
    color: #fff;
  }

  .preview-stage {
    width: 100%;
    height: calc(100vh - 120px);
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .preview-image {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
  }

  .preview-model {
    width: 100%;
    height: 100%;
    background: #111;
    border: 1px solid #2b2b2b;
    border-radius: 0.5rem;
  }
  </style>
</head>

<body>
  <?php view('partials/menu'); ?>

  <div class="container mt-5 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="mb-0">Autopartes</h1>
      <div class="d-flex gap-2">
        <a href="/products/create" class="btn btn-success">Nuevo producto</a>
      </div>
    </div>

    <form method="GET" action="/products" class="d-flex gap-2 mb-4">
      <select name="category" class="form-select" style="min-width: 180px;">
        <option value="">Todas las categorías</option>
        <?php foreach (($categories ?? []) as $categoryOption): ?>
        <option value="<?= htmlspecialchars($categoryOption) ?>"
          <?= ($selectedCategory ?? '') === $categoryOption ? 'selected' : '' ?>>
          <?= htmlspecialchars($categoryOption) ?>
        </option>
        <?php endforeach; ?>
      </select>
      <input type="text" name="search" class="form-control" placeholder="Buscar autoparte..." data-debounce-search
        data-debounce-ms="500" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
      <button class="btn btn-primary">Buscar</button>
      <button class="btn btn-secondary" type="button" onclick="window.location='/products'">Limpiar</button>
    </form>

    <div class="row g-4">
      <?php if (!empty($parts)): ?>
      <?php foreach ($parts as $part): ?>
      <div class="col-lg-4 col-md-6">
        <div class="card h-100 shadow-sm">
          <div class="card-body d-flex flex-column gap-3">
            <h5 class="card-title mb-0"><?= htmlspecialchars($part['name']) ?></h5>
            <p class="card-text text-muted mb-0"><?= htmlspecialchars($part['description']) ?></p>
            <div class="d-flex gap-2">
              <a href="/products/edit?id=<?= (int) $part['id'] ?>" class="btn btn-outline-primary btn-sm">Editar</a>
              <a href="/products/delete?id=<?= (int) $part['id'] ?>" class="btn btn-outline-danger btn-sm js-delete-product"
                data-product-name="<?= htmlspecialchars($part['name']) ?>">Eliminar</a>
            </div>

            <div>
              <h6 class="mb-2">Vista 2D</h6>
              <?php if (!empty($part['image_2d'])): ?>
              <img src="<?= htmlspecialchars($part['image_2d']) ?>"
                alt="Imagen 2D de <?= htmlspecialchars($part['name']) ?>" class="part-image-2d preview-trigger"
                data-preview-type="2d" data-preview-src="<?= htmlspecialchars($part['image_2d']) ?>"
                data-preview-title="<?= htmlspecialchars($part['name']) ?> - Vista 2D">
              <?php else: ?>
              <div class="alert alert-light border mb-0">Imagen 2D no disponible.</div>
              <?php endif; ?>
            </div>

            <div>
              <h6 class="mb-2">Vista 3D</h6>
              <?php if (!empty($part['model_3d'])): ?>
              <model-viewer src="<?= htmlspecialchars($part['model_3d']) ?>"
                alt="Modelo 3D de <?= htmlspecialchars($part['name']) ?>" class="part-view-3d preview-trigger"
                data-preview-type="3d" data-preview-src="<?= htmlspecialchars($part['model_3d']) ?>"
                data-preview-title="<?= htmlspecialchars($part['name']) ?> - Vista 3D" camera-controls auto-rotate
                shadow-intensity="1">
              </model-viewer>
              <?php else: ?>
              <div class="alert alert-light border mb-0">Modelo 3D no disponible.</div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
      <?php else: ?>
      <div class="col-12">
        <div class="alert alert-info mb-0">No hay autopartes registradas.</div>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
      <div class="modal-content">
        <div class="modal-header border-secondary">
          <h5 class="modal-title" id="previewModalTitle">Vista previa</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="preview-stage" id="previewStage"></div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <?php view('partials/debounced-search'); ?>
  <?php view('partials/sweetalert'); ?>
  <script>
  const previewModalElement = document.getElementById('previewModal');
  const previewModalTitle = document.getElementById('previewModalTitle');
  const previewStage = document.getElementById('previewStage');
  const previewModal = new bootstrap.Modal(previewModalElement);

  function openPreview(type, src, title) {
    previewModalTitle.textContent = title || 'Vista previa';
    previewStage.innerHTML = '';

    if (type === '3d') {
      const model = document.createElement('model-viewer');
      model.setAttribute('src', src);
      model.setAttribute('camera-controls', '');
      model.setAttribute('auto-rotate', '');
      model.setAttribute('shadow-intensity', '1');
      model.className = 'preview-model';
      previewStage.appendChild(model);
    } else {
      const image = document.createElement('img');
      image.src = src;
      image.alt = title || 'Vista 2D';
      image.className = 'preview-image';
      previewStage.appendChild(image);
    }

    previewModal.show();
  }

  document.querySelectorAll('.preview-trigger').forEach((element) => {
    element.addEventListener('click', () => {
      openPreview(
        element.getAttribute('data-preview-type'),
        element.getAttribute('data-preview-src'),
        element.getAttribute('data-preview-title')
      );
    });
  });

  (function() {
    const searchForm = document.querySelector('form[action="/products"]');
    const categorySelect = searchForm?.querySelector('select[name="category"]');

    if (!searchForm) {
      return;
    }

    if (categorySelect) {
      categorySelect.addEventListener('change', function() {
        searchForm.submit();
      });
    }
  })();

  (function() {
    document.querySelectorAll('.js-delete-product').forEach((link) => {
      link.addEventListener('click', function(event) {
        event.preventDefault();

        const deleteUrl = this.getAttribute('href');
        const productName = this.getAttribute('data-product-name') || 'este producto';

        if (typeof Swal === 'undefined') {
          window.location.href = deleteUrl;
          return;
        }

        Swal.fire({
          title: '¿Eliminar producto?',
          html: `Se eliminará <strong>${productName}</strong>. Esta acción no se puede deshacer.`,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Sí, eliminar',
          cancelButtonText: 'Cancelar',
          confirmButtonColor: '#dc3545'
        }).then((result) => {
          if (result.isConfirmed) {
            window.location.href = deleteUrl;
          }
        });
      });
    });
  })();
  </script>
</body>

</html>
