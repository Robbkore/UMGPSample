<?php

namespace Robbkore\ProductExporterBundle\Tests\Listener;

use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Pimcore\Event\Model\DataObjectEvent;
use Pimcore\Model\DataObject\Product;
use Psr\Log\LoggerInterface;
use Robbkore\ProductExporterBundle\Listener\ProductUpdateListener;
use Robbkore\ProductExporterBundle\Repository\ShopifyProductRepository;
use Robbkore\ProductExporterBundle\Service\ProductService;

class ProductUpdateListenerTest extends TestCase
{
    private ProductUpdateListener $listener;
    private ProductService $productService;

    protected function setUp(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $productRepository = $this->createMock(ShopifyProductRepository::class);
        $this->productService = $this->createMock(ProductService::class);

        $this->listener = new ProductUpdateListener($logger, $productRepository, $this->productService);
    }

    /**
     * @throws Exception
     */
    public function testOnObjectPostUpdateUnpublished(): void
    {
        $this->productService->expects($this->never())->method('save');
        $dataObject = $this->createMock(Product::class);
        $dataObject->method('isPublished')->willReturn(false);
        $dataObject->method('getSku')->willReturn('test-sku');

        $eventElement = $this->createMock(DataObjectEvent::class);
        $eventElement->method('getObject')->willReturn($dataObject);

        $this->listener->onObjectPostUpdate($eventElement);
    }

    /**
     * @throws Exception
     */
    public function testOnObjectPostUpdatePublished(): void
    {
        $this->productService->expects($this->once())->method('save');
        $dataObject = $this->createMock(Product::class);
        $dataObject->method('isPublished')->willReturn(true);
        $dataObject->method('getSku')->willReturn('test-sku');

        $eventElement = $this->createMock(DataObjectEvent::class);
        $eventElement->method('getObject')->willReturn($dataObject);
        $this->listener->onObjectPostUpdate($eventElement);
    }
}
