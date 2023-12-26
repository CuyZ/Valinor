<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Normalizer\CommonExamples;

use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Normalizer\Format;
use PHPUnit\Framework\TestCase;

use function dechex;

final class CustomObjectNormalizationTest extends TestCase
{
    public function test_custom_object_normalization_works_properly(): void
    {
        $result = (new MapperBuilder())
            ->registerTransformer(
                fn (HasCustomNormalization $object) => $object->normalize(),
            )
            ->normalizer(Format::array())
            ->normalize(new class () implements HasCustomNormalization {
                public function __construct(
                    public int $red = 64,
                    public int $green = 128,
                    public int $blue = 255,
                ) {}

                public function normalize(): string
                {
                    return '#' . dechex($this->red) . dechex($this->green) . dechex($this->blue);
                }
            });

        self::assertSame('#4080ff', $result);
    }
}

interface HasCustomNormalization
{
    public function normalize(): mixed;
}
