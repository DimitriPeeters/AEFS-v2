<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Application
    |--------------------------------------------------------------------------
    */

    'name' => 'AEFS',

    /*
    |--------------------------------------------------------------------------
    | Base URL
    |--------------------------------------------------------------------------
    |
    | Leeg laten = automatisch detecteren.
    | Op one.com kan hier later bv.
    | https://leden.aefs.be ingevuld worden.
    |
    */

    'base_url' => '',

    /*
    |--------------------------------------------------------------------------
    | Environment
    |--------------------------------------------------------------------------
    */

    'environment' => 'development',

    /*
    |--------------------------------------------------------------------------
    | Application Key
    |--------------------------------------------------------------------------
    |
    | Wordt gebruikt voor encryptie van gevoelige gegevens zoals:
    | - rekeningnummer
    | - rijksregisternummer
    | - toekomstige API-sleutels
    |
    | Deze sleutel NOOIT wijzigen nadat de applicatie in productie is,
    | anders kunnen bestaande gegevens niet meer ontsleuteld worden.
    |
    */

    'app_key' => '2d4971b3c58f7d314db7c36cb16d45fb84d2b74af6deac5d537f07bfa90bcb72',

];