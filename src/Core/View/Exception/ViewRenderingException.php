<?php

declare(strict_types=1);

namespace AEFS\Core\View\Exception;

use RuntimeException;
use Throwable;

final class ViewRenderingException extends RuntimeException
{
    public function __construct(
        private readonly string $view,
        private readonly string $file,
        Throwable $previous
    ) {
        parent::__construct(
            sprintf(
                'Fout tijdens het renderen van view [%s] uit bestand [%s]: %s',
                $view,
                $file,
                $previous->getMessage()
            ),
            0,
            $previous
        );
    }

    public function view(): string
    {
        return $this->view;
    }

    public function file(): string
    {
        return $this->file;
    }
}