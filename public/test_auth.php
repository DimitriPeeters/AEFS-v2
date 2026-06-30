<?php

require dirname(__DIR__) . '/bootstrap.php';

use AEFS\Core\Auth;
use AEFS\Core\Session;

Session::start();

Auth::login([
    'id' => 1,
    'naam' => 'Dimitri',
    'email' => 'test@test.be',
    'rol' => 'Super Admin'
]);

echo '<pre>';
print_r(Auth::user());
echo '</pre>';