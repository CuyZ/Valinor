<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Normalizer\CommonExamples;

use Attribute;
use CuyZ\Valinor\Normalizer\AsTransformer;
use CuyZ\Valinor\Normalizer\Format;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

use function count;
use function current;
use function is_array;

final class SinglePropertyObjectFlatteningFromAttributeTest extends IntegrationTestCase
{
    public function test_single_property_object_flattening_normalization_works_properly(): void
    {
        $result = $this->normalizerBuilder()
            ->normalizer(Format::array())
            ->normalize(new #[FlattenSingleProperty] class () {
                public function __construct(
                    public string $foo = 'foo',
                ) {}
            });

        self::assertSame('foo', $result);
    }
}

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
#[AsTransformer]
final class FlattenSingleProperty
{
    /**
     * @param callable(): mixed $next
     */
    public function normalize(object $object, callable $next): mixed
    {
        $result = $next();

        if (is_array($result) && count($result) === 1) {
            return current($result);
        }

        return $result;
    }
}
