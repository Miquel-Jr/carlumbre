<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\Car;
use App\Models\Client;

class ClientController
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
        $search = trim($_GET['search'] ?? '');
        $clients = $this->clientModel->all($search !== '' ? $search : null);
        return view('clients/index', ['menu' => menu(), 'clients' => $clients]);
    }

    public function create()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_clients'))->handle();
        return view('clients/create');
    }

    public function edit()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_clients'))->handle();
        $id = $_GET['id'] ?? null;

        if (!$id) {
            return view('error/nopage');
        }
        $client = $this->clientModel->find($id);
        return view('clients/edit', ['client' => $client]);
    }

    public function store()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_clients'))->handle();

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $document_type = $_POST['document_type'] ?? '';
        $document_number = trim($_POST['document_number'] ?? '');


        if (!$name || !$document_type || !$document_number) {
            $_SESSION['error'] = 'Nombre, tipo de documento y número de documento son obligatorios.';
            return redirect('/clients/create');
        }

        // Validar email duplicado
        if ($email) {
            $searchEmail = $this->clientModel->findByEmail($email);
            if ($searchEmail) {
                $_SESSION['error'] = 'Este correo ya está registrado.';
                return redirect('/clients/create');
            }
        }

        $searchDocument = $this->clientModel->findByDocument($document_type, $document_number);

        if ($searchDocument) {
            $_SESSION['error'] = 'Este número de documento ya está registrado para el tipo seleccionado.';
            return redirect('/clients/create');
        }

        // Validar teléfono duplicado (si se ingresó)
        if ($phone) {
            $searchPhone = $this->clientModel->findByPhone($phone);
            if ($searchPhone) {
                $_SESSION['error'] = 'Este número de teléfono ya está registrado.';
                return redirect('/clients/create');
            }
        }

        $this->clientModel->create([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
            'document_type' => $document_type,
            'document_number' => $document_number
        ]);

        $_SESSION['success'] = 'Cliente registrado correctamente.';
        return redirect('/clients');
    }

    public function update()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_clients'))->handle();

        $id = $_POST['id'] ?? '';
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $document_type = $_POST['document_type'] ?? '';
        $document_number = trim($_POST['document_number'] ?? '');

        if (!$name || !$document_type || !$document_number) {
            $_SESSION['error'] = 'Nombre, tipo de documento y número de documento son obligatorios.';
            return redirect('/clients/edit?id=' . $id);
        }

        // Validar email duplicado
        if ($email) {
            $validateEmail = $this->clientModel->findByEmailAndId($email, $id);
            if ($validateEmail) {
                $_SESSION['error'] = 'Este correo ya está registrado.';
                return redirect('/clients/edit?id=' . $id);
            }
        }

        $validateDocument = $this->clientModel->findByDocumentAndId($document_type, $document_number, $id);

        if ($validateDocument) {
            $_SESSION['error'] = 'Este número de documento ya está registrado para el tipo seleccionado.';
            return redirect('/clients/edit?id=' . $id);
        }


        // Validar teléfono duplicado (si se ingresó)
        if ($phone) {
            $validatePhone = $this->clientModel->findByPhoneAndId($phone, $id);
            if ($validatePhone) {
                $_SESSION['error'] = 'Este número de teléfono ya está registrado.';
                return redirect('/clients/edit?id=' . $id);
            }
        }

        $this->clientModel->update($id, [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
            'document_type' => $document_type,
            'document_number' => $document_number
        ]);

        $_SESSION['success'] = 'Cliente actualizado correctamente.';
        return redirect('/clients');
    }

    public function delete()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_clients'))->handle();

        $id = $_GET['id'] ?? '';
        if (!$id) {
            return view('error/nopage');
        }
        $this->clientModel->delete($id);
        $_SESSION['success'] = 'Cliente eliminado correctamente.';
        return redirect('/clients');
    }

    public function show()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_clients'))->handle();
        $client_id = $_GET['id'];

        // Cliente
        $client = $this->clientModel->find($client_id);

        // Autos del cliente
        $cars = $this->carModel->getByClientId($client_id);

        // Fotos por auto
        $photos = [];
        foreach ($cars as $car) {
            $photos[$car['id']] = $this->carModel->getPhotos($car['id']);
        }

        return view('clients/show', [
            'menu' => menu(),
            'client' => $client,
            'cars' => $cars,
            'photos' => $photos
        ]);
    }

    public function storeCar()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_clients'))->handle();

        $this->carModel->create([
            'client_id' => $_POST['client_id'],
            'marca' => $_POST['brand'],
            'modelo' => $_POST['model'],
            'placa' => $_POST['plate'],
            'year' => $_POST['year']
        ]);

        return redirect('/clients/show?id=' . $_POST['client_id']);
    }

    public function uploadPhoto()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_clients'))->handle();
        $car_id = $_POST['car_id'];

        if (!empty($_FILES['photo']['name'])) {
            $filename = time() . '_' . basename($_FILES['photo']['name']);
            $targetDir = __DIR__ . '/../../public/uploads/';
            move_uploaded_file($_FILES['photo']['tmp_name'], $targetDir . $filename);

            $this->carModel->addPhoto($car_id, '/uploads/' . $filename);
        }

        return redirect('/clients/show?id=' . $_POST['client_id']);
    }
}
