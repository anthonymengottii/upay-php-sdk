# Upay PHP SDK

SDK oficial da Upay para PHP - Integração fácil com a API de pagamentos.

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP 7.4+](https://img.shields.io/badge/php-7.4+-blue.svg)](https://www.php.net/downloads)

## 📦 Instalação

```bash
composer require upay/upay-php
```

Ou adicione ao seu `composer.json`:

```json
{
    "require": {
        "upay/upay-php": "^1.0"
    }
}
```

## 🚀 Início Rápido

```php
<?php

require_once 'vendor/autoload.php';

use Upay\UpayClient;

// Inicializar o cliente
$upay = new UpayClient(
    apiKey: "sua_api_key_aqui"
);

// Criar um link de pagamento
$paymentLink = $upay->paymentLinks->create([
    'title' => 'Produto Premium',
    'amount' => 10000,  // R$ 100,00 em centavos
    'description' => 'Descrição do produto'
]);

echo "Link criado: " . $upay->paymentLinks->getCheckoutUrl($paymentLink['slug']) . "\n";

// Listar transações
$transactions = $upay->transactions->list(['page' => 1, 'limit' => 10]);
echo "Total de transações: " . $transactions['pagination']['total'] . "\n";
```

## 📚 Recursos Disponíveis

### Payment Links

```php
// Criar link
$link = $upay->paymentLinks->create([
    'title' => 'Produto',
    'amount' => 10000,
    'status' => 'ACTIVE'
]);

// Listar links
$links = $upay->paymentLinks->list(['page' => 1, 'limit' => 10]);

// Obter por ID
$link = $upay->paymentLinks->get($linkId);

// Obter por slug
$link = $upay->paymentLinks->getBySlug('meu-link');

// Atualizar
$upay->paymentLinks->update($linkId, ['status' => 'INACTIVE']);

// Deletar
$upay->paymentLinks->delete($linkId);

// Obter URL do checkout
$url = $upay->paymentLinks->getCheckoutUrl($link['slug']);
```

### Transações

```php
// Criar transação
$transaction = $upay->transactions->create([
    'product' => 'Produto Teste',
    'amountCents' => 10000,
    'paymentMethod' => 'PIX'
]);

// Listar transações
$transactions = $upay->transactions->list([
    'page' => 1,
    'limit' => 10,
    'status' => 'PAID'
]);

// Obter transação
$transaction = $upay->transactions->get($transactionId);

// Processar pagamento
$upay->transactions->process($transactionId, [
    'cardData' => [...],
    'installments' => 3
]);

// Capturar transação
$upay->transactions->capture($transactionId);

// Cancelar transação
$upay->transactions->cancel($transactionId);

// Estornar transação
$upay->transactions->refund($transactionId, amountCents: 5000);
```

### Produtos

```php
// Criar produto
$product = $upay->products->create([
    'name' => 'Produto Teste',
    'price' => 10000,
    'description' => 'Descrição'
]);

// Listar produtos
$products = $upay->products->list(['page' => 1, 'limit' => 10]);

// Obter produto
$product = $upay->products->get($productId);

// Atualizar produto
$upay->products->update($productId, ['price' => 15000]);

// Deletar produto
$upay->products->delete($productId);
```

### Clientes

```php
// Criar cliente
$client = $upay->clients->create([
    'name' => 'João Silva',
    'email' => 'joao@example.com',
    'document' => '12345678900'
]);

// Listar clientes
$clients = $upay->clients->list(['page' => 1, 'limit' => 10]);

// Obter cliente
$client = $upay->clients->get($clientId);

// Atualizar cliente
$upay->clients->update($clientId, ['email' => 'novo@example.com']);
```

### Cupons

```php
// Validar cupom (endpoint público)
$validation = $upay->coupons->validate(
    code: 'CUPOM10',
    amountCents: 10000,
    productIds: ['produto-id-1', 'produto-id-2']
);

if ($validation['valid']) {
    echo "Desconto: R$ " . number_format($validation['discountCents'] / 100, 2, ',', '.') . "\n";
    echo "Valor final: R$ " . number_format($validation['finalAmountCents'] / 100, 2, ',', '.') . "\n";
}
```

### Webhooks

```php
use Upay\UpayClient;

$upay = new UpayClient(apiKey: "sua_api_key");

// Em um controller/endpoint de webhook
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_UPAY_SIGNATURE'] ?? '';
$secret = "seu_webhook_secret";

if ($upay->verifyWebhookSignature($payload, $signature, $secret)) {
    // Processar webhook
    $data = json_decode($payload, true);
    echo "Evento: " . $data['type'] . "\n";
} else {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid signature']);
}
```

## ⚙️ Configuração

```php
$upay = new UpayClient(
    apiKey: "sua_api_key",
    baseUrl: "https://upay-sistema-api.onrender.com",  // Opcional
    version: "v1",  // Opcional
    timeout: 30  // Opcional, em segundos
);
```

## 🔑 Obter API Key

Para usar o SDK, você precisa de uma API Key:

1. Acesse o [Dashboard Upay](https://app.upaybr.com)
2. Vá em **Configurações** > **API Keys**
3. Crie uma nova API Key
4. Copie e use no seu código

## 🛠️ Tratamento de Erros

```php
use Upay\Utils\Exceptions\UpayError;
use Upay\Utils\Exceptions\UpayAuthenticationError;
use Upay\Utils\Exceptions\UpayValidationError;
use Upay\Utils\Exceptions\UpayNotFoundError;

try {
    $link = $upay->paymentLinks->create(['title' => 'Teste']);
} catch (UpayValidationError $e) {
    echo "Erro de validação: " . $e->getMessage() . "\n";
    if ($e->details) {
        print_r($e->details);
    }
} catch (UpayAuthenticationError $e) {
    echo "Erro de autenticação: " . $e->getMessage() . "\n";
} catch (UpayError $e) {
    echo "Erro: " . $e->getMessage() . " (Código: {$e->code})\n";
}
```

## 📝 Exemplos Completos

Veja a pasta `examples/` para exemplos mais detalhados.

## 🔗 Links Úteis

- [Documentação da API](https://docs.upaybr.com)
- [Dashboard](https://app.upaybr.com)
- [Suporte](mailto:suporte@upaybr.com)
- [Repositório](https://github.com/anthonymengottii/upay-php-sdk)
- [Issues](https://github.com/anthonymengottii/upay-php-sdk/issues)

## 📄 Licença

MIT

## 🤝 Contribuindo

Contribuições são bem-vindas! Veja as [issues abertas](https://github.com/anthonymengottii/upay-php-sdk/issues) para sugestões.
