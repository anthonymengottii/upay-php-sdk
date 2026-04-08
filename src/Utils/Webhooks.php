<?php

namespace Upay\Utils;

class Webhooks
{
    /**
     * Verifica a assinatura de um webhook usando HMAC SHA256
     * 
     * @param string $payload Corpo da requisição
     * @param string $signature Assinatura recebida no header
     * @param string $secret Secret da API key
     * @return bool True se a assinatura for válida
     */
    public static function verify(string $payload, string $signature, string $secret): bool
    {
        if (empty($payload) || empty($signature) || empty($secret)) {
            return false;
        }
        
        try {
            $expectedSignature = hash_hmac('sha256', $payload, $secret);
            return hash_equals($expectedSignature, $signature);
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Extrai a assinatura do header da requisição
     * 
     * @param array $headers Headers da requisição
     * @return string|null A assinatura ou null se não encontrada
     */
    public static function extractSignature(array $headers): ?string
    {
        $signatureHeader = 
            $headers['x-upay-signature'] ??
            $headers['x-upay-signature-256'] ??
            $headers['upay-signature'] ??
            $headers['signature'] ??
            null;
        
        if (!$signatureHeader) {
            return null;
        }
        
        // Remove prefixo "sha256=" se existir
        return str_replace('sha256=', '', $signatureHeader);
    }
}
