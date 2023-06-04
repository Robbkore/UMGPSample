<?php

namespace Robbkore\ProductExporterBundle\Repository;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;

class ShopifyProductRepository
{
    public function __construct(
        private LoggerInterface $logger,
        private Client $client
    )
    {
    }

    public function getIdForSku(string $sku): ?int
    {
        $products = json_decode($this->getAllProducts()); // Eww!

        $matches = [];
        foreach ($products as $product) {
            foreach ($product as $childProduct) {
                if ($sku == $childProduct->variants[0]->sku) {
                    $this->logger->info('ShopifyProductRepository: Found a matching SKU in Shopify => ' . $childProduct->id);
                    array_push($matches, $childProduct->id);
                }
            }
        }

        return $matches[0] ?? null;
    }

    private function getAllProducts(): ?string
    {
        try {
            return $this->client->get($_ENV['SHOPIFY_URI'] . $_ENV['SHOPIFY_PRODUCT_ENDPOINT'], [
                'headers' => [
                    'Accept'     => 'application/json',
                    'X-Shopify-Access-Token'      => $_ENV['SHOPIFY_TOKEN']
                ]
            ])->getBody()->getContents();
        } catch(GuzzleException $e) {
            $this->logger->error('ShopifyProductRepository Exception Thrown: (' .$e->getCode() . ') ' . $e->getMessage());
            return null;
        }
    }
}
