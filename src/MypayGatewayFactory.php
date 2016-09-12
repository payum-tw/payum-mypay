<?php

namespace PayumTW\Mypay;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
use PayumTW\Mypay\Action\CaptureAction;
use PayumTW\Mypay\Action\ConvertPaymentAction;
use PayumTW\Mypay\Action\NotifyAction;
use PayumTW\Mypay\Action\NotifyNullAction;
use PayumTW\Mypay\Action\StatusAction;
use PayumTW\Mypay\Action\SyncAction;

class MypayGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritdoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults([
            'payum.factory_name' => 'mypay',
            'payum.factory_title' => 'Mypay',
            'payum.action.capture' => new CaptureAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
            'payum.action.notify' => new NotifyAction(),
            'payum.action.notify_null' => new NotifyNullAction(),
            'payum.action.sync' => new SyncAction(),
        ]);

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = [
                'store_uid' => null,
                'key' => null,
                'ip' => $_SERVER['REMOTE_ADDR'],
                'sandbox' => true,
            ];

            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = ['store_uid', 'key'];

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                return new Api((array) $config, $config['payum.http_client'], $config['httplug.message_factory']);
            };
        }
    }
}