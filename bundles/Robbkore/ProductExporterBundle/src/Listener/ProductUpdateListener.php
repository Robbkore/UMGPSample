<?php

namespace Robbkore\ProductExporterBundle\Listener;

use Pimcore\Logger;
use Pimcore\Event\Model\ElementEventInterface;

class ProductUpdateListener {
    /**
     * @throws \Exception
     */
    public function onObjectPostUpdate(ElementEventInterface $eventElement): void {
        $logger = new Logger();
        $logger->info('ProductUpdateListener::onObjectPostUpdate');
    }
}
