<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Crear Usuario | Carlumbre</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

  <?php view('partials/menu'); ?>

  <div class="container mt-5">
    <h1>Crear Usuario</h1>

    <form method="POST" action="/users/store" class="mt-4">
      <div class="mb-3">
        <label class="form-label">Nombre</label>
        <input type="text" name="name" class="form-control" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Correo</label>
        <input type="email" name="email" class="form-control" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Contraseña</label>
        <input type="password" name="password" class="form-control" minlength="8" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Rol</label>
        <select name="role_id" id="roleId" class="form-select" required>
          <option value="">Seleccione un rol</option>
          <?php foreach (($roles ?? []) as $role): ?>
          <option value="<?= (int) ($role['id'] ?? 0) ?>"><?= htmlspecialchars($role['name'] ?? '') ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Permisos del rol seleccionado</label>
        <ul class="list-group" id="rolePermissionsList">
          <li class="list-group-item text-muted">Selecciona un rol para ver sus permisos.</li>
        </ul>
      </div>

      <button class="btn btn-success">Guardar Usuario</button>
      <a href="/users" class="btn btn-secondary">Volver</a>
    </form>
  </div>

</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php view('partials/sweetalert'); ?>
<script>
(function() {
  const roleSelect = document.getElementById('roleId');
  const permissionsList = document.getElementById('rolePermissionsList');
  const rolePermissionsMap = <?= json_encode($rolePermissionsMap ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

  if (!roleSelect || !permissionsList) {
    return;
  }

  const renderPermissions = () => {
    const roleId = roleSelect.value;
    const permissions = rolePermissionsMap[roleId] || [];

    permissionsList.innerHTML = '';

    if (!permissions.length) {
      permissionsList.innerHTML = '<li class="list-group-item text-muted">El rol no tiene permisos asignados.</li>';
      return;
    }

    permissions.forEach((permission) => {
      const item = document.createElement('li');
      item.className = 'list-group-item';
      item.textContent = permission;
      permissionsList.appendChild(item);
    });
  };

  roleSelect.addEventListener('change', renderPermissions);
  renderPermissions();
})();
</script>

</html>
