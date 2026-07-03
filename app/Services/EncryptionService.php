<?php

declare(strict_types=1);

namespace AEFS\Services;

use RuntimeException;

final class EncryptionService
{
    private const CIPHER = 'AES-256-CBC';

    private string $key;

    public function __construct()
    {
        $config = require dirname(__DIR__, 2) . '/config/app.php';

        if (
            !isset($config['app_key']) ||
            trim($config['app_key']) === ''
        ) {
            throw new RuntimeException(
                'Geen app_key gevonden in config/app.php.'
            );
        }

        $this->key = hash(
            'sha256',
            $config['app_key'],
            true
        );
    }

    public function encrypt(?string $value): ?string
    {
        if (
            $value === null ||
            trim($value) === ''
        ) {
            return null;
        }

        $ivLength = openssl_cipher_iv_length(
            self::CIPHER
        );

        $iv = random_bytes($ivLength);

        $encrypted = openssl_encrypt(
            $value,
            self::CIPHER,
            $this->key,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($encrypted === false) {

            throw new RuntimeException(
                'Encryptie mislukt.'
            );

        }

        return base64_encode(
            $iv . $encrypted
        );
    }

    public function decrypt(?string $value): ?string
    {
        if (
            $value === null ||
            trim($value) === ''
        ) {
            return null;
        }

        $binary = base64_decode(
            $value,
            true
        );

        if ($binary === false) {

            return $value;

        }

        $ivLength = openssl_cipher_iv_length(
            self::CIPHER
        );

        $iv = substr(
            $binary,
            0,
            $ivLength
        );

        $encrypted = substr(
            $binary,
            $ivLength
        );

        $plain = openssl_decrypt(
            $encrypted,
            self::CIPHER,
            $this->key,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($plain === false) {

            return $value;

        }

        return $plain;
    }

    public function encryptArray(
        array $data,
        array $velden
    ): array
    {
        foreach ($velden as $veld) {

            if (array_key_exists($veld, $data)) {

                $data[$veld] = $this->encrypt(
                    $data[$veld]
                );

            }

        }

        return $data;
    }

    public function decryptArray(
        array $data,
        array $velden
    ): array
    {
        foreach ($velden as $veld) {

            if (array_key_exists($veld, $data)) {

                $data[$veld] = $this->decrypt(
                    $data[$veld]
                );

            }

        }

        return $data;
    }
}