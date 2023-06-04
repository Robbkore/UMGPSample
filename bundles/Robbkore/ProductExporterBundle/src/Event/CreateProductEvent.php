<?php

namespace Robbkore\ProductExporterBundle\Event;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Pimcore\Model\DataObject\Product;
use Psr\Log\LoggerInterface;

class CreateProductEvent
{
    public function __construct(
        private LoggerInterface $logger,
        private ClientInterface $client // I'd probably wire this differently so that it expected a ClientInterface which wrapped Guzzle, which seems more flexible
    )
    {
    }

    /**
     * @param Product $product
     */
    public function create(Product $product): void
    {
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

        try {
            $response = $this->client->request('POST', $_ENV['SHOPIFY_URI'] . $_ENV['SHOPIFY_PRODUCT_ENDPOINT'], [
                'form_params' => $product,
                'headers' => [
                    'Accept'     => 'application/json',
                    'X-Shopify-Access-Token'      => $_ENV['SHOPIFY_TOKEN']
                ]
            ]);

            // Trapping 300+ status codes for reference.
            if ($response->getStatusCode() > 299) {
                $this->logger->error('CreateProductEvent: Error (' .$response->getStatusCode() . ') ' . $response->getReasonPhrase());
                return;
            }
        } catch (GuzzleException $e) {
            // You could fire an event, queue a message or add a sort of reconciliation log behind this, so we can trap failures and retry later
            // Could bubble back a more informative message with that change and add a backend process (command/script) to retry failed transmissions
            $this->logger->error('CreateProductEvent Error (' .  $e->getCode() . ') ' . $e->getMessage());
            return;
        }

        $this->logger->info('CreateProductEvent completed successfully.');
        $this->logger->info('CreateProductEvent Object: ' . print_r(json_decode($response->getBody()->getContents()), true));
    }
}
