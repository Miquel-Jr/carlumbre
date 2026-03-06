<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Roles y Permisos | Carlumbre</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

  <?php view('partials/menu'); ?>

  <div class="container mt-5 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="mb-0">Roles y permisos</h1>
      <a href="/users" class="btn btn-secondary">Volver a usuarios</a>
    </div>

    <p class="text-muted">
      Configura aquí qué puede ver cada rol. Los usuarios heredarán automáticamente estos permisos según su rol.
    </p>

    <?php if (empty($roles)): ?>
    <div class="alert alert-info">No hay roles registrados.</div>
    <?php else: ?>
    <?php foreach ($roles as $role): ?>
    <?php $roleId = (int) ($role['id'] ?? 0); ?>
    <?php $selectedPermissionIds = $rolePermissionIdsMap[$roleId] ?? []; ?>

    <div class="card mb-4">
      <div class="card-header">
        <strong><?= htmlspecialchars($role['name'] ?? 'Rol') ?></strong>
      </div>
      <div class="card-body">
        <form method="POST" action="/users/roles/update">
          <input type="hidden" name="role_id" value="<?= $roleId ?>">

          <div class="row g-3">
            <?php if (!empty($permissions)): ?>
            <?php foreach ($permissions as $permission): ?>
            <?php $permissionId = (int) ($permission['id'] ?? 0); ?>
            <div class="col-md-6 col-lg-4">
              <div class="form-check border rounded p-2 h-100">
                <input class="form-check-input" type="checkbox" name="permission_ids[]" value="<?= $permissionId ?>"
                  id="role_<?= $roleId ?>_permission_<?= $permissionId ?>"
                  <?= in_array($permissionId, $selectedPermissionIds, true) ? 'checked' : '' ?>>
                <label class="form-check-label" for="role_<?= $roleId ?>_permission_<?= $permissionId ?>">
                  <div><?= htmlspecialchars($permission['label'] ?? $permission['name'] ?? '') ?></div>
                </label>
              </div>
            </div>
            <?php endforeach; ?>
            <?php else: ?>
            <div class="col-12">
              <div class="text-muted">No hay permisos registrados.</div>
            </div>
            <?php endif; ?>
          </div>

          <div class="mt-3">
            <button type="submit" class="btn btn-primary">Guardar permisos del rol</button>
          </div>
        </form>
      </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>

</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php view('partials/sweetalert'); ?>

</html>
