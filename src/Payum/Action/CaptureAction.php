<?php

declare(strict_types=1);

namespace SyliusIcepayPlugin\Payum\Action;

use Payum\Core\ApiAwareTrait;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Sylius\Bundle\PayumBundle\Provider\PaymentDescriptionProviderInterface;
use SyliusIcepayPlugin\Payum\IcepayApi;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;
use Payum\Core\Request\Capture;

final class CaptureAction implements ActionInterface, ApiAwareInterface, GenericTokenFactoryAwareInterface
{
    use GenericTokenFactoryAwareTrait;
    use ApiAwareTrait;

    public function __construct(private PaymentDescriptionProviderInterface $paymentDescription)
    {
        $this->apiClass = IcepayApi::class;
    }

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());
        if (isset($details['key'])) {
            return;
        }

        /** @var SyliusPaymentInterface $payment */
        $payment = $request->getModel();

        $body = $this->getBody($payment, $request->getToken());

        [ $isSuccessful, $checkout ] = $this->api->create($body);

        if (!$isSuccessful) {
            return;
        }

        $details['key'] = $checkout['key'];
        $details['status'] = 'started';
        $payment->setDetails((array) $details);

        throw new HttpRedirect($checkout['links']['checkout']);
    }

    private function getBody($payment, $token): array
    {
        return [
            'reference' => $payment->getOrder()->getNumber(),
            'description' => $this->paymentDescription->getPaymentDescription($payment),
            'amount' => [
                'value' => $payment->getAmount(),
                'currency' => $payment->getCurrencyCode(),
            ],
            'redirectUrl' => $token->getAfterUrl(),
            'webhookUrl' => $this->tokenFactory->createNotifyToken(
                $token->getGatewayName(),
                $token->getDetails()
            )->getTargetUrl(),
            'meta' => [
                'integration' => [
                    'type' => 'sylius',
                    'version' => '1.0.0',
                    'developer' => 'ICEPAY',
                ],
            ],
        ];
    }

    public function supports($request): bool
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof SyliusPaymentInterface
            ;
    }
}
