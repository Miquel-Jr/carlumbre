<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\UserManagement;

class UserController
{
    private const ROUTE_INDEX = '/users';
    private const ROUTE_CREATE = '/users/create';
    private const ROUTE_EDIT = '/users/edit?id=';
    private const ROUTE_ROLES = '/users/roles';
    private const VIEW_INDEX = 'users/index';
    private const VIEW_CREATE = 'users/create';
    private const VIEW_EDIT = 'users/edit';
    private const VIEW_ROLES = 'users/roles';
    private const VIEW_NOT_FOUND = 'errors/nopage';

    private UserManagement $userModel;

    public function __construct()
    {
        $this->userModel = new UserManagement();
    }

    public function index()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_users'))->handle();

        $search = trim($_GET['search'] ?? '');
        $users = $this->userModel->all($search !== '' ? $search : null);

        foreach ($users as &$user) {
            $permissionsText = trim((string) ($user['role_permissions'] ?? ''));
            if ($permissionsText === '') {
                $user['role_permissions_readable'] = '';
                continue;
            }

            $permissions = array_map('trim', explode(',', $permissionsText));
            $permissions = array_values(array_filter($permissions, static fn($value) => $value !== ''));
            $permissions = array_map(static fn($permission) => permissionLabel($permission), $permissions);
            $user['role_permissions_readable'] = implode(', ', $permissions);
        }
        unset($user);

        return view(self::VIEW_INDEX, [
            'users' => $users,
        ]);
    }

    public function create()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_users'))->handle();

        $roles = $this->userModel->rolesWithPermissions();
        $rolePermissionsMap = $this->buildRolePermissionsMap($roles);

        return view(self::VIEW_CREATE, [
            'roles' => $roles,
            'rolePermissionsMap' => $rolePermissionsMap,
        ]);
    }

    public function store()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_users'))->handle();

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $roleId = (int) ($_POST['role_id'] ?? 0);

        if ($name === '' || $email === '' || $password === '' || $roleId <= 0) {
            $_SESSION['error'] = 'Nombre, correo, contraseña y rol son obligatorios.';
            return redirect(self::ROUTE_CREATE);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'El correo no tiene un formato válido.';
            return redirect(self::ROUTE_CREATE);
        }

        if (strlen($password) < 8) {
            $_SESSION['error'] = 'La contraseña debe tener al menos 8 caracteres.';
            return redirect(self::ROUTE_CREATE);
        }

        if (!$this->userModel->roleExists($roleId)) {
            $_SESSION['error'] = 'El rol seleccionado no es válido.';
            return redirect(self::ROUTE_CREATE);
        }

        $existing = $this->userModel->findByEmail($email);
        if ($existing) {
            $_SESSION['error'] = 'El correo ya está registrado.';
            return redirect(self::ROUTE_CREATE);
        }

        $this->userModel->create([
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role_id' => $roleId,
        ]);

        $_SESSION['success'] = 'Usuario registrado correctamente.';
        return redirect(self::ROUTE_INDEX);
    }

    public function edit()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_users'))->handle();

        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            return view(self::VIEW_NOT_FOUND);
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            return view(self::VIEW_NOT_FOUND);
        }

        $roles = $this->userModel->rolesWithPermissions();
        $rolePermissionsMap = $this->buildRolePermissionsMap($roles);

        return view(self::VIEW_EDIT, [
            'user' => $user,
            'roles' => $roles,
            'rolePermissionsMap' => $rolePermissionsMap,
        ]);
    }

    public function update()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_users'))->handle();

        $id = (int) ($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $roleId = (int) ($_POST['role_id'] ?? 0);

        if ($id <= 0 || $name === '' || $email === '' || $roleId <= 0) {
            $_SESSION['error'] = 'Nombre, correo y rol son obligatorios.';
            return redirect(self::ROUTE_EDIT . $id);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'El correo no tiene un formato válido.';
            return redirect(self::ROUTE_EDIT . $id);
        }

        if ($password !== '' && strlen($password) < 8) {
            $_SESSION['error'] = 'La nueva contraseña debe tener al menos 8 caracteres.';
            return redirect(self::ROUTE_EDIT . $id);
        }

        if (!$this->userModel->roleExists($roleId)) {
            $_SESSION['error'] = 'El rol seleccionado no es válido.';
            return redirect(self::ROUTE_EDIT . $id);
        }

        $existing = $this->userModel->findByEmailAndId($email, $id);
        if ($existing) {
            $_SESSION['error'] = 'El correo ya está registrado por otro usuario.';
            return redirect(self::ROUTE_EDIT . $id);
        }

        $currentUser = $this->userModel->find($id);
        if (!$currentUser) {
            $_SESSION['error'] = 'Usuario no encontrado.';
            return redirect(self::ROUTE_INDEX);
        }

        $this->userModel->update($id, [
            'name' => $name,
            'email' => $email,
            'password' => $password !== '' ? password_hash($password, PASSWORD_DEFAULT) : null,
            'role_id' => $roleId,
        ]);

        if ((int) ($_SESSION['user']['id'] ?? 0) === $id) {
            $_SESSION['user']['name'] = $name;
            $_SESSION['user']['role_id'] = $roleId;
            $_SESSION['user']['permissions'] = $this->userModel->permissionsByRoleId($roleId);
        }

        $_SESSION['success'] = 'Usuario actualizado correctamente.';
        return redirect(self::ROUTE_INDEX);
    }

    public function delete()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_users'))->handle();

        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            return view(self::VIEW_NOT_FOUND);
        }

        if ((int) ($_SESSION['user']['id'] ?? 0) === $id) {
            $_SESSION['error'] = 'No puedes eliminar tu propio usuario.';
            return redirect(self::ROUTE_INDEX);
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            $_SESSION['error'] = 'Usuario no encontrado.';
            return redirect(self::ROUTE_INDEX);
        }

        $this->userModel->delete($id);
        $_SESSION['success'] = 'Usuario eliminado correctamente.';
        return redirect(self::ROUTE_INDEX);
    }

    public function roles()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_users'))->handle();

        $roles = $this->userModel->roles();
        $permissions = $this->userModel->allPermissions();

        foreach ($permissions as &$permission) {
            $permission['label'] = permissionLabel((string) ($permission['name'] ?? ''));
        }
        unset($permission);

        $rolePermissionIdsMap = [];
        foreach ($roles as $role) {
            $rolePermissionIdsMap[$role['id']] = $this->userModel->permissionIdsByRoleId((int) $role['id']);
        }

        return view(self::VIEW_ROLES, [
            'roles' => $roles,
            'permissions' => $permissions,
            'rolePermissionIdsMap' => $rolePermissionIdsMap,
        ]);
    }

    public function updateRolePermissions()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_users'))->handle();

        $roleId = (int) ($_POST['role_id'] ?? 0);
        $permissionIds = $_POST['permission_ids'] ?? [];

        if ($roleId <= 0 || !$this->userModel->roleExists($roleId)) {
            $_SESSION['error'] = 'El rol seleccionado no es válido.';
            return redirect(self::ROUTE_ROLES);
        }

        if (!is_array($permissionIds)) {
            $permissionIds = [];
        }

        $this->userModel->syncRolePermissions($roleId, $permissionIds);

        if ((int) ($_SESSION['user']['role_id'] ?? 0) === $roleId) {
            $_SESSION['user']['permissions'] = $this->userModel->permissionsByRoleId($roleId);

            if (!in_array('view_users', $_SESSION['user']['permissions'], true)) {
                $_SESSION['success'] = 'Permisos del rol actualizados. Ya no tienes acceso a Usuarios.';
                return redirect('/dashboard');
            }
        }

        $_SESSION['success'] = 'Permisos del rol actualizados correctamente.';
        return redirect(self::ROUTE_ROLES);
    }

    private function buildRolePermissionsMap(array $roles): array
    {
        $map = [];

        foreach ($roles as $role) {
            $permissionsText = trim((string) ($role['permissions'] ?? ''));

            if ($permissionsText === '') {
                $map[$role['id']] = [];
                continue;
            }

            $permissions = array_map('trim', explode(',', $permissionsText));
            $permissions = array_values(array_filter($permissions, static fn($value) => $value !== ''));
            $permissions = array_map(static fn($permission) => permissionLabel($permission), $permissions);

            $map[$role['id']] = $permissions;
        }

        return $map;
    }
}
