<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\Car;
use App\Models\Client;
use App\Models\CarPhoto;

class CarPhotoController
{
  protected $clientModel;
  protected $carModel;
  protected $carPhotoModel;

  public function __construct()
  {
    $this->clientModel = new Client();
    $this->carModel = new Car();
    $this->carPhotoModel = new CarPhoto();
  }

  public function index()
  {
    (new AuthMiddleware())->handle();
    (new PermissionMiddleware('view_clients'))->handle();

    $id = $_GET['id'] ?? null;
    $client = $this->clientModel->find($id);
    if (!$client) {
      return view('errors/nopage');
    }

    $cars = $this->carModel->getByClientId($id);

    return view('clients/cars/index', [
      'client' => $client,
      'cars' => $cars
    ]);
  }

  public function deletePhoto()
  {
    (new AuthMiddleware())->handle();
    (new PermissionMiddleware('view_clients'))->handle();

    $photo_id  = $_GET['id'] ?? null;
    $car_id    = $_GET['car_id'] ?? null;
    $client_id = $_GET['client_id'] ?? null;

    if (!$photo_id || !$car_id || !$client_id) {
      return redirect('/clients/cars/edit?id=' . $photo_id . '&client_id=' . $client_id);
    }

    $photo = $this->carPhotoModel->find($photo_id);

    if ($photo) {

      $filePath = __DIR__ . '/../../public' . $photo['photo_path'];

      if (file_exists($filePath)) {
        unlink($filePath);
      }

      $this->carPhotoModel->delete($photo_id);
    }

    $_SESSION['success'] = 'Imagen eliminada correctamente.';
    return redirect('/clients/cars/edit?id=' . $car_id . '&client_id=' . $client_id);
  }
}
