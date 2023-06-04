<?php

namespace Robbkore\ProductExporterBundle\Event;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Pimcore\Model\DataObject\Product;
use Psr\Log\LoggerInterface;

class UpdateProductEvent
{
    public function __construct(
        private LoggerInterface $logger,
        private ClientInterface $client
    )
    {

    }

    public function update(int $productId, Product $product): void
    {
        // Should be mapped before we get here, but this ties it up nicely for the moment.
        $product = [
            'product' => [
                'variant' => [
                    'price' => $product->getPrice(),
                    'sku' => $product->getSku(),
                ],
                'title' => $product->getName(),
                'product_type' => $product->getMedia_type(),
                'product_id' => $productId,
            ],
        ];

        try {
            $response = $this->client->request('PUT', $_ENV['SHOPIFY_URI'] . 'products/' . $productId . '.json', [
                'form_params' => $product,
                'headers' => [
                    'Accept' => 'application/json',
                    'X-Shopify-Access-Token' => $_ENV['SHOPIFY_TOKEN']
                ]
            ]);

            // Trapping 300+ status codes for reference.
            if ($response->getStatusCode() > 299) {
                $this->logger->error('UpdateProductEvent: Error (' .$response->getStatusCode() . ') ' . $response->getReasonPhrase());
                return;
            }
        } catch (GuzzleException $e) {
            // Add me to a queue or persist the command body here, we can do retries later on.
            // Could bubble back a more informative message with that change and add a backend process (command/script) to retry failed transmissions
            $this->logger->error('UpdateProductEvent Exception (' .  $e->getCode() . ') ' . $e->getMessage());
            return;
        }

        $this->logger->info('UpdateProductEvent product ' . $product['product']['title']  . $productId . ' updated.');
    }
}
