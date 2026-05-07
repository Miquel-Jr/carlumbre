<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Login | Carlumbre</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Estilos personalizados -->
  <style>
  body {
    background: linear-gradient(135deg, #1f1f1f, #2c2c2c);
    height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .login-card {
    width: 380px;
    border-radius: 15px;
  }

  .brand {
    font-weight: bold;
    font-size: 22px;
    color: #f4b400;
  }
  </style>
</head>

<body>

  <div class="card shadow-lg login-card p-4">
    <div class="text-center mb-4">
      <div class="brand">🔧 Carlumbre</div>
      <small class="text-muted">Panel Administrativo</small>
    </div>

    <?php if (!empty($_SESSION['error'])): ?>
    <div class="alert alert-danger">
      <?= $_SESSION['error']; ?>
    </div>
    <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_GET['timeout'])): ?>
    <div class="alert alert-warning">
      <?php
            if ($_GET['timeout'] == 1)
                echo 'Tu sesión expiró por inactividad.';
            else
                echo 'Debes iniciar sesión para continuar.';
            ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="/login">
      <div class="mb-3">
        <label class="form-label">Correo electrónico</label>
        <input type="email" name="email" class="form-control" placeholder="admin@carlumbre.pe" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Contraseña</label>
        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
      </div>

      <button class="btn btn-warning w-100">
        Ingresar
      </button>
    </form>
  </div>

</body>

</html>