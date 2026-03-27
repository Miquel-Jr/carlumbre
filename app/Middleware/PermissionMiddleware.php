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
    if (empty($_SESSION['user'])) {
      redirect('/');
    }

    if (!in_array($this->permission, $_SESSION['user']['permissions'])) {
      http_response_code(403);
      $backUrl = $_SERVER['HTTP_REFERER'] ?? firstAccessibleUrl('/dashboard');

      view('errors/forbidden', [
        'backUrl' => $backUrl,
      ]);

      exit;
    }
  }
}
