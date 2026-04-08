<?php

namespace Upay\Resources;

use Upay\HttpClient;

class Coupons
{
    private HttpClient $http;
    
    public function __construct(HttpClient $http)
    {
        $this->http = $http;
    }
    
    /**
     * Valida um cupom de desconto
     * 
     * Nota: Este endpoint é público e não requer autenticação
     * Endpoint: POST /api/coupons/validate (não /api/v1)
     */
    public function validate(string $code, int $amountCents, ?array $productIds = null): array
    {
        if (empty($code) || strlen(trim($code)) === 0) {
            throw new \InvalidArgumentException('Código do cupom é obrigatório');
        }
        
        if ($amountCents < 100) {
            throw new \InvalidArgumentException('Valor mínimo é R$ 1,00 (100 centavos)');
        }
        
        // Endpoint público em /api/coupons/validate (sem /v1)
        $data = [
            'code' => trim($code),
            'amount' => $amountCents,
            'productIds' => $productIds ?? [],
        ];
        
        // Faz chamada direta pois 400 com {valid:false} é resposta válida
        $url = $this->http->baseUrl . '/api/coupons/validate';
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => $this->http->timeout,
            CURLOPT_POSTFIELDS => json_encode($data),
        ]);
        $raw = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($raw, true) ?? [];
        
        // Normalizar resposta para o formato esperado
        return [
            'valid' => $result['valid'] ?? false,
            'discountCents' => $result['discountAmount'] ?? 0,
            'discountPercentage' => $result['coupon']['discountPercentage'] ?? null,
            'finalAmountCents' => $result['finalAmount'] ?? $amountCents,
            'message' => $result['error'] ?? $result['message'] ?? null,
        ];
    }
}
