<?php

namespace Mix\Tracing\Zipkin\Reporter;

use GuzzleHttp\Client as HttpClient;
use Zipkin\Reporters\Http\ClientFactory;
use RuntimeException;

/**
 * Class GuzzleFactory
 * @package Mix\Tracing\Zipkin\Reporter
 */
class GuzzleFactory implements ClientFactory
{

    /**
     * Create
     * @return GuzzleFactory
     */
    public static function create()
    {
        return new self();
    }

    /**
     * Build
     * @param array $options
     * @return callable|\Closure
     */
    public function build(array $options = [])
    {
        $handler = new \Mix\Guzzle\Handler\StreamHandler();
        $stack   = \GuzzleHttp\HandlerStack::create($handler);
        $client  = new HttpClient(
            [
                'handler' => $stack,
            ]
        );
        /**
         * @param string $payload
         * @return void
         * @throws RuntimeException
         */
        return function ($payload) use ($client, $options) {
            $url               = $options['endpoint_url'];
            $requiredHeaders   = [
                'Content-Type' => 'application/json',
            ];
            $additionalHeaders = (isset($options['headers']) ? $options['headers'] : []);
            $headers           = array_merge($additionalHeaders, $requiredHeaders);

            try {
                $response   = $client->post($url, [
                    'headers' => $headers,
                    'body'    => $payload,
                    'timeout' => $options['timeout'] ?? 0,
                ]);
                $statusCode = $response->getStatusCode();
                if ($statusCode !== 202) {
                    throw new RuntimeException(
                        sprintf('status code %d', $statusCode)
                    );
                }
            } catch (\Throwable $exception) {
                throw new RuntimeException(sprintf(
                    'Reporting of spans failed: %s, error code %s',
                    $exception->getMessage(),
                    $exception->getCode()
                ));
            }
        };
    }
}
