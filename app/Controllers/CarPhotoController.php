<?php

namespace App\Controllers;

use App\Core\CloudinaryStorage;
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
  protected $cloudinaryStorage;

  public function __construct()
  {
    $this->clientModel = new Client();
    $this->carModel = new Car();
    $this->carPhotoModel = new CarPhoto();
    $this->cloudinaryStorage = new CloudinaryStorage();
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
      if ($this->deletePhotoFile($photo)) {
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

  private function deletePhotoFile($photo)
  {
    $photoPath = (string) ($photo['photo_path'] ?? '');

    if ($this->isCloudinaryUrl($photoPath)) {
      if ($this->cloudinaryStorage->isEnabled()) {
        $this->cloudinaryStorage->deleteByUrl($photoPath);
      }

      return true;
    }

    $filePath = $this->buildLocalPhotoFilePath($photoPath);

    if (is_file($filePath) && !@unlink($filePath)) {
      $_SESSION['error'] = 'No se pudo eliminar el archivo físico de la imagen.';
      return false;
    }

    return true;
  }

  private function buildLocalPhotoFilePath($photoPath)
  {
    $publicPath = realpath(__DIR__ . '/../../public');
    $relativePhotoPath = ltrim((string) $photoPath, '/\\');
    return $publicPath . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePhotoPath);
  }

  private function isCloudinaryUrl($photoPath)
  {
    if (!is_string($photoPath) || $photoPath === '') {
      return false;
    }

    $host = parse_url($photoPath, PHP_URL_HOST);
    if (!is_string($host) || $host === '') {
      return false;
    }

    return stripos($host, 'res.cloudinary.com') !== false;
  }
}
