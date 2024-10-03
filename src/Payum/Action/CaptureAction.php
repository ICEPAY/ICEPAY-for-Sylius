<?php

declare(strict_types=1);

namespace SyliusIcepayPlugin\Payum\Action;

use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use SyliusIcepayPlugin\Payum\IcepayApi;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
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

    /** @var Client */
    private $client;
    /** @var IcepayApi */
    private $api;

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
        $webhookUrl = $notifyToken->getTargetUrl();
        $redirectUrl = $request->getToken()->getTargetUrl();

//        $details['reference'] = $payment->getOrder()->getId();
//        $details['description'] = $payment->getDescription();
//        $details['amount'] = [
//            'value' => $payment->getAmount(),
//            'currency' => $payment->getCurrencyCode(),

        throw new HttpRedirect("https://icepay.com");
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
