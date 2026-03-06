<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\Invoice;
use App\Models\WorkOrder;
use Throwable;

class WorkOrderController
{
    private const WORK_ORDERS_ROUTE = '/work-orders';
    private const ERROR_PAGE = 'errors/nopage';

    protected $workOrderModel;
    protected $invoiceModel;

    public function __construct()
    {
        $this->workOrderModel = new WorkOrder();
        $this->invoiceModel = new Invoice();
    }

    public function index()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_ot'))->handle();
        $search = trim($_GET['search'] ?? '');
        $workOrders = $this->workOrderModel->all($search !== '' ? $search : null);

        return view('work_orders/index', [
            'workOrders' => $workOrders,
        ]);
    }

    public function show()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_ot'))->handle();

        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'ID de OT no proporcionado.';
            return redirect(self::WORK_ORDERS_ROUTE);
        }

        $workOrder = $this->workOrderModel->find($id);
        if (!$workOrder) {
            return view(self::ERROR_PAGE);
        }

        $activities = $this->workOrderModel->getActivities($id);
        $invoice = $this->invoiceModel->findByWorkOrderId($id);

        return view('work_orders/show', [
            'workOrder' => $workOrder,
            'activities' => $activities,
            'invoice' => $invoice,
        ]);
    }

    public function addActivity()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_ot'))->handle();

        $workOrderId = $_POST['work_order_id'] ?? null;
        $description = trim((string) ($_POST['description'] ?? ''));
        $quantity = (int) ($_POST['quantity'] ?? 1);
        $unitPrice = (float) ($_POST['unit_price'] ?? 0);

        if (!$workOrderId || $description === '') {
            $_SESSION['error'] = 'Debes indicar la OT y la descripción de la actividad.';
            return redirect(self::WORK_ORDERS_ROUTE);
        }

        $workOrder = $this->workOrderModel->find($workOrderId);
        if (!$workOrder) {
            return view(self::ERROR_PAGE);
        }

        $this->workOrderModel->addActivity($workOrderId, $description, $quantity, 'manual', $unitPrice);

        $_SESSION['success'] = 'Actividad agregada correctamente.';
        return redirect(self::WORK_ORDERS_ROUTE . '/show?id=' . $workOrderId);
    }

    public function updateActivityStatus()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_ot'))->handle();

        $workOrderId = $_POST['work_order_id'] ?? null;
        $activityId = $_POST['activity_id'] ?? null;
        $status = trim((string) ($_POST['status'] ?? 'pending'));

        if (!$workOrderId || !$activityId) {
            $_SESSION['error'] = 'No se pudo identificar la actividad a actualizar.';
            return redirect(self::WORK_ORDERS_ROUTE);
        }

        $workOrder = $this->workOrderModel->find($workOrderId);
        if (!$workOrder) {
            return view(self::ERROR_PAGE);
        }

        $updated = $this->workOrderModel->updateActivityStatus($activityId, $workOrderId, $status);

        if ($updated) {
            $_SESSION['success'] = 'Estado de actividad actualizado correctamente.';
        } else {
            $_SESSION['error'] = 'No se pudo actualizar el estado de la actividad.';
        }

        return redirect(self::WORK_ORDERS_ROUTE . '/show?id=' . $workOrderId);
    }

    public function delete()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_ot'))->handle();

        $workOrderId = $_GET['id'] ?? null;
        if (!$workOrderId) {
            $_SESSION['error'] = 'ID de OT no proporcionado.';
            return redirect(self::WORK_ORDERS_ROUTE);
        }

        $workOrder = $this->workOrderModel->find($workOrderId);
        if (!$workOrder) {
            return view(self::ERROR_PAGE);
        }

        $invoice = $this->invoiceModel->findByWorkOrderId($workOrderId);
        if ($invoice) {
            $_SESSION['error'] = 'No se puede eliminar la OT porque ya tiene una factura asociada.';
            return redirect(self::WORK_ORDERS_ROUTE);
        }

        try {
            $this->workOrderModel->deleteWithActivities($workOrderId);
            $_SESSION['success'] = 'Orden de trabajo eliminada correctamente.';
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            $_SESSION['error'] = 'No se pudo eliminar la orden de trabajo.';
        }

        return redirect(self::WORK_ORDERS_ROUTE);
    }
}
