<?php

declare(strict_types=1);

namespace AEFS\Core;

use JsonException;

final class Response
{
    public static function html(
        string $content,
        int $status = 200
    ): never {

        http_response_code($status);

        header('Content-Type: text/html; charset=UTF-8');

        echo $content;

        exit;
    }

    /**
     * @throws JsonException
     */
    public static function json(
        array $data,
        int $status = 200
    ): never {

        http_response_code($status);

        header('Content-Type: application/json; charset=UTF-8');

        echo json_encode(
            $data,
            JSON_THROW_ON_ERROR
            | JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
        );

        exit;
    }

    public static function redirect(
        string $path,
        int $status = 302
    ): never {

        if (
            str_starts_with($path, 'http://')
            || str_starts_with($path, 'https://')
        ) {

            $url = $path;

        } else {

            $url = Url::to($path);

        }

        header(
            'Location: ' . $url,
            true,
            $status
        );

        exit;
    }

    public static function back(): never
    {
        $url = $_SERVER['HTTP_REFERER']
            ?? Url::to('/');

        self::redirect($url);
    }

    public static function refresh(): never
    {
        self::redirect(
            $_SERVER['REQUEST_URI'] ?? '/'
        );
    }

    public static function forbidden(): never
    {
        self::html(
            '<h1>403 - Toegang geweigerd</h1>',
            403
        );
    }

    public static function unauthorized(): never
    {
        self::html(
            '<h1>401 - Niet aangemeld</h1>',
            401
        );
    }

    public static function notFound(): never
    {
        self::html(
            '<h1>404 - Pagina niet gevonden</h1>',
            404
        );
    }

    public static function noContent(): never
    {
        http_response_code(204);

        exit;
    }

    public static function download(
        string $file,
        ?string $filename = null
    ): never {

        if (!is_file($file)) {
            self::notFound();
        }

        $filename ??= basename($file);

        header('Content-Type: application/octet-stream');
        header(
            'Content-Disposition: attachment; filename="' .
            $filename .
            '"'
        );
        header('Content-Length: ' . filesize($file));

        readfile($file);

        exit;
    }

    public static function file(
        string $file,
        string $contentType
    ): never {

        if (!is_file($file)) {
            self::notFound();
        }

        header(
            'Content-Type: ' . $contentType
        );

        readfile($file);

        exit;
    }
}