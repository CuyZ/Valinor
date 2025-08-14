<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Normalizer\CommonExamples;

use CuyZ\Valinor\Normalizer\Format;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

use function current;
use function is_array;

final class SinglePropertyObjectFlatteningTest extends IntegrationTestCase
{
    public function test_single_property_object_flattening_normalization_works_properly(): void
    {
        $result = $this->normalizerBuilder()
            ->registerTransformer(function (object $object, callable $next) {
                $result = $next();

                if (is_array($result) && count($result) === 1) {
                    return current($result);
                }

                return $result;
            })
            ->normalizer(Format::array())
            ->normalize(new class () {
                public function __construct(
                    public string $foo = 'foo',
                ) {}
            });

        self::assertSame('foo', $result);
    }
}
