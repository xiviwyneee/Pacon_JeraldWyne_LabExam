<?php
require __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('login.php');
}

if (!valid_csrf()) {
    http_response_code(403);
    exit('Invalid request. Go back and try signing out again.');
}

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $parameters = session_get_cookie_params();

    setcookie(session_name(), '', [
        'expires' => time() - 42000,
        'path' => $parameters['path'],
        'domain' => $parameters['domain'],
        'secure' => $parameters['secure'],
        'httponly' => $parameters['httponly'],
        'samesite' => 'Lax',
    ]);
}

session_destroy();
redirect('login.php');