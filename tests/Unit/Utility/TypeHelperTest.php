<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Utility;

use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Tests\Unit\UnitTestCase;
use CuyZ\Valinor\Type\Types\ArrayType;
use CuyZ\Valinor\Type\Types\NativeBooleanType;
use CuyZ\Valinor\Type\Types\NativeClassType;
use CuyZ\Valinor\Type\Types\NativeFloatType;
use CuyZ\Valinor\Type\Types\NativeIntegerType;
use CuyZ\Valinor\Type\Types\NativeStringType;
use CuyZ\Valinor\Utility\TypeHelper;
use stdClass;

final class TypeHelperTest extends UnitTestCase
{
    public function test_types_have_correct_priorities(): void
    {
        self::assertSame(3, TypeHelper::typePriority(new NativeClassType(stdClass::class)));
        self::assertSame(2, TypeHelper::typePriority(ArrayType::native()));
        self::assertSame(1, TypeHelper::typePriority(NativeStringType::get()));
        self::assertSame(0, TypeHelper::typePriority(new FakeType()));
    }

    public function test_scalar_types_have_correct_priorities(): void
    {
        self::assertSame(4, TypeHelper::scalarTypePriority(NativeIntegerType::get()));
        self::assertSame(3, TypeHelper::scalarTypePriority(NativeFloatType::get()));
        self::assertSame(2, TypeHelper::scalarTypePriority(NativeStringType::get()));
        self::assertSame(1, TypeHelper::scalarTypePriority(NativeBooleanType::get()));
    }
}
