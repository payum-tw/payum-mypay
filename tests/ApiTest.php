<?php

namespace PayumTW\Mypay\Tests;

use Mockery as m;
use PayumTW\Mypay\Api;
use PHPUnit\Framework\TestCase;

class ApiTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testGetApiEndpoint()
    {
        $api = new Api(
            $options = ['sandbox' => false, 'key' => md5(rand())],
            $httpClient = m::mock('Payum\Core\HttpClientInterface'),
            $messageFactory = m::mock('Http\Message\MessageFactory'),
            $encrypter = m::mock('PayumTW\Mypay\Encrypter')
        );
        $this->assertSame('https://mypay.tw/api/init', $api->getApiEndpoint());

        $api = new Api(
            $options = ['sandbox' => true, 'key' => md5(rand())],
            $httpClient = m::mock('Payum\Core\HttpClientInterface'),
            $messageFactory = m::mock('Http\Message\MessageFactory'),
            $encrypter = m::mock('PayumTW\Mypay\Encrypter')
        );
        $this->assertSame('https://pay.usecase.cc/api/init', $api->getApiEndpoint());
    }

    public function testCreateTransaction()
    {
        $api = new Api(
            $options = [
                'store_uid' => $storeUid = '123',
                'key' => $key = md5(rand()),
                'ip' => $ip = '::1',
                'sandbox' => true,
            ],
            $httpClient = m::mock('Payum\Core\HttpClientInterface'),
            $messageFactory = m::mock('Http\Message\MessageFactory'),
            $encrypter = m::mock('PayumTW\Mypay\Encrypter')
        );

        $params = [
            'user_id' => $userId = 'phper',
            'item' => $count = 1,
            'items' => [
                [
                    'id' => $itemId = '0886449',
                    'name' => $itemName = '商品名稱',
                    'price' => $cost = 10,
                    'quantity' => $quantity = 1,
                    'total' => $total = 10,
                ],
            ],
            'order_id' => $orderId = '1234567890',
        ];

        $encrypter->shouldReceive('encrypt')->once()->with(json_encode(['service_name' => 'api', 'cmd' => 'api/orders']))->andReturn($service = 'foo');
        $encrypter->shouldReceive('encrypt')->once()->with(json_encode([
            'store_uid' => $storeUid,
            'user_id' => $userId,
            'cost' => 10,
            'order_id' => $orderId,
            'ip' => $ip,
            'item' => $count,
            'pfn' => 'CREDITCARD',
            'i_0_id' => $itemId,
            'i_0_name' => $itemName,
            'i_0_cost' => $cost,
            'i_0_amount' => $quantity,
            'i_0_total' => $total,
        ]))->andReturn($encryData = 'foo');

        $messageFactory->shouldReceive('createRequest')->once()->with(
            'POST',
            $api->getApiEndpoint(),
            [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            http_build_query([
                'store_uid' => $storeUid,
                'service' => $service,
                'encry_data' => $encryData,
            ])
        )->andReturn(
            $request = m::mock('Psr\Http\Message\RequestInterface')
        );

        $httpClient->shouldReceive('send')->once()->with($request)->andReturn(
            $response = m::mock('Psr\Http\Message\ResponseInterface')
        );

        $response->shouldReceive('getStatusCode')->once()->andReturn(200);
        $response->shouldReceive('getBody->getContents')->andReturn(
            json_encode($content = ['foo' => 'bar'])
        );

        $this->assertSame($content, $api->createTransaction($params));
    }

    public function testGetTransactionData()
    {
        $api = new Api(
            $options = [
                'store_uid' => $storeUid = '123',
                'key' => $key = md5(rand()),
                'ip' => $ip = '::1',
                'sandbox' => true,
            ],
            $httpClient = m::mock('Payum\Core\HttpClientInterface'),
            $messageFactory = m::mock('Http\Message\MessageFactory'),
            $encrypter = m::mock('PayumTW\Mypay\Encrypter')
        );

        $params = [
            'uid' => $uid = md5(rand()),
            'key' => $key = md5(rand()),
        ];

        $encrypter->shouldReceive('encrypt')->once()->with(json_encode(['service_name' => 'api', 'cmd' => 'api/queryorder']))->andReturn($service = 'foo');
        $encrypter->shouldReceive('encrypt')->once()->with(json_encode($params))->andReturn($encryData = 'foo');

        $messageFactory->shouldReceive('createRequest')->once()->with(
            'POST',
            $api->getApiEndpoint(),
            [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            http_build_query([
                'store_uid' => $storeUid,
                'service' => $service,
                'encry_data' => $encryData,
            ])
        )->andReturn(
            $request = m::mock('Psr\Http\Message\RequestInterface')
        );

        $httpClient->shouldReceive('send')->once()->with($request)->andReturn(
            $response = m::mock('Psr\Http\Message\ResponseInterface')
        );

        $response->shouldReceive('getStatusCode')->once()->andReturn(200);
        $response->shouldReceive('getBody->getContents')->andReturn(
            json_encode($content = ['foo' => 'bar'])
        );

        $this->assertSame($content, $api->getTransactionData($params));
    }

    public function testVerifyHash()
    {
        $api = new Api(
            $options = [
                'store_uid' => $storeUid = '123',
                'key' => $key = md5(rand()),
                'ip' => $ip = '::1',
                'sandbox' => true,
            ],
            $httpClient = m::mock('Payum\Core\HttpClientInterface'),
            $messageFactory = m::mock('Http\Message\MessageFactory'),
            $encrypter = m::mock('PayumTW\Mypay\Encrypter')
        );
        $this->assertTrue($api->verifyHash(['key' => 'key'], ['key' => 'key']));
    }
}
