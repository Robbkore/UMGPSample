<?php

namespace Robbkore\ProductExporterBundle\Service;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Pimcore\Model\DataObject\Product;
use Psr\Log\LoggerInterface;

class ProductService
{
    public function __construct(private LoggerInterface $logger, private ClientInterface $client)
    {
    }

    public function save(Product $product, ?int $existingProductId = null): void
    {
        $productRequest = $this->buildProductRequest($product);

        $method = $existingProductId ? 'PUT' : 'POST';
        $url = $existingProductId ? 'products/' . $existingProductId . '.json' : 'products.json';
        $endpoint = $_ENV['SHOPIFY_URI'] . $url;

        try {
            $response = $this->client->request($method , $endpoint, [
                'form_params' => $productRequest,
                'headers' => [
                    'Accept'     => 'application/json',
                    'X-Shopify-Access-Token'      => $_ENV['SHOPIFY_TOKEN']
                ]
            ]);

            // Trapping 300+ status codes for reference.
            if ($response->getStatusCode() > 299) {
                $this->logger->error('ProductService: Error (' .$response->getStatusCode() . ') ' . $response->getReasonPhrase());
                return;
            }
        } catch (GuzzleException $e) {
            // You could fire an event, queue a message or add a sort of reconciliation log behind this, so we can trap failures and retry later
            // Could bubble back a more informative message with that change and add a backend process (command/script) to retry failed transmissions
            $this->logger->error('ProductService Error (' .  $e->getCode() . ') ' . $e->getMessage());
            return;
        }

        $this->logger->info('ProductService completed successfully.');
    }

    private function buildProductRequest(Product $product) : array
    {
        return [
            'product' => [
                'variant' => [
                    'price' => $product->getPrice(),
                    'sku' => $product->getSku(),
                ],
                'title' => $product->getName(),
                'product_type' => $product->getMedia_type()
            ],
        ];
    }
}
