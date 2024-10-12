<?php

declare(strict_types=1);

namespace SyliusIcepayPlugin\Payum;

use GuzzleHttp\Client;

final class IcepayApi
{
    protected const Endpoint = 'https://checkout.icepay.com/api';

    private Client $client;

    public function __construct(protected ?string $merchantId, protected ?string $secret)
    {
        $this->client = new Client();
    }

    public function create( array $data ): array {
        return $this->do( '/payments', $data, 'POST' );
    }

    public function cancel( string $id ): array {
        return $this->do( '/payments/' . $id . '/cancel', method: 'POST' );
    }

    public function get( string $id ): array {
        return $this->do( '/payments/' . $id );
    }

    public function refund( string $id, array $data ): array {
        return $this->do( '/payments/' . $id . '/refund', $data, 'POST' );
    }

    protected function do( string $uri, ?array $body = null, string $method = 'GET' ): array {
        if ( ! $this->merchantId || ! $this->secret ) {
            return [ false, [ 'message' => 'Merchant ID or Secret Code not set' ] ];
        }

        $request_args = [
            'method'      => $method,
            'headers'     => [
                'Authorization' => $this->toBasicAuthorization(),
                'Content-Type'  => 'application/json',
            ],
            'httpversion' => '1.1',
            'sslverify'   => true,
            'timeout'     => 10,
        ];

        if ( $body ) {
            $request_args['body'] = json_encode( $body );
        }

        try {
            $response = $this->client->request($method, self::Endpoint . $uri, $request_args);
        } catch (\Exception $e) {
            return [ false, [ 'message' => $e->getMessage() ] ];
        }

        $responseBody          = json_decode(  (string)$response->getBody() , true );
        return [ true, $responseBody ];
    }

    protected function toBasicAuthorization(): string {
        return 'Basic ' . base64_encode( $this->merchantId . ':' . $this->secret );
    }

    public function validateSignature(string $content, string $signature): bool {
        return $signature === base64_encode(hash_hmac( 'sha256', $content, $this->secret, true ));
    }

}
