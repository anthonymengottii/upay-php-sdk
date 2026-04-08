<?php

namespace Upay\Resources;

use Upay\HttpClient;

class Products
{
    private HttpClient $http;
    
    public function __construct(HttpClient $http)
    {
        $this->http = $http;
    }
    
    public function create(array $data): array
    {
        // Validação básica
        if (empty($data['name']) || strlen(trim($data['name'])) === 0) {
            throw new \InvalidArgumentException('Nome do produto é obrigatório');
        }
        
        if (empty($data['price']) || $data['price'] < 100) {
            throw new \InvalidArgumentException('Preço mínimo é R$ 1,00 (100 centavos)');
        }
        
        return $this->http->post('/products', $data);
    }
    
    public function list(?array $params = null): array
    {
        $response = $this->http->get('/products', $params);
        return [
            'data' => $response['products'] ?? $response['data'] ?? [],
            'pagination' => $response['pagination'] ?? ['total' => 0, 'page' => 1, 'limit' => 10],
        ];
    }
    
    public function get(string $id): array
    {
        if (empty($id)) {
            throw new \InvalidArgumentException('ID é obrigatório');
        }
        
        return $this->http->get("/products/{$id}");
    }
    
    public function update(string $id, array $data): array
    {
        if (empty($id)) {
            throw new \InvalidArgumentException('ID é obrigatório');
        }
        
        if (isset($data['price']) && $data['price'] < 100) {
            throw new \InvalidArgumentException('Preço mínimo é R$ 1,00 (100 centavos)');
        }
        
        return $this->http->patch("/products/{$id}", $data);
    }
    
    public function delete(string $id): void
    {
        if (empty($id)) {
            throw new \InvalidArgumentException('ID é obrigatório');
        }
        
        $this->http->delete("/products/{$id}");
    }
}
