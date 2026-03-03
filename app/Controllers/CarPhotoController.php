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

    $redirectUrl = $this->performPhotoDelete($photo_id, $car_id, $client_id);
    return redirect($redirectUrl);
  }

  private function performPhotoDelete($photo_id, $car_id, $client_id)
  {
    $redirectUrl = '/clients/cars/edit?id=' . $car_id . '&client_id=' . $client_id;

    if (!$this->validateDeleteParameters($photo_id, $car_id, $client_id)) {
      $_SESSION['error'] = 'Parámetros incompletos para eliminar la imagen.';
    } elseif (!$photo = $this->carPhotoModel->find($photo_id)) {
      $_SESSION['error'] = 'La imagen no existe o ya fue eliminada.';
    } else {
      $filePath = $this->buildPhotoFilePath($photo);
      if ($this->deletePhotoFile($filePath)) {
        if (!$this->carPhotoModel->delete($photo_id)) {
          $_SESSION['error'] = 'No se pudo eliminar el registro de la imagen en la base de datos.';
        } else {
          $_SESSION['success'] = 'Imagen eliminada correctamente.';
        }
      }
    }

    return $redirectUrl;
  }

  private function validateDeleteParameters($photo_id, $car_id, $client_id)
  {
    return $photo_id && $car_id && $client_id;
  }

  private function buildPhotoFilePath($photo)
  {
    $publicPath = realpath(__DIR__ . '/../../public');
    $relativePhotoPath = ltrim((string) ($photo['photo_path'] ?? ''), '/\\');
    return $publicPath . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePhotoPath);
  }

  private function deletePhotoFile($filePath)
  {
    if (is_file($filePath) && !@unlink($filePath)) {
      $_SESSION['error'] = 'No se pudo eliminar el archivo físico de la imagen.';
      return false;
    }
    return true;
  }
}
