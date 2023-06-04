<?php

namespace Robbkore\ProductExporterBundle\Listener;

use Pimcore\Event\Model\DataObjectEvent;
use Pimcore\Event\Model\ElementEventInterface;
use Pimcore\Model\DataObject\Product;
use Psr\Log\LoggerInterface;
use Robbkore\ProductExporterBundle\Event\CreateProductEvent;
use Robbkore\ProductExporterBundle\Event\UpdateProductEvent;
use Robbkore\ProductExporterBundle\Repository\ShopifyProductRepository;

class ProductUpdateListener
{
    public function __construct(
        private LoggerInterface $logger,
        private ShopifyProductRepository $productRepository,
        private CreateProductEvent $createProductEvent,
        private UpdateProductEvent $updateProductEvent
    )
    {
    }

    public function onObjectPostUpdate(ElementEventInterface $eventElement): void
    {
        $this->logger->info('ProductUpdateListener: Product Exporter Started.');
        // Bail early. In theory, this shouldn't happen unless someone changes the configuration and tries to pass in other events to the listener.
        if (!($eventElement instanceof DataObjectEvent)) {
            $this->logger->error('ProductUpdateListener: This listener can only be bound to DataObjectEvents. Please check your service configuration.');
            return;
        }

        $dataObject = $eventElement->getObject();

        if ($dataObject instanceof Product) {
            $isPublished = $dataObject->isPublished();

            // If the product is not published, just log that there was nothing updated.
            // In a more robust solution, we'd probably tie Pimcore published status to the Shopify draft status
            if (!$isPublished) {
                $this->logger->info('ProductUpdateListener: Product is not published. Skipping.');
                return;
            }

            $shopifyProductId = $this->productRepository->getIdForSku($dataObject->getSku());

            if (!is_null($shopifyProductId)) {
                $this->updateProductEvent->update($shopifyProductId, $dataObject);
                $this->logger->info('Updated Product: ' . $shopifyProductId);
            } else {
                $this->createProductEvent->create($dataObject);
                $this->logger->info('ProductUpdateListener: New Product Added.');
            }

            $this->logger->info('ProductUpdateListener: Product Exporter Complete.');
        }
    }
}
