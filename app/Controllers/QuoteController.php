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
use App\Models\Product;
use App\Models\WorkOrder;
use Dompdf\Dompdf;
use Dompdf\Options;

class QuoteController
{
    private const QUOTES_ROUTE = '/quotes';
    private const QUOTE_ID_ERROR = 'ID de presupuesto no proporcionado.';
    protected $clientModel;
    protected $carModel;
    protected $serviceModel;
    protected $carPhotoModel;
    protected $quoteModel;
    protected $quoteItemsModel;
    protected $productModel;
    protected $workOrderModel;
    public function __construct()
    {
        $this->clientModel = new Client();
        $this->carModel = new Car();
        $this->serviceModel = new Service();
        $this->carPhotoModel = new CarPhoto();
        $this->quoteModel = new Quote();
        $this->quoteItemsModel = new QuoteItems();
        $this->productModel = new Product();
        $this->workOrderModel = new WorkOrder();
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
        $products = $this->productModel->all();
        $photos = $this->carPhotoModel->all();

        return view('quotes/create', ['clients' => $clients, 'cars' => $cars, 'services' => $services, 'products' => $products, 'photos' => $photos]);
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
            $_SESSION['error'] = 'El cliente, el coche y el total son campos obligatorios.'. $clientId. '-' . $carId . '-' . $total;

            return redirect(self::QUOTES_ROUTE . '/create');
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
        $hasWarranties = $_POST['has_warranties'] ?? [];
        $warrantyTimes = $_POST['warranty_times'] ?? [];
        $partIds = $_POST['part_ids'] ?? [];
        $partDescriptions = $_POST['part_descriptions'] ?? [];
        $partQuantities = $_POST['part_quantities'] ?? [];
        $partPrices = $_POST['part_prices'] ?? [];
        $partImages = $_POST['part_images'] ?? [];

        foreach ($services as $index => $serviceId) {
            if (empty($serviceId)) {
                continue;
            }

            $hasWarranty = (int) ($hasWarranties[$index] ?? 0) === 1 ? 1 : 0;
            $warrantyTimeBase = null;

            if ($hasWarranty === 1) {
                $candidateTime = (int) ($warrantyTimes[$index] ?? 0);
                $warrantyTimeBase = $candidateTime > 0 ? $candidateTime : 1;
            }

            $this->quoteItemsModel->create([
                'quote_id' => $quoteId,
                'service_id' => $serviceId,
                'item_type' => 'service',
                'description' => $descripcions[$index] ?? '',
                'quantity' => $quantities[$index] ?? 1,
                'price' => $prices[$index] ?? 0,
                'subtotal' => ($quantities[$index] ?? 1) * ($prices[$index] ?? 0),
                'has_warranty' => $hasWarranty,
                'warranty_time_base' => $warrantyTimeBase
            ]);
        }

        foreach ($partIds as $index => $partId) {
            $partDescription = trim((string) ($partDescriptions[$index] ?? ''));
            $partQuantity = (int) ($partQuantities[$index] ?? 1);
            $partPrice = (float) ($partPrices[$index] ?? 0);

            if ($partDescription === '' || $partQuantity < 1) {
                continue;
            }

            $this->quoteItemsModel->create([
                'quote_id' => $quoteId,
                'service_id' => null,
                'product_id' => !empty($partId) ? (int) $partId : null,
                'item_type' => 'product',
                'description' => $partDescription,
                'quantity' => $partQuantity,
                'price' => $partPrice,
                'subtotal' => $partQuantity * $partPrice,
                'has_warranty' => 0,
                'warranty_time_base' => null,
                'reference_image_url' => trim((string) ($partImages[$index] ?? '')) ?: null
            ]);
        }

        $_SESSION['success'] = 'Presupuesto creado exitosamente.';
        return redirect(self::QUOTES_ROUTE);
    }

    public function edit()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_quotes'))->handle();

        $quoteId = $_GET['id'] ?? null;
        if (!$quoteId) {
            $_SESSION['error'] = self::QUOTE_ID_ERROR;
            return redirect(self::QUOTES_ROUTE);
        }

        $quote = $this->quoteModel->find($quoteId);
        if (!$quote) {
            $_SESSION['error'] = 'Presupuesto no encontrado.';
            return redirect(self::QUOTES_ROUTE);
        }

        $clients = $this->clientModel->all();
        $cars = $this->carModel->all();
        $services = $this->serviceModel->all();
        $products = $this->productModel->all();
        $photos = $this->carPhotoModel->all();
        $items = $this->quoteItemsModel->getByQuoteId($quoteId);

        return view('quotes/edit', ['quote' => $quote, 'clients' => $clients, 'cars' => $cars, 'services' => $services, 'products' => $products, 'photos' => $photos, 'items' => $items]);
    }

    public function update()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_quotes'))->handle();
        $quoteId = $_POST['id'] ?? null;
        if (!$quoteId) {
            $_SESSION['error'] = self::QUOTE_ID_ERROR;
            return redirect(self::QUOTES_ROUTE);
        }
        $clientId = $_POST['client_id'];
        $carId = $_POST['car_id'];
        $total = $_POST['total'] ?? null;
        $status = $_POST['status'] ?? 'pending';
        $notes = $_POST['notes'] ?? null;

        if (!$clientId || !$carId || !$total) {
            $_SESSION['error'] = 'El cliente, el coche y el total son campos obligatorios.'. $clientId. '-' . $carId . '-' . $total;
            return redirect(self::QUOTES_ROUTE . '/edit?id=' . $quoteId);
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
        $hasWarranties = $_POST['has_warranties'] ?? [];
        $warrantyTimes = $_POST['warranty_times'] ?? [];
        $partIds = $_POST['part_ids'] ?? [];
        $partDescriptions = $_POST['part_descriptions'] ?? [];
        $partQuantities = $_POST['part_quantities'] ?? [];
        $partPrices = $_POST['part_prices'] ?? [];
        $partImages = $_POST['part_images'] ?? [];

        foreach ($services as $index => $serviceId) {
            if (empty($serviceId)) {
                continue;
            }

            if (isset($descriptions[$index]) && isset($quantities[$index]) && isset($prices[$index])) {
                $hasWarranty = (int) ($hasWarranties[$index] ?? 0) === 1 ? 1 : 0;
                $warrantyTimeBase = null;

                if ($hasWarranty === 1) {
                    $candidateTime = (int) ($warrantyTimes[$index] ?? 0);
                    $warrantyTimeBase = $candidateTime > 0 ? $candidateTime : 1;
                }

                $this->quoteItemsModel->create([
                    'quote_id' => $quoteId,
                    'service_id' => $serviceId,
                    'item_type' => 'service',
                    'description' => htmlspecialchars($descriptions[$index]),
                    'quantity' => (int)$quantities[$index],
                    'price' => (float)$prices[$index],
                    'subtotal' => (int)$quantities[$index] * (float)$prices[$index],
                    'has_warranty' => $hasWarranty,
                    'warranty_time_base' => $warrantyTimeBase
                ]);
            }
        }

        foreach ($partIds as $index => $partId) {
            $partDescription = trim((string) ($partDescriptions[$index] ?? ''));
            $partQuantity = (int) ($partQuantities[$index] ?? 1);
            $partPrice = (float) ($partPrices[$index] ?? 0);

            if ($partDescription === '' || $partQuantity < 1) {
                continue;
            }

            $this->quoteItemsModel->create([
                'quote_id' => $quoteId,
                'service_id' => null,
                'product_id' => !empty($partId) ? (int) $partId : null,
                'item_type' => 'product',
                'description' => htmlspecialchars($partDescription),
                'quantity' => $partQuantity,
                'price' => $partPrice,
                'subtotal' => $partQuantity * $partPrice,
                'has_warranty' => 0,
                'warranty_time_base' => null,
                'reference_image_url' => trim((string) ($partImages[$index] ?? '')) ?: null
            ]);
        }

        $_SESSION['success'] = 'Presupuesto actualizado exitosamente.';
        return redirect(self::QUOTES_ROUTE);
    }

    public function delete()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_quotes'))->handle();

        $quoteId = $_GET['id'] ?? null;
        if (!$quoteId) {
            $_SESSION['error'] = self::QUOTE_ID_ERROR;
            return redirect(self::QUOTES_ROUTE);
        }

        $this->quoteModel->delete($quoteId);
        $_SESSION['success'] = 'Presupuesto eliminado exitosamente.';
        return redirect(self::QUOTES_ROUTE);
    }

    public function approve()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_quotes'))->handle();

        $quoteId = $_GET['id'] ?? null;
        if (!$quoteId) {
            $_SESSION['error'] = self::QUOTE_ID_ERROR;
            return redirect(self::QUOTES_ROUTE);
        }

        $this->quoteModel->updateStatus($quoteId, 'approved');

        $workOrderResult = $this->workOrderModel->createFromQuote((int) $quoteId);

        if (!empty($workOrderResult['work_order_id'])) {
            if (!empty($workOrderResult['created'])) {
                $_SESSION['success'] = 'Presupuesto aprobado y OT #' . $workOrderResult['work_order_id'] . ' generada automáticamente.';
            } else {
                $_SESSION['success'] = 'Presupuesto aprobado. La OT #' . $workOrderResult['work_order_id'] . ' ya existía.';
            }
        } else {
            $_SESSION['success'] = 'Presupuesto aprobado exitosamente.';
        }

        return redirect(self::QUOTES_ROUTE);
    }

    public function reject()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_quotes'))->handle();

        $quoteId = $_GET['id'] ?? null;
        if (!$quoteId) {
            $_SESSION['error'] = self::QUOTE_ID_ERROR;
            return redirect(self::QUOTES_ROUTE);
        }

        $this->quoteModel->updateStatus($quoteId, 'rejected');
        $_SESSION['success'] = 'Presupuesto rechazado exitosamente.';
        return redirect(self::QUOTES_ROUTE);
    }

    private function imageToBase64($path)
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
            $_SESSION['error'] = self::QUOTE_ID_ERROR;
            return redirect(self::QUOTES_ROUTE);
        }

        $quote = $this->quoteModel->generatePdf($id);

        if (!$quote) {
            $_SESSION['error'] = 'Presupuesto no encontrado.';
            return redirect(self::QUOTES_ROUTE);
        }

        $idPresupuesto = str_pad($quote['id'], 8, '0', STR_PAD_LEFT);

        $items = $this->quoteItemsModel->getByQuoteId($id);
        $logoBase64 = $this->imageToBase64(__DIR__ . '/../../public/assets/carlumbre/Icon.jpeg');
        $facebookIcon = $this->imageToBase64(__DIR__ . '/../../public/assets/carlumbre/facebook.png');
        $instagramIcon = $this->imageToBase64(__DIR__ . '/../../public/assets/carlumbre/instagram.png');
        $whatsappIcon = $this->imageToBase64(__DIR__ . '/../../public/assets/carlumbre/whatsapp.png');


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

        $dompdf->stream("Presupuesto-{$idPresupuesto}.pdf", ["Attachment" => true]);
    }
}