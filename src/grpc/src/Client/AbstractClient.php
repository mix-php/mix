<?php

namespace Mix\Grpc\Client;

use Mix\Grpc\Context;
use Google\Protobuf\Internal\Message;
use Mix\Grpc\Exception\RuntimeException;
use Mix\Grpc\Helper\GrpcHelper;

/**
 * Class AbstractClient
 * @package Mix\Grpc\Client
 */
abstract class AbstractClient
{

    /**
     * @var \Mix\Grpc\Client
     */
    protected $connection;

    /**
     * AbstractClient constructor.
     * @param \Mix\Grpc\Client $connection
     */
    public function __construct(\Mix\Grpc\Client $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Request
     * @param string $path
     * @param Context $context
     * @param Message $request
     * @param Message $response
     * @param array $options
     * @return Message
     * @throws RuntimeException
     */
    protected function _simpleRequest(string $path, Context $context, Message $request, Message $response): Message
    {
        $conn = $this->connection;
        $headers = $context->getHeaders();
        $headers += [
            'Content-Type' => 'application/grpc+proto',
        ];
        $body = GrpcHelper::serialize($request);
        $timeout = $context->getTimeout();
        $resp = $conn->request('POST', $path, $headers, $body, $timeout);
        if ($resp->statusCode != 200) {
            throw new RuntimeException(sprintf('Response Error: status-code: %d, grpc-status %s, grpc-message: %s', $resp->statusCode, $resp->headers['grpc-status'] ?? '', $resp->headers['grpc-message'] ?? ''));
        }
        GrpcHelper::deserialize($response, $resp->data ?? '');
        return $response;
    }

}
