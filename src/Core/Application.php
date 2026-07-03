<?php
declare(strict_types=1);

namespace AEFS\Core;

final class Application
{
    private Kernel $kernel;

    public function __construct()
    {
        $this->kernel = Container::get(Kernel::class);
    }

    public function run(): void
    {
        $this->kernel->handle();
    }
}