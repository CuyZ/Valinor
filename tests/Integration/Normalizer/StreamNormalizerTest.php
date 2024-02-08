<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Normalizer;

use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Normalizer\Format;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

use function fopen;
use function rewind;

final class StreamNormalizerTest extends IntegrationTestCase
{
    public function test_json_normalizer_can_normalize_into_stream(): void
    {
        /** @var resource $resource */
        $resource = fopen('php://memory', 'r+');

        (new MapperBuilder())
            ->normalizer(Format::json())
            ->streamTo($resource)
            ->normalize(['foo' => 'bar']);

        rewind($resource);

        self::assertSame('{"foo":"bar"}', stream_get_contents($resource));
    }
}
