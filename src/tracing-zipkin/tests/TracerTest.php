<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class TracerTest extends TestCase
{

    public function testRootSpan(): void
    {
        $tracing  = new \Mix\Tracing\Zipkin\Tracing();
        $tracer   = $tracing->trace('test-1', '192.168.1.1', 1234);
        $rootSpan = $tracer->startSpan('service-1', ['tags' => ['foo' => 'bar']]);

        usleep(100000);

        $scope1 = $tracer->startActiveSpan('scope-1', ['tags' => ['foo' => 'bar']]);
        usleep(100000);
        $scope1->close();

        $scope2 = $tracer->startActiveSpan('scope-2', ['tags' => ['foo' => 'bar']]);
        usleep(100000);

        $metadata = [];
        $tracer->inject($scope2->getSpan()->getContext(), \OpenTracing\Formats\TEXT_MAP, $metadata);
        echo "\n" . json_encode($metadata);

        $tracer2    = $tracing->trace('test-2', '192.168.1.1', 1234);
        $scope2Span = $tracer2->extract(\OpenTracing\Formats\TEXT_MAP, $metadata);
        $rootSpan2  = $tracer2->startSpan('service-2', [
            'tags'     => ['foo' => 'bar'],
            'child_of' => $scope2Span,
        ]);

        usleep(100000);

        $rootSpan2->finish();
        $tracer2->flush();

        $scope2->close();

        $rootSpan->finish();
        $tracer->flush();

        $this->assertNotEmpty($metadata);
    }

}
