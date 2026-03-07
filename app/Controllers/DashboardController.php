<?php

namespace App\Controllers;

use App\Core\Database;
use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\Notification;
use App\Models\WarrantyValidity;
use PDO;

class DashboardController
{
    public function index()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_dashboard'))->handle();

        $db = Database::connect();
        (new WarrantyValidity());

        $paidInvoicesMonth = (int) $db->query(" 
            SELECT COUNT(*)
            FROM invoices
            WHERE status = 'paid'
              AND YEAR(issued_at) = YEAR(CURDATE())
              AND MONTH(issued_at) = MONTH(CURDATE())
        ")->fetchColumn();

        $monthlyRevenue = (float) $db->query(" 
            SELECT COALESCE(SUM(total), 0)
            FROM invoices
            WHERE status = 'paid'
              AND YEAR(issued_at) = YEAR(CURDATE())
              AND MONTH(issued_at) = MONTH(CURDATE())
        ")->fetchColumn();

        $workOrdersInProgress = (int) $db->query(" 
            SELECT COUNT(*)
            FROM work_orders
            WHERE status = 'in_progress'
        ")->fetchColumn();

        $warrantiesExpiringSoon = (int) $db->query(" 
            SELECT COUNT(*)
            FROM warranty_validities
            WHERE status = 'active'
              AND DATE(expires_at) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
        ")->fetchColumn();

        $currentYear = (int) date('Y');
        $requestedYear = isset($_GET['year']) ? (int) $_GET['year'] : $currentYear;

        $availableYearsStmt = $db->query(" 
            SELECT DISTINCT YEAR(issued_at) AS year_value
            FROM invoices
            WHERE issued_at IS NOT NULL
            ORDER BY year_value DESC
        ");
        $availableYears = array_map(
            static fn(array $row): int => (int) ($row['year_value'] ?? 0),
            $availableYearsStmt->fetchAll(PDO::FETCH_ASSOC)
        );
        $availableYears = array_values(array_filter($availableYears, static fn(int $year): bool => $year > 0));

        if (empty($availableYears)) {
            $availableYears = [$currentYear];
        }

        if (!in_array($requestedYear, $availableYears, true)) {
            $requestedYear = $currentYear;
            if (!in_array($requestedYear, $availableYears, true)) {
                $requestedYear = $availableYears[0];
            }
        }

        $monthlyRevenueStmt = $db->prepare(" 
            SELECT
                MONTH(issued_at) AS month_number,
                COALESCE(SUM(total), 0) AS amount
            FROM invoices
            WHERE status = 'paid'
              AND YEAR(issued_at) = :year
            GROUP BY MONTH(issued_at)
            ORDER BY month_number ASC
        ");
        $monthlyRevenueStmt->execute(['year' => $requestedYear]);

        $monthlyRevenueRows = $monthlyRevenueStmt->fetchAll(PDO::FETCH_ASSOC);
        $monthLabels = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        $monthlyRevenueChart = array_fill(0, 12, 0.0);

        foreach ($monthlyRevenueRows as $row) {
            $monthIndex = (int) ($row['month_number'] ?? 0) - 1;
            if ($monthIndex >= 0 && $monthIndex < 12) {
                $monthlyRevenueChart[$monthIndex] = round((float) ($row['amount'] ?? 0), 2);
            }
        }

        $quoteSummaryStmt = $db->query(" 
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) AS approved,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) AS rejected
            FROM quotes
        ");
        $quoteSummary = $quoteSummaryStmt->fetch(PDO::FETCH_ASSOC) ?: [
            'total' => 0,
            'approved' => 0,
            'pending' => 0,
            'rejected' => 0,
        ];

        $expiringWarrantiesStmt = $db->query(" 
            SELECT
                wv.id,
                wv.invoice_id,
                wv.service_description,
                wv.expires_at,
                c.name AS client_name,
                CONCAT(car.brand, ' ', car.model, ' - ', car.plate) AS car_info
            FROM warranty_validities wv
            INNER JOIN clients c ON c.id = wv.client_id
            INNER JOIN cars car ON car.id = wv.car_id
            WHERE wv.status = 'active'
              AND DATE(wv.expires_at) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
            ORDER BY wv.expires_at ASC
            LIMIT 8
        ");
        $expiringWarranties = $expiringWarrantiesStmt->fetchAll(PDO::FETCH_ASSOC);

        $notificationsStats = (new Notification())->getStatistics();

        $failedNotificationsStmt = $db->query(" 
            SELECT
                n.id,
                n.status,
                n.phone_number,
                n.error_message,
                c.name AS client_name
            FROM notifications n
            LEFT JOIN clients c ON c.id = n.client_id
            WHERE n.status IN ('failed', 'pending')
            ORDER BY n.id DESC
            LIMIT 8
        ");
        $actionableNotifications = $failedNotificationsStmt->fetchAll(PDO::FETCH_ASSOC);

        return view('dashboard', [
            'paidInvoicesMonth' => $paidInvoicesMonth,
            'monthlyRevenue' => $monthlyRevenue,
            'selectedYear' => $requestedYear,
            'availableYears' => $availableYears,
            'monthLabels' => $monthLabels,
            'monthlyRevenueChart' => $monthlyRevenueChart,
            'workOrdersInProgress' => $workOrdersInProgress,
            'warrantiesExpiringSoon' => $warrantiesExpiringSoon,
            'quoteSummary' => $quoteSummary,
            'expiringWarranties' => $expiringWarranties,
            'notificationsStats' => $notificationsStats,
            'actionableNotifications' => $actionableNotifications,
        ]);
    }
}
