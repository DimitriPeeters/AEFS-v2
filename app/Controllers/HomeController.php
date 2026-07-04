<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\HomeRepository;

final class HomeController extends BaseController
{
    public function __construct(
        private HomeRepository $repository
    )
    {
        parent::__construct();
    }

    public function index(): void
    {
        $this->view('home', [

            'titel' => 'AEFS v2',

            'versie' => $this->repository->databaseVersion()

        ]);
    }
}