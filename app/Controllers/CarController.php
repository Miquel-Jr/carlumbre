<?php

namespace App\Controllers;

use App\Core\CloudinaryStorage;
use App\Core\Database;
use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\Car;
use App\Models\Client;
use App\Models\Quote;

class CarController
{
    protected const ERROR_PAGE = 'errors/nopage';
    protected const CARS_LISTING_URL = '/clients/cars?id=';
    protected const CARS_PATH = '/cars/';
    protected $clientModel;
    protected $carModel;
    protected $cloudinaryStorage;
    protected $quoteModel;
    public function __construct()
    {
        $this->clientModel = new Client();
        $this->carModel = new Car();
        $this->cloudinaryStorage = new CloudinaryStorage();
        $this->quoteModel = new Quote();
    }

    public function index()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_clients'))->handle();

        $id = $_GET['id'] ?? null;
        $client = $this->clientModel->find($id);
        if (!$client) {
            return view(self::ERROR_PAGE);
        }

        $cars = $this->carModel->getByClientId($id);

        foreach ($cars as &$car) {
            $car['photos'] = $this->carModel->getPhotos($car['id']);
        }
        unset($car);

        $quotes = $this->quoteModel->getByClientId($id);

        return view('clients/cars/index', [
            'client' => $client,
            'cars' => $cars,
            'quotes' => $quotes,
        ]);
    }


    public function create()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_clients'))->handle();

        $clientId = $_GET['client_id'] ?? null;
        $client = $this->clientModel->find($clientId);
        if (!$client) {
            return view(self::ERROR_PAGE);
        }

        return view('clients/cars/create', [
            'client' => $client
        ]);
    }

    public function store()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_clients'))->handle();

        $pdo = Database::connect();

        $client_id = $_POST['client_id'] ?? null;
        $brand = trim($_POST['brand'] ?? '');
        $model = trim($_POST['model'] ?? '');
        $year = $_POST['year'] ?? null;
        $plate = trim($_POST['plate'] ?? '');
        $color = trim($_POST['color'] ?? '');

        if (!$client_id || !$brand || !$model) {
            $_SESSION['error'] = 'Marca y modelo son obligatorios.';
            return redirect(self::CARS_LISTING_URL . $client_id);
        }

        $stmt = $pdo->prepare('
            INSERT INTO cars (client_id, brand, model, year, plate, color, created_at, updated_at)
            VALUES (?,?,?,?,?,?,NOW(),NOW())
        ');

        $stmt->execute([
            $client_id,
            $brand,
            $model,
            $year ?: null,
            $plate ?: null,
            $color ?: null
        ]);

        $car_id = $pdo->lastInsertId();

        $cloudinaryFolder = 'carlumbre/clients/' . $client_id . self::CARS_PATH . $car_id;

        if (!empty($_FILES['photos']['name'][0])) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/jpg'];

            foreach ($_FILES['photos']['tmp_name'] as $key => $tmpName) {
                if ($_FILES['photos']['error'][$key] === 0) {
                    $fileType = mime_content_type($tmpName);

                    // Validar tipo
                    if (!in_array($fileType, $allowedTypes)) {
                        continue;  // Ignorar archivos no válidos
                    }

                    // Ruta que se guarda en BD (pública)
                    $dbPath = $this->cloudinaryStorage->uploadImage($tmpName, $cloudinaryFolder);

                    if (!$dbPath) {
                        continue;
                    }

                    $stmt = $pdo->prepare('
                    INSERT INTO car_photos (car_id, photo_path, created_at)
                    VALUES (?,?,NOW())
                ');

                    $stmt->execute([$car_id, $dbPath]);
                }
            }
        }

        $_SESSION['success'] = 'Auto registrado correctamente con imágenes.';
        return redirect(self::CARS_LISTING_URL . $client_id);
    }

    public function edit()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_clients'))->handle();

        $id = $_GET['id'] ?? null;
        $clientId = $_GET['client_id'] ?? null;

        $client = $this->clientModel->find($clientId);
        if (!$client) {
            return view(self::ERROR_PAGE);
        }

        $car = $this->carModel->find($id);
        if (!$car) {
            return view(self::ERROR_PAGE);
        }

        $photos = $this->carModel->getPhotos($id);

        return view('clients/cars/edit', [
            'car' => $car,
            'client' => $client,
            'photos' => $photos,
        ]);
    }

    public function update()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_clients'))->handle();

        $pdo = Database::connect();

        $car_id    = $_POST['car_id'] ?? null;
        $client_id = $_POST['client_id'] ?? null;

        $brand = trim($_POST['brand'] ?? '');
        $model = trim($_POST['model'] ?? '');
        $year  = $_POST['year'] ?? null;
        $plate = trim($_POST['plate'] ?? '');
        $color = trim($_POST['color'] ?? '');

        if (!$car_id || !$brand || !$model) {
            $_SESSION['error'] = 'Marca y modelo son obligatorios.';
            return redirect('/cars/edit?id=' . $car_id);
        }

        $stmt = $pdo->prepare("
            UPDATE cars 
            SET brand = ?, model = ?, year = ?, plate = ?, color = ?, updated_at = NOW()
            WHERE id = ?
        ");

        $stmt->execute([
            $brand,
            $model,
            $year ?: null,
            $plate ?: null,
            $color ?: null,
            $car_id
        ]);

        if (!empty($_FILES['photos']['name'][0])) {

            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

            $cloudinaryFolder = 'carlumbre/clients/' . $client_id . '/cars/' . $car_id;

            foreach ($_FILES['photos']['tmp_name'] as $key => $tmpName) {

                if ($_FILES['photos']['error'][$key] === 0) {

                    $fileType = mime_content_type($tmpName);

                    if (!in_array($fileType, $allowedTypes)) {
                        continue;
                    }

                    // Limite 5MB
                    if ($_FILES['photos']['size'][$key] > 5 * 1024 * 1024) {
                        continue;
                    }

                    $dbPath = $this->cloudinaryStorage->uploadImage($tmpName, $cloudinaryFolder);

                    if (!$dbPath) {
                        continue;
                    }

                    $this->carModel->addPhoto($car_id, $dbPath);
                }
            }
        }

        $_SESSION['success'] = 'Auto actualizado correctamente.';
        return redirect(self::CARS_LISTING_URL . $client_id);
    }

    public function delete()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_clients'))->handle();

        $id = $_GET['id'] ?? null;
        $clientId = $_GET['client_id'] ?? null;

        $car = $this->carModel->find($id);
        if (!$car) {
            return view(self::ERROR_PAGE);
        }

        $this->carModel->delete($id);

        $_SESSION['success'] = 'Auto eliminado correctamente.';

        $plates = $_GET['plate'] ?? null;
        if ($plates) {
          return redirect('/clients/cars/plates');
        }
        return redirect(self::CARS_LISTING_URL . $clientId);
    }

    public function plates()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_clients'))->handle();

        $search = $_GET['search'] ?? null;
        $carsWithPlates = $this->carModel->getCarsWithPlates($search);

        foreach ($carsWithPlates as &$car) {
            $car['photos'] = $this->carModel->getPhotos($car['car_id']);
        }
        unset($car);

        return view('clients/cars/plates', [
            'cars' => $carsWithPlates,
        ]);
    }

    public function quotes()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_clients'))->handle();

        $carId = $_GET['car_id'] ?? null;
        if (!$carId) {
            return view(self::ERROR_PAGE);
        }
        $car = $this->carModel->find($carId);
        $photos = $this->carModel->getPhotos($carId);
        $quotes = $this->quoteModel->getByCarId($carId);

        return view('clients/cars/quotes', [
            'car' => $car,
            'photos' => $photos,
            'quotes' => $quotes,
        ]);
    }
}