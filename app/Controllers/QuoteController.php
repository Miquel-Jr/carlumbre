<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\Car;
use App\Models\CarPhoto;
use App\Models\Client;
use App\Models\Service;
use App\Models\Quote;
use App\Models\QuoteItems;
use Dompdf\Dompdf;
use Dompdf\Options;

class QuoteController
{
    protected $clientModel;
    protected $carModel;
    protected $serviceModel;
    protected $carPhotoModel;
    protected $quoteModel;
    protected $quoteItemsModel;
    public function __construct()
    {
        $this->clientModel = new Client();
        $this->carModel = new Car();
        $this->serviceModel = new Service();
        $this->carPhotoModel = new CarPhoto();
        $this->quoteModel = new Quote();
        $this->quoteItemsModel = new QuoteItems();
    }
    public function index()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_quotes'))->handle();
        $quotes = $this->quoteModel->all();
        return view('quotes/index', ['quotes' => $quotes]);
    }

    public function create()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_quotes'))->handle();

        $clients = $this->clientModel->all();
        $cars = $this->carModel->all();
        $services = $this->serviceModel->all();
        $photos = $this->carPhotoModel->all();

        return view('quotes/create', ['clients' => $clients, 'cars' => $cars, 'services' => $services, 'photos' => $photos]);
    }

    public function store()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_quotes'))->handle();

        $clientId = $_POST['client_id'] ?? null;
        $carId = $_POST['car_id'] ?? null;
        $total = $_POST['total'] ?? null;
        $status = $_POST['status'] ?? 'pending';
        $notes = $_POST['notes'] ?? null;

        if (!$clientId || !$carId || !$total) {
            $_SESSION['error'] = 'El cliente, el coche y el total son campos obligatorios.';
            return redirect('/quotes/create');
        }

        $quoteId = $this->quoteModel->create([
            'client_id' => $clientId,
            'car_id' => $carId,
            'total' => $total,
            'status' => $status,
            'notes' => $notes
        ]);

        $services = $_POST['services'] ?? [];
        $descripcions = $_POST['descriptions'] ?? [];
        $quantities = $_POST['quantities'] ?? [];
        $prices = $_POST['prices'] ?? [];

        foreach ($services as $index => $serviceId) {
            $this->quoteItemsModel->create([
                'quote_id' => $quoteId,
                'service_id' => $serviceId,
                'description' => $descripcions[$index] ?? '',
                'quantity' => $quantities[$index] ?? 1,
                'price' => $prices[$index] ?? 0,
                'subtotal' => ($quantities[$index] ?? 1) * ($prices[$index] ?? 0)
            ]);
        }

        $_SESSION['success'] = 'Presupuesto creado exitosamente.';
        return redirect('/quotes');
    }

    public function edit()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_quotes'))->handle();

        $quoteId = $_GET['id'] ?? null;
        if (!$quoteId) {
            $_SESSION['error'] = 'ID de presupuesto no proporcionado.';
            return redirect('/quotes');
        }

        $quote = $this->quoteModel->find($quoteId);
        if (!$quote) {
            $_SESSION['error'] = 'Presupuesto no encontrado.';
            return redirect('/quotes');
        }

        $clients = $this->clientModel->all();
        $cars = $this->carModel->all();
        $services = $this->serviceModel->all();
        $photos = $this->carPhotoModel->all();
        $items = $this->quoteItemsModel->getByQuoteId($quoteId);

        return view('quotes/edit', ['quote' => $quote, 'clients' => $clients, 'cars' => $cars, 'services' => $services, 'photos' => $photos, 'items' => $items]);
    }

    public function update()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_quotes'))->handle();
        $quoteId = $_POST['id'] ?? null;
        if (!$quoteId) {
            $_SESSION['error'] = 'ID de presupuesto no proporcionado.';
            return redirect('/quotes');
        }
        $clientId = $_POST['client_id'];
        $carId = $_POST['car_id'];
        $total = $_POST['total'] ?? null;
        $status = $_POST['status'] ?? 'pending';
        $notes = $_POST['notes'] ?? null;

        if (!$clientId || !$carId || !$total) {
            $_SESSION['error'] = 'El cliente, el coche y el total son campos obligatorios.';
            return redirect('/quotes/edit?id=' . $quoteId);
        }

        $this->quoteModel->update($quoteId, [
            'client_id' => $clientId,
            'car_id' => $carId,
            'total' => $total,
            'status' => $status,
            'notes' => $notes
        ]);

        // Eliminar los items anteriores
        $this->quoteItemsModel->deleteByQuoteId($quoteId);

        // Agregar los nuevos items
        $services = $_POST['services'] ?? [];
        $descriptions = $_POST['descriptions'] ?? [];
        $quantities = $_POST['quantities'] ?? [];
        $prices = $_POST['prices'] ?? [];

        foreach ($services as $index => $serviceId) {
            if (isset($descriptions[$index]) && isset($quantities[$index]) && isset($prices[$index])) {
                $this->quoteItemsModel->create([
                    'quote_id' => $quoteId,
                    'service_id' => $serviceId,
                    'description' => htmlspecialchars($descriptions[$index]),
                    'quantity' => (int)$quantities[$index],
                    'price' => (float)$prices[$index],
                    'subtotal' => (int)$quantities[$index] * (float)$prices[$index]
                ]);
            }
        }

        $_SESSION['success'] = 'Presupuesto actualizado exitosamente.';
        return redirect('/quotes');
    }

    public function delete()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_quotes'))->handle();

        $quoteId = $_GET['id'] ?? null;
        if (!$quoteId) {
            $_SESSION['error'] = 'ID de presupuesto no proporcionado.';
            return redirect('/quotes');
        }

        $this->quoteModel->delete($quoteId);
        $_SESSION['success'] = 'Presupuesto eliminado exitosamente.';
        return redirect('/quotes');
    }

    public function approve()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_quotes'))->handle();

        $quoteId = $_GET['id'] ?? null;
        if (!$quoteId) {
            $_SESSION['error'] = 'ID de presupuesto no proporcionado.';
            return redirect('/quotes');
        }

        $this->quoteModel->updateStatus($quoteId, 'approved');
        $_SESSION['success'] = 'Presupuesto aprobado exitosamente.';
        return redirect('/quotes');
    }

    public function reject()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_quotes'))->handle();

        $quoteId = $_GET['id'] ?? null;
        if (!$quoteId) {
            $_SESSION['error'] = 'ID de presupuesto no proporcionado.';
            return redirect('/quotes');
        }

        $this->quoteModel->updateStatus($quoteId, 'rejected');
        $_SESSION['success'] = 'Presupuesto rechazado exitosamente.';
        return redirect('/quotes');
    }

    function imageToBase64($path)
    {
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        return 'data:image/' . $type . ';base64,' . base64_encode($data);
    }

    public function generatePdf()
    {
        (new AuthMiddleware())->handle();

        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'ID de presupuesto no proporcionado.';
            return redirect('/quotes');
        }

        $quote = $this->quoteModel->generatePdf($id);

        if (!$quote) {
            $_SESSION['error'] = 'Presupuesto no encontrado.';
            return redirect('/quotes');
        }

        $items = $this->quoteItemsModel->getByQuoteId($id);

        $logoBase64 = $this->imageToBase64(__DIR__ . '/../../public/uploads/carlumbre/Icon.jpeg');
        $facebookIcon = $this->imageToBase64(__DIR__ . '/../../public/uploads/carlumbre/facebook.png');
        $instagramIcon = $this->imageToBase64(__DIR__ . '/../../public/uploads/carlumbre/instagram.png');
        $whatsappIcon = $this->imageToBase64(__DIR__ . '/../../public/uploads/carlumbre/whatsapp.png');


        // Generar HTML
        ob_start();
        include __DIR__ . '/../../resources/views/quotes/pdf.php';
        $html = ob_get_clean();

        $options = new Options();
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dompdf->stream("Presupuesto-{$id}.pdf", ["Attachment" => false]);
    }
}
