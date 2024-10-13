<?php

declare(strict_types=1);

namespace SyliusIcepayPlugin\Payum;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
use SyliusIcepayPlugin\Payum\Action\StatusAction;

final class IcepayGatewayFactory extends GatewayFactory
{
    protected function populateConfig(ArrayObject $config): void
    {
        $config->defaults([
            'payum.factory_name' => 'icepay',
            'payum.factory_title' => 'ICEPAY',
            'payum.action.status' => new StatusAction(),
        ]);

        $config['payum.api'] = function (ArrayObject $config) {
            $icepayApiClient = new IcepayApi();
            $icepayApiClient->setConfig($config['merchant_id'], $config['secret']);
            return $icepayApiClient;
        };
    }
}
