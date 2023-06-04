<?php

namespace Robbkore\ProductExporterBundle\Event;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Pimcore\Logger;
use Pimcore\Model\DataObject\Product;


class CreateProductEvent {
    /**
     * @throws GuzzleException
     */
    public function create(Product $product): void {
        $logger = new Logger();
        // Should be mapped before we get here, but this ties it up nicely for the moment.
        $product = [
            'product' => [
                'variant' => [
                    'price' => $product->getPrice(),
                    'sku' => $product->getSku(),
                ],
                'title' => $product->getName(),
                'product_type' => $product->getMedia_type()
            ],
        ];

        $logger->info('Shopify Uri: ' . $_ENV['SHOPIFY_URI']);

        $client = new Client([
            'base_uri' => $_ENV['SHOPIFY_URI'],
            'timeout' => 2.0
        ]);

        try {
            $response = $client->request('POST', '/admin/api/2023-04/products.json', [
                'form_params' => $product,
                'headers' => [
                    'Accept'     => 'application/json',
                    'X-Shopify-Access-Token'      => $_ENV['SHOPIFY_TOKEN']
                ]
            ]);
        } catch (GuzzleException $e) {
            // You could fire an event, queue a message or add a sort of reconciliation log behind this, so we can trap failures and retry later
            // Could bubble back a more informative message with that change and add a backend process (command/script) to retry failed transmissions
            $logger->error('CreateProductEvent Error (' .  $e->getCode() . ') ' . $e->getMessage());
            return;
        }

        $logger->info('CreateProductEvent completed successfully.');
        $logger->info('CreateProductEvent Object: ' . print_r($response->getBody(), true));
    }
}
