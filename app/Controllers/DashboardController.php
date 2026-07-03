<?php

declare(strict_types=1);

namespace AEFS\Controllers;

use AEFS\Core\Auth;
use AEFS\Core\Request;

final class DashboardController
{
    public function index(Request $request): void
    {
        $user = Auth::user();

        require dirname(__DIR__, 2)
            . '/resources/views/dashboard/index.php';
    }
}