<?php

namespace DDTrace;

/**
 * Propagator implementations should be able to inject and extract
 * SpanContexts into an implementation specific carrier.
 */
interface Propagator
{
    const DEFAULT_BAGGAGE_HEADER_PREFIX = 'ot-baggage-';
    const DEFAULT_TRACE_ID_HEADER = 'x-datadog-trace-id';
    const DEFAULT_PARENT_ID_HEADER = 'x-datadog-parent-id';

    /**
     * Inject takes the SpanContext and injects it into the carrier using
     * an implementation specific method.
     *
     * @param SpanContext $spanContext
     * @param array|\ArrayAccess $carrier
     * @return void
     */
    public function inject(SpanContext $spanContext, &$carrier);

    /**
     * Extract returns the SpanContext from the given carrier using an
     * implementation specific method.
     *
     * @param array|\ArrayAccess $carrier
     * @return SpanContext
     */
    public function extract($carrier);
}
