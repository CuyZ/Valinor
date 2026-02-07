<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Object;

use CuyZ\Valinor\Mapper\Object\NativeEnumObjectBuilder;
use CuyZ\Valinor\Tests\Fixture\Enum\PureEnum;
use CuyZ\Valinor\Tests\Unit\UnitTestCase;
use CuyZ\Valinor\Type\Types\EnumType;

final class NativeEnumObjectBuilderTest extends UnitTestCase
{
    public function test_signature_for_arguments_is_correct(): void
    {
        $builder = new NativeEnumObjectBuilder(EnumType::native(PureEnum::class));

        self::assertSame(PureEnum::class . '::$value', $builder->describeArguments()->at(0)->signature());
    }
}
