<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class MainTest extends TestCase
{

    public function testOK(): void
    {
        $handler  = new \Mix\Guzzle\Handler\StreamHandler();
        $stack    = \GuzzleHttp\HandlerStack::create($handler);
        $client   = new \GuzzleHttp\Client([
            'handler' => $stack,
            'timeout' => 1,
        ]);
        $response = $client->get('https://www.baidu.com/');
        $this->assertNotEquals($response->getBody()->getSize(), 0);
    }

    public function testFailed(): void
    {
        $handler = new \Mix\Guzzle\Handler\StreamHandler();
        $stack   = \GuzzleHttp\HandlerStack::create($handler);
        $client  = new \GuzzleHttp\Client([
            'handler' => $stack,
            'timeout' => 1,
        ]);
        try {
            $response = $client->get('https://www.baidu12354636754dafdf.com/');
        } catch (\Throwable $exception) {
            $this->assertEquals(get_class($exception), \GuzzleHttp\Exception\RequestException::class);
        }
    }

    public function testHook(): void
    {
        $client = new \GuzzleHttp\Client([
            'timeout' => 1,
        ]);
        try {
            $response = $client->get('https://www.baidu12354636754dafdf.com/');
        } catch (\Throwable $exception) {
            $this->assertContains('guzzle/src/Handler/StreamHandler.php', $exception->getTraceAsString());
            $this->assertEquals(get_class($exception), \GuzzleHttp\Exception\RequestException::class);
        }
    }

}
