<?php

namespace Upay\Utils\Exceptions;

class UpayError extends \Exception
{
    public ?string $code;
    public ?int $status;
    public ?array $details;
    
    public function __construct(
        string $message,
        ?string $code = null,
        ?int $status = null,
        ?array $details = null
    ) {
        parent::__construct($message);
        $this->code = $code;
        $this->status = $status;
        $this->details = $details;
    }
}

class UpayAuthenticationError extends UpayError
{
    public function __construct(string $message = 'Falha na autenticação. Verifique sua API key.')
    {
        parent::__construct($message, 'AUTHENTICATION_ERROR', 401);
    }
}

class UpayValidationError extends UpayError
{
    public function __construct(string $message, ?array $details = null)
    {
        parent::__construct($message, 'VALIDATION_ERROR', 400, $details);
    }
}

class UpayNotFoundError extends UpayError
{
    public function __construct(string $resource, ?string $resourceId = null)
    {
        $message = $resourceId 
            ? "{$resource} com ID {$resourceId} não encontrado."
            : "{$resource} não encontrado.";
        parent::__construct($message, 'NOT_FOUND', 404);
    }
}

class UpayRateLimitError extends UpayError
{
    public function __construct(string $message = 'Limite de requisições excedido. Tente novamente mais tarde.')
    {
        parent::__construct($message, 'RATE_LIMIT_ERROR', 429);
    }
}

class UpayServerError extends UpayError
{
    public function __construct(string $message = 'Erro interno do servidor. Tente novamente mais tarde.')
    {
        parent::__construct($message, 'SERVER_ERROR', 500);
    }
}
