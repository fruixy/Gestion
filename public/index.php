<?php

use App\Kernel;
use Symfony\Component\HttpFoundation\Request;

// 🔥 Correction Railway pour HTTPS
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
}

// 🔥 Correction complète pour Railway Proxies
Request::setTrustedProxies(
    [$_SERVER['REMOTE_ADDR']],
    Request::HEADER_X_FORWARDED_PROTO
);

// Chargement normal Symfony
require_once dirname(DIR).'/vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};