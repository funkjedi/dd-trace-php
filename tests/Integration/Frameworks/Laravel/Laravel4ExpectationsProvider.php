<?php

namespace DDTrace\Tests\Integration\Frameworks\Laravel;

use DDTrace\Tests\Integration\Common\SpanAssertion;
use DDTrace\Tests\Integration\Frameworks\Util\ExpectationProvider;


class Laravel4ExpectationsProvider implements ExpectationProvider
{
    /**
     * @return SpanAssertion[]
     */
    public function provide()
    {
        return [
            'A simple GET request' => [
                SpanAssertion::exists('laravel.request'),
                SpanAssertion::build('laravel.action', 'laravel', 'web', 'simple'),
            ],
            'A simple GET request with a view' => [
                SpanAssertion::exists('laravel.request'),
                SpanAssertion::exists('laravel.action'),
                SpanAssertion::build('laravel.view.render', 'laravel', 'web', 'simple_view'),
            ],
        ];
    }
}
