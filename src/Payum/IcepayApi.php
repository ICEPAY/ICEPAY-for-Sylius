<?php

declare(strict_types=1);

namespace SyliusIcepayPlugin\Payum;

final class IcepayApi
{
    /** @var string */
    private $merchantId;
    private $secret;

    public function __construct(string $merchantId, $secret)
    {
        $this->merchantId = $merchantId;
        $this->secret = $secret;
    }

}
