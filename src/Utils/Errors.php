<?php

namespace Upay\Utils;

use Upay\Utils\Exceptions\UpayError;
use Upay\Utils\Exceptions\UpayAuthenticationError;
use Upay\Utils\Exceptions\UpayValidationError;
use Upay\Utils\Exceptions\UpayNotFoundError;
use Upay\Utils\Exceptions\UpayRateLimitError;
use Upay\Utils\Exceptions\UpayServerError;

class Errors
{
    public static function handle(int $statusCode, array $body): \Exception
    {
        $message = $body['message'] ?? "HTTP {$statusCode}";
        $code = $body['code'] ?? null;
        
        switch ($statusCode) {
            case 401:
                return new UpayAuthenticationError($message);
            case 400:
                $details = $body['details'] ?? null;
                return new UpayValidationError($message, $details);
            case 404:
                $resourceId = $body['id'] ?? null;
                return new UpayNotFoundError('Recurso', $resourceId);
            case 429:
                return new UpayRateLimitError($message);
            case 500:
            case 502:
            case 503:
                return new UpayServerError($message);
            default:
                return new UpayError($message, $code, $statusCode, $body);
        }
    }
}
