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
            'amountCents' => $amountCents,
            'productIds' => $productIds ?? [],
        ];
        
        $result = $this->http->postPublic('/api/coupons/validate', $data);
        
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
