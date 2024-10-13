<?php

declare(strict_types=1);

namespace SyliusIcepayPlugin\Processor;

use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;
use Sylius\Component\Resource\Exception\UpdateHandlingException;
use SyliusIcepayPlugin\Payum\IcepayApi;

class IcepayPaymentRefundProcessor
{
    public function __construct(IcepayApi $api)
    {
        $this->api = $api;
    }

    public function refund(SyliusPaymentInterface $payment): void
    {
        $paymentMethod = $payment->getMethod();
        $gatewayConfig = $paymentMethod->getGatewayConfig();

        if ($gatewayConfig->getFactoryName() !== 'icepay') {
            return;
        }

        $details = $payment->getDetails();

        $config = $gatewayConfig->getConfig();
        $this->api->setConfig($config['merchant_id'], $config['secret']);

        [ $isSuccessful, $refund ] = $this->api->refund( $details['key'], [
            'amount'      => [
                'value'    => $payment->getAmount(),
            ],
        ] );

        if ( ! $isSuccessful ) {
            throw new UpdateHandlingException('Could not refund order');
        }
    }
}
