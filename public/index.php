<?php
use Symfony\Component\HttpFoundation\Request;

if ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '' === 'https') {
    Request::setTrustedProxies(
        [$_SERVER['REMOTE_ADDR']],
        Request::HEADER_X_FORWARDED_PROTO
    );
}

use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
