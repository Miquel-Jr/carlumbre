<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Usuarios | Carlumbre</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

  <?php view('partials/menu'); ?>

  <div class="container mt-5">
    <h1 class="mb-4">Usuarios</h1>

    <div class="d-flex justify-content-between mb-3">
      <form method="GET" action="/users" class="d-flex">
        <input type="text" name="search" class="form-control me-2" placeholder="Buscar usuario..." data-debounce-search
          data-debounce-ms="500" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        <button class="btn btn-primary">Buscar</button>
        <button class="btn btn-secondary" type="button" onclick="window.location='/users'">Limpiar</button>
      </form>

      <div class="d-flex gap-2">
        <a href="/users/roles" class="btn btn-outline-primary">Roles y permisos</a>
        <a href="/users/create" class="btn btn-success">+ Nuevo Usuario</a>
      </div>
    </div>

    <table class="table table-striped table-bordered table-mobile-cards">
      <thead class="table-dark">
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>Correo</th>
          <th>Rol</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($users)): ?>
        <?php foreach ($users as $user): ?>
        <tr>
          <td><?= (int) ($user['id'] ?? 0) ?></td>
          <td><?= htmlspecialchars($user['name'] ?? '') ?></td>
          <td><?= htmlspecialchars($user['email'] ?? '') ?></td>
          <td><?= htmlspecialchars($user['role_name'] ?? 'Sin rol') ?></td>
          <td>
            <a href="/users/edit?id=<?= (int) ($user['id'] ?? 0) ?>" class="btn btn-sm btn-warning">Editar</a>
            <button type="button" class="btn btn-sm btn-danger" onclick="deleteUser(<?= (int) ($user['id'] ?? 0) ?>)">
              Eliminar
            </button>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php else: ?>
        <tr>
          <td colspan="6" class="text-center">No hay usuarios registrados.</td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php view('partials/sweetalert'); ?>
<?php view('partials/mobile-table-cards'); ?>
<?php view('partials/debounced-search'); ?>
<script>
function deleteUser(id) {
  Swal.fire({
    title: '¿Eliminar usuario?',
    text: 'Esta acción no se puede revertir.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = `/users/delete?id=${id}`;
    }
  });
}
</script>

</html>
