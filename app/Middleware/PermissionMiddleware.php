<?php

namespace App\Middleware;

class PermissionMiddleware
{
  protected string $permission;

  public function __construct(string $permission)
  {
    $this->permission = $permission;
  }

  public function handle()
  {
    if (
      empty($_SESSION['user']) ||
      !in_array($this->permission, $_SESSION['user']['permissions'])
    ) {
      http_response_code(403);
      echo '⛔ No tienes permiso.';
      exit;
    }
  }
}
