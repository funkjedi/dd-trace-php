<?php

namespace DDTrace\Tests\Integration\Integrations\Guzzle\V5;

use DDTrace\Configuration;
use DDTrace\Integrations\IntegrationsLoader;
use GuzzleHttp\Client;
use GuzzleHttp\Message\Request;
use GuzzleHttp\Ring\Client\MockHandler;
use DDTrace\Tests\Integration\Common\SpanAssertion;
use DDTrace\Tests\Integration\Common\IntegrationTestCase;
use OpenTracing\GlobalTracer;

final class GuzzleIntegrationTest extends IntegrationTestCase
{
    const URL = 'http://httpbin_integration';

    /** @var Client */
    private $client;

    public static function setUpBeforeClass()
    {
        IntegrationsLoader::load();
    }

    protected function setUp()
    {
        parent::setUp();
        $handler = new MockHandler(['status' => 200]);
        $this->client = new Client(['handler' => $handler]);
    }

    /**
     * @dataProvider providerHttpMethods
     */
    public function testAliasMethods($method)
    {
        $traces = $this->isolateTracer(function () use ($method) {
            $this->client->$method('http://example.com/?foo=secret');
        });
        $this->assertSpans($traces, [
            SpanAssertion::build('GuzzleHttp\Client.send', 'guzzle', 'http', 'send')
                ->withExactTags([
                    'http.method' => strtoupper($method),
                    'http.url' => 'http://example.com/',
                    'http.status_code' => '200',
                ]),
        ]);
    }

    public function providerHttpMethods()
    {
        return [
            ['get'],
            ['delete'],
            ['head'],
            ['options'],
            ['patch'],
            ['post'],
            ['put'],
        ];
    }

    public function testSend()
    {
        $traces = $this->isolateTracer(function () {
            $request = new Request('put', 'http://example.com');
            $this->client->send($request);
        });
        $this->assertSpans($traces, [
            SpanAssertion::build('GuzzleHttp\Client.send', 'guzzle', 'http', 'send')
                ->withExactTags([
                    'http.method' => 'PUT',
                    'http.url' => 'http://example.com',
                    'http.status_code' => '200',
                ]),
        ]);
    }

    public function testDistributedTracingIsPropagated()
    {
        $client = new Client();
        $found = [];

        $traces = $this->isolateTracer(function () use (&$found, $client) {
            $tracer = GlobalTracer::get();
            $span = $tracer->startActiveSpan('some_operation')->getSpan();

            $response = $client->get(self::URL . '/headers', [
                'headers' => [
                    'honored' => 'preserved_value',
                ],
            ]);

            $found = $response->json();
            $span->finish();
        });

        // trace is: some_operation
        $this->assertSame($traces[0][0]->getContext()->getSpanId(), $found['headers']['X-Datadog-Trace-Id']);
        // parent is: curl_exec, used under the hood
        $this->assertSame($traces[0][2]->getContext()->getSpanId(), $found['headers']['X-Datadog-Parent-Id']);
        // existing headers are honored
        $this->assertSame('preserved_value', $found['headers']['Honored']);
    }

    public function testDistributedTracingIsNotPropagatedIfDisabled()
    {
        $client = new Client();
        $found = [];
        Configuration::replace(\Mockery::mock('\DDTrace\Configuration', [
            'isDistributedTracingEnabled' => false
        ]));

        $this->isolateTracer(function () use (&$found, $client) {
            $tracer = GlobalTracer::get();
            $span = $tracer->startActiveSpan('some_operation')->getSpan();

            $response = $client->get(self::URL . '/headers');

            $found = $response->json();
            $span->finish();
        });

        $this->assertArrayNotHasKey('X-Datadog-Trace-Id', $found['headers']);
        $this->assertArrayNotHasKey('X-Datadog-Parent-Id', $found['headers']);
    }
}
