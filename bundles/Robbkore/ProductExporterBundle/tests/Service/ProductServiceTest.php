<?php

namespace Robbkore\ProductExporterBundle\Tests\Listener;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Pimcore\Model\DataObject\Product;
use Psr\Log\LoggerInterface;
use Robbkore\ProductExporterBundle\Service\ProductService;

class ProductServiceTest extends TestCase
{
    private ProductService $productService;

    private mixed $client;
    private mixed $response;
    private mixed $dataObject;
    private mixed $logger;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->client = $this->createMock(Client::class);
        $this->response = $this->createMock(Response::class);
        $this->dataObject = $this->createMock(Product::class);
        $this->dataObject->method('getSku')->willReturn('test-sku');

        $this->productService = new ProductService($this->logger, $this->client);
    }

    /**
     * @throws Exception
     */
    public function testSave(): void
    {
        $this->response->method('getStatusCode')->willReturn(200);

        $this->client->expects($this->once())->method('request')->willReturn($this->response);

        $this->logger->expects($this->once())->method('info')->with('ProductService completed successfully.');

        $this->productService->save($this->dataObject, 123);
    }

    public function testSaveFailsGracefully(): void
    {
        $this->response->method('getStatusCode')->willReturn(500);
        $this->response->method('getReasonPhrase')->willReturn('Internal Server Error');
        $this->client->expects($this->once())->method('request')->willReturn($this->response);

        $this->logger->expects($this->once())->method('error')->with('ProductService: Error (500) Internal Server Error');
        $this->productService->save($this->dataObject);
    }

    /**
     * @throws Exception
     */
    public function testExceptionCatching() : void
    {
        $mockException = $this->createMock(GuzzleException::class);

        $this->client->expects($this->once())->method('request')->willThrowException($mockException);

        $this->logger->expects($this->once())->method('error')->with('ProductService Exception (0) ');
        $this->productService->save($this->dataObject);
    }
}
