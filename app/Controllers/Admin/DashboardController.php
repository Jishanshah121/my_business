<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Http\Response;
use App\Repositories\BusinessProfileRepository;
use App\Services\Auth\AuthService;
use App\Services\Auth\Gate;
use App\Support\Csrf;
use App\Support\Database;
use App\Support\Session\Session;
use App\Support\View;

/**
 * Admin landing page for Phase 2. The full dashboard (revenue, orders, charts)
 * arrives in Phase 7 — this shows what exists now and what needs attention.
 */
final class DashboardController extends Controller
{
    public function __construct(
        View $view,
        Session $session,
        AuthService $auth,
        Gate $gate,
        Csrf $csrf,
        private readonly BusinessProfileRepository $businesses,
    ) {
        parent::__construct($view, $session, $auth, $gate, $csrf);
    }

    public function index(): Response
    {
        $pdo = Database::connection();

        return $this->render('admin/dashboard', [
            'title' => 'Admin',
            'stats' => [
                'pending_businesses' => $this->businesses->countByStatus('pending'),
                'customers'          => (int) $pdo->query("SELECT COUNT(*) FROM `users` WHERE `deleted_at` IS NULL")->fetchColumn(),
                'products'           => (int) $pdo->query("SELECT COUNT(*) FROM `products` WHERE `status` = 'published'")->fetchColumn(),
                'skus'               => (int) $pdo->query("SELECT COUNT(*) FROM `variant_packs` WHERE `status` = 'active'")->fetchColumn(),
                'low_stock'          => (int) $pdo->query(
                    'SELECT COUNT(*) FROM `inventory`
                     WHERE `low_stock_threshold` > 0
                       AND (`quantity_on_hand` - `quantity_reserved`) <= `low_stock_threshold`'
                )->fetchColumn(),
            ],
        ]);
    }
}
