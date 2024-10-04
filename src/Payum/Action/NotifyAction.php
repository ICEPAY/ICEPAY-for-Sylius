<?php

declare(strict_types=1);

namespace SyliusIcepayPlugin\Payum\Action;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\Notify;
use SyliusIcepayPlugin\Payum\IcepayApi;
use Symfony\Component\HttpFoundation\Response;

class NotifyAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface
{
    use ApiAwareTrait;
    use GatewayAwareTrait;

    public function __construct()
    {
        $this->apiClass = IcepayApi::class;
    }

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        // Receive webhook from ICEPAY
        $this->gateway->execute($httpRequest = new GetHttpRequest());
        $content = $httpRequest->content;

        if (!$this->api->validateSignature($content, $httpRequest->headers['icepay-signature'][0])) {
            throw new \Exception('Invalid signature');
        }

        $payload = json_decode($content);

        $details = ArrayObject::ensureArrayObject($request->getModel());
        $details['status'] = $payload->status;

        throw new HttpResponse('', Response::HTTP_OK);
    }

    public function supports($request): bool
    {
        return
            $request instanceof Notify &&
            $request->getModel() instanceof ArrayAccess;
    }
}
