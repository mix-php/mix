<?php

namespace Mix\Http\Message\Factory;

use Mix\Http\Message\Exception\UnavailableMethodException;
use Mix\Http\Message\Stream\ContentStream;
use Mix\Http\Message\Stream\FileStream;
use Mix\Http\Message\Stream\IOStream;
use Mix\Http\Message\Stream\SwooleResourceStream;
use Mix\Http\Message\Stream\SwowResourceStream;
use Mix\Http\Message\Stream\WorkerManResourceStream;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Class StreamFactory
 * @package Mix\Http\Message\Factory
 */
class StreamFactory implements StreamFactoryInterface
{

    /**
     * Create a new stream from a string.
     *
     * The stream SHOULD be created with a temporary resource.
     *
     * @param string $content String content with which to populate the stream.
     *
     * @return StreamInterface
     */
    public function createStream(string $content = ''): StreamInterface
    {
        return new ContentStream($content);
    }

    /**
     * Create a stream from an existing file.
     *
     * The file MUST be opened using the given mode, which may be any mode
     * supported by the `fopen` function.
     *
     * The `$filename` MAY be any string supported by `fopen()`.
     *
     * @param string $filename Filename or stream URI to use as basis of stream.
     * @param string $mode Mode with which to open the underlying filename/stream.
     *
     * @return StreamInterface
     * @throws \RuntimeException If the file cannot be opened.
     * @throws \InvalidArgumentException If the mode is invalid.
     */
    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        return new FileStream($filename);
    }

    /**
     * Create a new stream from an existing resource.
     *
     * The stream MUST be readable and may be writable.
     *
     * @param resource $resource PHP resource to use as basis of stream.
     *
     * @return StreamInterface
     */
    public function createStreamFromResource($resource): StreamInterface
    {
        throw new UnavailableMethodException('Not implemented');
    }

    /**
     * Create a new stream from an existing resource.
     *
     * The stream MUST be readable and may be writable.
     *
     * @param \Swoole\Http\Request $request PHP resource to use as basis of stream.
     *
     * @return StreamInterface
     */
    public function createStreamFromFPM(): StreamInterface
    {
        return new IOStream('php://input');
    }

    /**
     * Create a new stream from an existing resource.
     *
     * The stream MUST be readable and may be writable.
     *
     * @param \Swoole\Http\Request $request PHP resource to use as basis of stream.
     *
     * @return StreamInterface
     */
    public function createStreamFromSwoole($request): StreamInterface
    {
        return new SwooleResourceStream($request);
    }

    /**
     * Create a new stream from an existing resource.
     *
     * The stream MUST be readable and may be writable.
     *
     * @param \Swow\Http\Server\Request $request PHP resource to use as basis of stream.
     *
     * @return StreamInterface
     */
    public function createStreamFromSwow($request): StreamInterface
    {
        return new SwowResourceStream($request);
    }

    /**
     * Create a new stream from an existing resource.
     *
     * The stream MUST be readable and may be writable.
     *
     * @param \Workerman\Protocols\Http\Request $request PHP resource to use as basis of stream.
     *
     * @return StreamInterface
     */
    public function createStreamFromWorkerMan($request): StreamInterface
    {
        return new WorkerManResourceStream($request);
    }

}
