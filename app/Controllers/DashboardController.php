<?php

declare(strict_types=1);

namespace App\Controllers;

use AEFS\Core\Http\Request;
use AEFS\Core\Http\Response;
use App\Services\DashboardService;

final class DashboardController extends BaseController
{
    public function __construct(
        private DashboardService $dashboardService
    ) {
    }

    public function index(Request $request): Response
    {
        return $this->view(
            'dashboard.index',
            $this->dashboardService->getDashboardData()
        );
    }
}