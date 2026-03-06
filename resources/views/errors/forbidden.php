<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>403 - Sin permisos</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
  body {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    background: #f8f9fa;
    text-align: center;
    padding: 1rem;
  }

  .card-box {
    max-width: 560px;
    width: 100%;
  }
  </style>
</head>

<body>
  <div class="card shadow-sm card-box">
    <div class="card-body p-4 p-md-5">
      <h1 class="display-5 mb-3">403</h1>
      <p class="lead mb-2">No tienes permisos para ver esta sección.</p>
      <p class="text-muted mb-4">Si necesitas acceso, solicita permisos al administrador.</p>

      <a href="<?= htmlspecialchars($backUrl ?? '/dashboard') ?>" class="btn btn-primary"
        onclick="if (window.history.length > 1) { event.preventDefault(); window.history.back(); }">
        Volver
      </a>
    </div>
  </div>
</body>

</html>
