<?php

use Mockery as m;
use PayumTW\Mypay\Encrypter;

class EncrypterTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_encrypt()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $key = md5(rand());
        $params = [
            'item' => 1,
            'items' => [
                [
                    'id' => '0886449',
                    'name' => '商品名稱',
                    'cost' => 10,
                    'amount' => '1',
                    'total' => 10,
                ],
            ],
            'user_id' => 'phper',
            'order_id' => '1234567890',
            'ip' => '::1',
        ];

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $encrypter = new Encrypter($key);
        $encrypter->setKey($key);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $encrypt = $encrypter->encrypt(json_encode($params));
        $this->assertSame($params, json_decode($encrypter->decrypt($encrypt), true));

        if (version_compare(PHP_VERSION, '7.1', '<') === true) {
            $encrypt = $encrypter->encrypt(json_encode($params));
            $this->assertSame($params, json_decode($encrypter->decryptByPHP($encrypt), true));

            $encrypt = $encrypter->encryptByPHP(json_encode($params));
            $this->assertSame($params, json_decode($encrypter->decrypt($encrypt), true));

            $encrypt = $encrypter->encryptByPHP(json_encode($params));
            $this->assertSame($params, json_decode($encrypter->decryptByPHP($encrypt), true));
        }
    }
}
