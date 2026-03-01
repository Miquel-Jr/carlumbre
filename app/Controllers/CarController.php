<?php

namespace App\Controllers;

use App\Core\Database;
use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\Car;
use App\Models\Client;

class CarController
{
    protected $clientModel;
    protected $carModel;

    public function __construct()
    {
        $this->clientModel = new Client();
        $this->carModel = new Car();
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

        foreach ($cars as &$car) {
            $car['photos'] = $this->carModel->getPhotos($car['id']);
        }
        unset($car); // buena práctica al usar referencias

        return view('clients/cars/index', [
            'client' => $client,
            'cars' => $cars,
        ]);
    }


    public function create()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_clients'))->handle();

        $clientId = $_GET['client_id'] ?? null;
        $client = $this->clientModel->find($clientId);
        if (!$client) {
            return view('errors/nopage');
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
            return redirect('/clients/cars?id=' . $client_id);
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

        $baseDir = __DIR__ . '/../../public/uploads/clients/' . $client_id . '/cars/' . $car_id . '/';

        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0777, true);
        }

        if (!empty($_FILES['photos']['name'][0])) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/jpg'];

            foreach ($_FILES['photos']['tmp_name'] as $key => $tmpName) {
                if ($_FILES['photos']['error'][$key] === 0) {
                    $fileType = mime_content_type($tmpName);

                    // Validar tipo
                    if (!in_array($fileType, $allowedTypes)) {
                        continue;  // Ignorar archivos no válidos
                    }

                    // Generar nombre único
                    $extension = pathinfo($_FILES['photos']['name'][$key], PATHINFO_EXTENSION);
                    $fileName = uniqid() . '.' . $extension;

                    $fullPath = $baseDir . $fileName;

                    move_uploaded_file($tmpName, $fullPath);

                    // Ruta que se guarda en BD (pública)
                    $dbPath = '/uploads/clients/' . $client_id . '/cars/' . $car_id . '/' . $fileName;

                    $stmt = $pdo->prepare('
                    INSERT INTO car_photos (car_id, photo_path, created_at)
                    VALUES (?,?,NOW())
                ');

                    $stmt->execute([$car_id, $dbPath]);
                }
            }
        }

        $_SESSION['success'] = 'Auto registrado correctamente con imágenes.';
        return redirect('/clients/cars?id=' . $client_id);
    }

    public function edit()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_clients'))->handle();

        $id = $_GET['id'] ?? null;
        $clientId = $_GET['client_id'] ?? null;

        $client = $this->clientModel->find($clientId);
        if (!$client) {
            return view('errors/nopage');
        }

        $car = $this->carModel->find($id);
        if (!$car) {
            return view('errors/nopage');
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

            $baseDir = __DIR__ . '/../../public/uploads/clients/' . $client_id . '/cars/' . $car_id . '/';

            if (!is_dir($baseDir)) {
                mkdir($baseDir, 0777, true);
            }

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

                    $extension = pathinfo($_FILES['photos']['name'][$key], PATHINFO_EXTENSION);
                    $fileName  = uniqid() . '.' . $extension;

                    $fullPath  = $baseDir . $fileName;

                    move_uploaded_file($tmpName, $fullPath);

                    $dbPath = '/uploads/clients/' . $client_id . '/cars/' . $car_id . '/' . $fileName;
                    $this->carModel->addPhoto($car_id, $dbPath);
                }
            }
        }

        $_SESSION['success'] = 'Auto actualizado correctamente.';
        return redirect('/clients/cars?id=' . $client_id);
    }
}
