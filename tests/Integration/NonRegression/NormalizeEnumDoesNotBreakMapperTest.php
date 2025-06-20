<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\NonRegression;

use CuyZ\Valinor\Normalizer\Format;
use CuyZ\Valinor\Tests\Fixture\Enum\BackedStringEnum;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

final class NormalizeEnumDoesNotBreakMapperTest extends IntegrationTestCase
{
    /**
     * The normalizer will at some point fetch the class definition of the enum,
     * we need to ensure an `EnumType` is used and not a `NativeClassType`,
     * otherwise the cache would be corrupted for further usage.
     *
     * @see https://github.com/CuyZ/Valinor/issues/562
     */
    public function test_normalizing_enum_and_then_map_value_on_same_enum_class_does_not_break(): void
    {
        $this->normalizerBuilder()
            ->normalizer(Format::array())
            ->normalize(BackedStringEnum::FOO);

        $result = $this->mapperBuilder()
            ->mapper()
            ->map(BackedStringEnum::class, 'foo');

        self::assertSame(BackedStringEnum::FOO, $result);
    }
}
