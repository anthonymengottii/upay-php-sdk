<?php

namespace Upay;

use Upay\Utils\Errors;

class HttpClient
{
    private string $apiKey;
    public string $baseUrl;
    private string $version;
    public int $timeout;
    
    public function __construct(
        string $apiKey,
        string $baseUrl,
        string $version = 'v1',
        int $timeout = 30
    ) {
        // Validar apiKey
        if (empty(trim($apiKey))) {
            throw new \InvalidArgumentException('apiKey must not be empty or whitespace');
        }
        
        // Validar baseUrl
        $normalizedBaseUrl = rtrim($baseUrl, '/');
        if (!filter_var($normalizedBaseUrl, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException("baseUrl must be a valid URL. Received: {$baseUrl}");
        }
        
        // Validar timeout
        if ($timeout <= 0 || !is_int($timeout)) {
            throw new \InvalidArgumentException("timeout must be a positive integer. Received: {$timeout}");
        }
        
        // Validar version
        if (empty(trim($version))) {
            throw new \InvalidArgumentException('version must not be empty or whitespace');
        }
        
        $this->apiKey = $apiKey;
        $this->baseUrl = $normalizedBaseUrl;
        $this->version = $version;
        $this->timeout = $timeout;
    }
    
    /**
     * Faz uma requisição HTTP
     * 
     * @param string $method Método HTTP
     * @param string $endpoint Endpoint da API
     * @param array|null $data Dados para enviar no body
     * @param array|null $params Parâmetros de query
     * @return array Resposta da API parseada
     * @throws \Exception
     */
    public function request(
        string $method,
        string $endpoint,
        ?array $data = null,
        ?array $params = null
    ): array {
        $url = "{$this->baseUrl}/api/{$this->version}{$endpoint}";
        
        // Adiciona query params
        if ($params) {
            $params = array_filter($params, fn($v) => $v !== null);
            if (!empty($params)) {
                $url .= '?' . http_build_query($params);
            }
        }
        
        $ch = curl_init($url);
        
        if ($ch === false) {
            throw new \RuntimeException("Failed to initialize cURL for URL: {$url}");
        }
        
        $headers = [
            "Authorization: Bearer {$this->apiKey}",
            "Content-Type: application/json",
            "User-Agent: Upay-PHP-SDK/1.0.0"
        ];
        
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        
        if ($data && in_array($method, ['POST', 'PATCH', 'PUT'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Verificar se curl_exec falhou
        if ($response === false || $response === null || $response === '') {
            $errorMsg = $error ?: 'Unknown cURL error';
            throw new \Exception("cURL request failed: {$errorMsg}. HTTP Code: {$httpCode}. URL: {$url}");
        }
        
        // Verificar se a resposta é uma string válida antes de decodificar JSON
        if (!is_string($response)) {
            throw new \Exception("Invalid response type from cURL. Expected string, got: " . gettype($response));
        }
        
        $body = json_decode($response, true);
        
        // Verificar se json_decode falhou (retorna null para strings inválidas)
        if ($body === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Failed to decode JSON response: " . json_last_error_msg() . ". Response: " . substr($response, 0, 200));
        }
        
        if ($httpCode >= 400) {
            throw Errors::handle($httpCode, $body ?? []);
        }
        
        return $body ?? [];
    }
    
    public function get(string $endpoint, ?array $params = null): array
    {
        return $this->request('GET', $endpoint, null, $params);
    }
    
    public function post(string $endpoint, ?array $data = null): array
    {
        return $this->request('POST', $endpoint, $data);
    }
    
    public function patch(string $endpoint, ?array $data = null): array
    {
        return $this->request('PATCH', $endpoint, $data);
    }
    
    public function delete(string $endpoint): array
    {
        return $this->request('DELETE', $endpoint);
    }
    
    /**
     * Faz uma requisição POST pública (sem autenticação)
     * 
     * @param string $endpoint Endpoint da API (sem /api/v1)
     * @param array|null $data Dados para enviar no body
     * @return array Resposta da API parseada
     * @throws \Exception
     */
    public function postPublic(string $endpoint, ?array $data = null): array
    {
        $url = "{$this->baseUrl}{$endpoint}";
        
        $ch = curl_init($url);
        
        if ($ch === false) {
            throw new \RuntimeException("Failed to initialize cURL for URL: {$url}");
        }
        
        $headers = [
            "Content-Type: application/json",
            "User-Agent: Upay-PHP-SDK/1.0.0"
        ];
        
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        
        if ($data) {
            $jsonData = json_encode($data);
            if ($jsonData === false) {
                curl_close($ch);
                throw new \Exception("Failed to encode JSON: " . json_last_error_msg());
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Verificar se curl_exec falhou
        if ($response === false || $response === null || $response === '') {
            $errorMsg = $error ?: 'Unknown cURL error';
            throw new \Exception("cURL request failed: {$errorMsg}. HTTP Code: {$httpCode}. URL: {$url}");
        }
        
        // Verificar se a resposta é uma string válida antes de decodificar JSON
        if (!is_string($response)) {
            throw new \Exception("Invalid response type from cURL. Expected string, got: " . gettype($response));
        }
        
        $body = json_decode($response, true);
        
        // Verificar se json_decode falhou (retorna null para strings inválidas)
        if ($body === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Failed to decode JSON response: " . json_last_error_msg() . ". Response: " . substr($response, 0, 200));
        }
        
        if ($httpCode >= 400) {
            $errorMessage = $body['message'] ?? "HTTP {$httpCode}";
            throw new \Exception($errorMessage);
        }
        
        return $body ?? [];
    }
}
