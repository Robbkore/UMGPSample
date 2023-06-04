<?php

namespace Robbkore\ProductExporterBundle\Listener;

use Pimcore\Event\Model\DataObjectEvent;
use Pimcore\Logger;
use Pimcore\Event\Model\ElementEventInterface;
use Pimcore\Model\DataObject\Product;
use Robbkore\ProductExporterBundle\Event\CreateProductEvent;

class ProductUpdateListener {
    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function onObjectPostUpdate(ElementEventInterface $eventElement): void {
        $logger = new Logger();
        $logger->info('ProductUpdateListener::onObjectPostUpdate called');

        // Bail early. In theory, this shouldn't happen unless someone changes the configuration and tries to pass in other events to the listener.
        if (!($eventElement instanceof DataObjectEvent)) {
            $logger->error('ProductUpdateListener: This listener can only be bound to DataObjectEvents. Please check your service configuration.');
            return;
        }

        $dataObject = $eventElement->getObject();

        if ($dataObject instanceof Product) {
            $isPublished = $dataObject->isPublished();

            // If the product is not published, just log that there was nothing updated.
            if (!$isPublished) {
                $logger->info('ProductUpdateListener: Product is not published.');
                return;
            }

            $event = new CreateProductEvent();
            $event->create($dataObject);
            $logger->info('ProductUpdateListener: Product Exporter Complete.');
        }
    }
}
