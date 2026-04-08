<?php

/**
 * Exemplo básico de uso do SDK Upay PHP
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Upay\UpayClient;

// Inicializar o cliente
$upay = new UpayClient(
    apiKey: "sua_api_key_aqui",
    baseUrl: "http://localhost:3001"  // Para desenvolvimento local
);

echo "=== Exemplo: Payment Links ===\n\n";

// Criar um link de pagamento
echo "1. Criando link de pagamento...\n";
$paymentLink = null;
$paymentLinkCreated = false;
try {
    $paymentLink = $upay->paymentLinks->create([
        'title' => 'Produto Premium',
        'amount' => 10000,  // R$ 100,00
        'description' => 'Descrição do produto',
        'status' => 'ACTIVE'
    ]);
    
    // Validar que o retorno é um array com as chaves necessárias
    if (!is_array($paymentLink) || !isset($paymentLink['id']) || !isset($paymentLink['slug'])) {
        echo "   Erro: Resposta inválida do servidor. Chaves esperadas: id, slug\n";
        echo "   Resposta recebida: " . json_encode($paymentLink) . "\n\n";
    } else {
        echo "   Link criado: {$paymentLink['id']}\n";
        echo "   Slug: {$paymentLink['slug']}\n";
        echo "   URL: " . $upay->paymentLinks->getCheckoutUrl($paymentLink['slug']) . "\n\n";
        $paymentLinkCreated = true;
    }
} catch (\Exception $e) {
    echo "   Erro ao criar link: {$e->getMessage()}\n";
    if ($e->getCode()) {
        echo "   Código: {$e->getCode()}\n";
    }
    echo "\n";
}

// Listar links
echo "2. Listando links de pagamento...\n";
try {
    $links = $upay->paymentLinks->list(['page' => 1, 'limit' => 5]);
    echo "   Total de links: " . ($links['pagination']['total'] ?? 0) . "\n";
    if (!empty($links['data'])) {
        echo "   Primeiro link: {$links['data'][0]['title']}\n\n";
    }
} catch (\Exception $e) {
    echo "   Erro: {$e->getMessage()}\n\n";
}

// Deletar o link de teste
if ($paymentLinkCreated && isset($paymentLink['id'])) {
    echo "3. Deletando link de teste...\n";
    try {
        $upay->paymentLinks->delete($paymentLink['id']);
        echo "   Link deletado com sucesso\n\n";
    } catch (\Exception $e) {
        echo "   Erro ao deletar: {$e->getMessage()}\n\n";
    }
}

echo "=== Exemplo: Transações ===\n\n";

// Listar transações
echo "1. Listando transações...\n";
$transactions = $upay->transactions->list(['page' => 1, 'limit' => 5]);
echo "   Total de transações: " . ($transactions['pagination']['total'] ?? 0) . "\n";
if (!empty($transactions['data'])) {
    $tx = $transactions['data'][0];
    $amount = number_format($tx['amountCents'] / 100, 2, ',', '.');
    echo "   Primeira transação: {$tx['product']} - R$ {$amount}\n\n";
}

echo "=== Exemplo: Produtos ===\n\n";

// Listar produtos
echo "1. Listando produtos...\n";
$products = $upay->products->list(['page' => 1, 'limit' => 5]);
echo "   Total de produtos: " . ($products['pagination']['total'] ?? 0) . "\n";
if (!empty($products['data'])) {
    $product = $products['data'][0];
    $price = number_format($product['price'] / 100, 2, ',', '.');
    echo "   Primeiro produto: {$product['name']} - R$ {$price}\n\n";
}

echo "Todos os exemplos executados com sucesso!\n";
