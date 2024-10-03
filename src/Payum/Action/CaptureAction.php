<?php

declare(strict_types=1);

namespace SyliusIcepayPlugin\Payum\Action;

use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Sylius\Bundle\PayumBundle\Provider\PaymentDescriptionProviderInterface;
use SyliusIcepayPlugin\Payum\IcepayApi;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;
use Payum\Core\Request\Capture;

final class CaptureAction implements ActionInterface, ApiAwareInterface, GenericTokenFactoryAwareInterface
{
    use GenericTokenFactoryAwareTrait;

    /** @var IcepayApi */
    private $api;

    public function __construct(private PaymentDescriptionProviderInterface $paymentDescription)
    {
    }

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        /** @var SyliusPaymentInterface $payment */
        $payment = $request->getModel();

        $notifyToken = $this->tokenFactory->createNotifyToken(
            $request->getToken()->getGatewayName(),
            $request->getToken()->getDetails()
        );

        $body['reference'] = (string)$payment->getOrder()->getId();
        $body['description'] = $this->paymentDescription->getPaymentDescription($payment);
        $body['amount'] = [
            'value' => $payment->getAmount(),
            'currency' => $payment->getCurrencyCode(),
        ];
        $body['redirectUrl'] = $request->getToken()->getTargetUrl();
        $body['webhookUrl'] = $notifyToken->getTargetUrl();

        [ $isSuccessful, $payment ] = $this->api->create($body);
        throw new HttpRedirect($payment['links']['checkout']);
    }

    public function supports($request): bool
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof SyliusPaymentInterface
            ;
    }

    public function setApi($api): void
    {
        if (!$api instanceof IcepayApi) {
            throw new UnsupportedApiException('Not supported. Expected an instance of ' . IcepayApi::class);
        }

        $this->api = $api;
    }
}
