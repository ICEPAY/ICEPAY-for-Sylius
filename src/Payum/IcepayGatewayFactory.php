<?php

declare(strict_types=1);

namespace SyliusIcepayPlugin\Payum;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

final class IcepayGatewayFactory extends GatewayFactory
{
    protected function populateConfig(ArrayObject $config): void
    {
        $config->defaults([
            'payum.factory_name' => 'icepay',
            'payum.factory_title' => 'ICEPAY',
        ]);

        $config['payum.api'] = function (ArrayObject $config) {
            return new IcepayApi($config['api_key']);
        };
    }
}
