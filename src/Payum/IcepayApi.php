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

        $response = $this->wp_remote_request(
            self::Endpoint . $uri,
            $request_args
        );

//        if ( is_wp_error( $response ) ) {
//            return [ false, [ 'message' => $response->get_error_message() ] ];
//        }

//        $responseBody          = json_decode( wp_remote_retrieve_body( $response ), true );
        $responseBody          = json_decode(  (string)$response->getBody() , true );
//        $responseStatusCode    = wp_remote_retrieve_response_code( $response );
//        $responseStatusMessage = wp_remote_retrieve_response_message( $response );
//
//        if ( $responseStatusCode < 200 || $responseStatusCode >= 300 ) {
//            return [
//                false,
//                [
//                    'message' => $responseStatusCode . ': ' . $responseStatusMessage . '<br>'
//                        . 'message: ' . $responseBody['message'] . '<br>'
//                        . 'documentation: ' . $responseBody['documentation']['link']
//                ]
//            ];
//        }

        return [ true, $responseBody ];
    }

    protected function toBasicAuthorization(): string {
        return 'Basic ' . base64_encode( $this->merchantId . ':' . $this->secret );
    }

    protected function wp_remote_request( string $url, array $args ) {
        $response = $this->client->request('POST', $url, $args);
        return $response;
    }

    public function validateSignature(string $content, string $signature): bool {
        return $signature === base64_encode(hash_hmac( 'sha256', $content, $this->secret, true ));
    }

}
