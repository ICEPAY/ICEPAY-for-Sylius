<?php

declare(strict_types=1);

namespace SyliusIcepayPlugin\Payum\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;

final class StatusAction implements ActionInterface
{
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var SyliusPaymentInterface $payment */
        $payment = $request->getFirstModel();

        $details = $payment->getDetails();

        if (!isset($details['status']) || !isset($details['key'])) {
            $request->markNew();
            return;
        }

        switch ($details['status']) {
            case 'started':
                $request->markNew();
                break;
            case 'cancelled':
                $request->markCanceled();
                break;
            case 'expired':
                $request->markFailed();
                break;
            case 'pending':
                $request->markPending();
                break;
            case 'completed':
                $request->markCaptured();
                break;
            default:
                $request->markUnknown();
                break;
        }
    }

    public function supports($request): bool
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getFirstModel() instanceof SyliusPaymentInterface
            ;
    }
}
