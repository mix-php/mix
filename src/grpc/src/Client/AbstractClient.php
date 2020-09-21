<?php

namespace Mix\Grpc\Client;

use Mix\Context\Context;
use Google\Protobuf\Internal\Message;
use Mix\Grpc\Exception\InvokeException;
use Mix\Grpc\Helper\GrpcHelper;

/**
 * Class AbstractClient
 * @package Mix\Grpc\Client
 */
abstract class AbstractClient
{

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * AbstractClient constructor.
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
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
     * @throws InvokeException
     */
    protected function _simpleRequest(string $path, Context $context, Message $request, Message $response, array $options): Message
    {
        $conn    = $this->connection;
        $headers = $options['headers'] ?? [];
        $headers += [
            'Content-Type' => 'application/grpc+proto',
        ];
        $body    = GrpcHelper::serialize($request);
        $timeout = $options['timeout'] ?? 5.0;
        $resp    = $conn->request('POST', $path, $headers, $body, $timeout);
        if ($resp->statusCode != 200) {
            throw new InvokeException(sprintf('Response Error: status-code: %d, grpc-status %s, grpc-message: %s', $resp->statusCode, $resp->headers['grpc-status'] ?? '', $resp->headers['grpc-message'] ?? ''));
        }
        GrpcHelper::deserialize($response, $resp->data);
        return $response;
    }

}
