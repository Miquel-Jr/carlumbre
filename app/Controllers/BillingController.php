<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\Invoice;
use App\Models\WarrantyValidity;
use App\Models\WorkOrder;

class BillingController
{
    private const BILLING_ROUTE = '/billing';
    private const ERROR_PAGE = 'errors/nopage';

    protected $invoiceModel;
    protected $workOrderModel;
    protected $warrantyValidityModel;

    public function __construct()
    {
        $this->invoiceModel = new Invoice();
        $this->workOrderModel = new WorkOrder();
        $this->warrantyValidityModel = new WarrantyValidity();
    }

    public function index()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_billing'))->handle();
        $search = trim($_GET['search'] ?? '');
        $invoices = $this->invoiceModel->all($search !== '' ? $search : null);

        return view('billing/index', [
            'invoices' => $invoices,
        ]);
    }

    public function show()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_quotes'))->handle();

        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'ID de factura no proporcionado.';
            return redirect(self::BILLING_ROUTE);
        }

        $invoice = $this->invoiceModel->find($id);
        if (!$invoice) {
            return view(self::ERROR_PAGE);
        }

        $items = $this->invoiceModel->getItems($id);
        $warranties = $this->warrantyValidityModel->listByInvoiceId((int) $id);

        return view('billing/show', [
            'invoice' => $invoice,
            'items' => $items,
            'warranties' => $warranties,
        ]);
    }

    public function generate()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_quotes'))->handle();

        $workOrderId = $_POST['work_order_id'] ?? null;
        if (!$workOrderId) {
            $_SESSION['error'] = 'No se proporcionó la OT para facturar.';
            return redirect('/work-orders');
        }

        $issuedAt = $_POST['issued_at'] ?? null;
        if (!$issuedAt || !strtotime($issuedAt)) {
            $_SESSION['error'] = 'Fecha de emisión inválida.';
            return redirect('/work-orders/show?id=' . (int) $workOrderId);
        }

        $workOrder = $this->workOrderModel->find($workOrderId);
        if (!$workOrder) {
            return view(self::ERROR_PAGE);
        }

        $result = $this->invoiceModel->createFromWorkOrder((int) $workOrderId, $issuedAt);

        if (!empty($result['invoice_id'])) {
            $_SESSION['success'] = $result['message'] ?? 'Factura generada correctamente.';
            return redirect(self::BILLING_ROUTE . '/show?id=' . (int) $result['invoice_id']);
        }

        $_SESSION['error'] = $result['message'] ?? 'No se pudo generar la factura.';
        return redirect('/work-orders/show?id=' . (int) $workOrderId);
    }

    public function delete()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_quotes'))->handle();

        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'ID de factura no proporcionado.';
            return redirect(self::BILLING_ROUTE);
        }

        $invoice = $this->invoiceModel->find($id);
        if (!$invoice) {
            $_SESSION['error'] = 'Factura no encontrada.';
            return redirect(self::BILLING_ROUTE);
        }

        try {
            $this->invoiceModel->deleteWithItems((int) $id);
            $_SESSION['success'] = 'Factura eliminada correctamente.';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error al eliminar la factura: ' . $e->getMessage();
        }

        return redirect(self::BILLING_ROUTE);
    }

    public function updateInvoiceNumber()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_quotes'))->handle();

        $id = $_POST['id'] ?? null;
        $invoiceNumber = $_POST['invoice_number'] ?? '';
        $invoiceNumber = trim($invoiceNumber);

        if (!$id) {
            $_SESSION['error'] = 'ID de factura no proporcionado.';
            return redirect(self::BILLING_ROUTE);
        }

        $invoice = $this->invoiceModel->find($id);
        if (!$invoice) {
            $_SESSION['error'] = 'Factura no encontrada.';
            return redirect(self::BILLING_ROUTE);
        }

        if ($invoiceNumber !== '') {
            $existingInvoice = $this->invoiceModel->findByInvoiceNumber($invoiceNumber);

            if ($existingInvoice && (int) ($existingInvoice['id'] ?? 0) !== (int) $id) {
                $_SESSION['error'] = 'El número de factura ya existe en la factura #' . (int) $existingInvoice['id'] . '.';
                return redirect(self::BILLING_ROUTE . '/show?id=' . (int) $id);
            }
        }

        try {
            $this->invoiceModel->updateInvoiceNumber((int) $id, $invoiceNumber);
            $_SESSION['success'] = 'Número de factura actualizado correctamente.';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error al actualizar el número de factura: ' . $e->getMessage();
        }

        return redirect(self::BILLING_ROUTE . '/show?id=' . (int) $id);
    }

    public function updateStatus()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_quotes'))->handle();

        $id = $_POST['id'] ?? null;
        $status = $_POST['status'] ?? '';
        $redirectTo = $_POST['redirect_to'] ?? null;

        if (!$id) {
            $_SESSION['error'] = 'ID de factura no proporcionado.';
            return redirect(self::BILLING_ROUTE);
        }

        if (!in_array($status, ['issued', 'paid', 'cancelled'], true)) {
            $_SESSION['error'] = 'Estado de factura no válido.';
            return redirect($redirectTo ?: (self::BILLING_ROUTE . '/show?id=' . (int) $id));
        }

        $invoice = $this->invoiceModel->find($id);
        if (!$invoice) {
            $_SESSION['error'] = 'Factura no encontrada.';
            return redirect(self::BILLING_ROUTE);
        }

        $currentStatus = $invoice['status'] ?? 'issued';
        if ($currentStatus !== 'issued') {
            $_SESSION['error'] = 'Solo se puede cambiar el estado cuando la factura está emitida.';
            return redirect($redirectTo ?: (self::BILLING_ROUTE . '/show?id=' . (int) $id));
        }

        if (!in_array($status, ['paid', 'cancelled'], true)) {
            $_SESSION['error'] = 'Desde emitida solo se puede pasar a pagada o anulada.';
            return redirect($redirectTo ?: (self::BILLING_ROUTE . '/show?id=' . (int) $id));
        }

        try {
            $this->invoiceModel->updateStatus((int) $id, $status);

            $newWarranties = 0;
            if ($status === 'paid') {
                $newWarranties = $this->warrantyValidityModel->registerFromPaidInvoice((int) $id);
            }
            
            $statusLabels = [
                'issued' => 'Emitida',
                'paid' => 'Pagada',
                'cancelled' => 'Anulada',
            ];
            
            $_SESSION['success'] = 'Estado actualizado a: ' . $statusLabels[$status];

            if ($status === 'paid') {
                if ($newWarranties > 0) {
                    $_SESSION['success'] .= '. Se registraron ' . $newWarranties . ' garantía(s) vigente(s).';
                } else {
                    $_SESSION['success'] .= '. No se encontraron servicios con garantía para registrar.';
                }
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error al actualizar el estado: ' . $e->getMessage();
        }

        return redirect($redirectTo ?: (self::BILLING_ROUTE . '/show?id=' . (int) $id));
    }
}
