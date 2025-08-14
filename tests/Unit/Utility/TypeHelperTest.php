<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Utility;

use CuyZ\Valinor\Mapper\Object\Argument;
use CuyZ\Valinor\Mapper\Object\Arguments;
use CuyZ\Valinor\Tests\Fake\Definition\FakeParameterDefinition;
use CuyZ\Valinor\Tests\Fake\Type\FakeObjectType;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Type\Types\ArrayType;
use CuyZ\Valinor\Type\Types\NativeBooleanType;
use CuyZ\Valinor\Type\Types\NativeClassType;
use CuyZ\Valinor\Type\Types\NativeFloatType;
use CuyZ\Valinor\Type\Types\NativeIntegerType;
use CuyZ\Valinor\Type\Types\NativeStringType;
use CuyZ\Valinor\Type\Types\UnionType;
use CuyZ\Valinor\Utility\TypeHelper;
use PHPUnit\Framework\TestCase;
use stdClass;

final class TypeHelperTest extends TestCase
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

    public function test_arguments_dump_is_correct(): void
    {
        $typeA = FakeType::permissive();
        $typeB = new FakeObjectType();
        $typeC = new UnionType(new FakeObjectType(), new FakeObjectType());
        $typeD = FakeType::permissive();

        $arguments = new Arguments(
            Argument::fromParameter(FakeParameterDefinition::new('someArgument', $typeA)),
            Argument::fromParameter(FakeParameterDefinition::new('someArgumentOfObject', $typeB)),
            Argument::fromParameter(FakeParameterDefinition::new('someArgumentWithUnionOfObject', $typeC)),
            Argument::fromParameter(FakeParameterDefinition::optional('someOptionalArgument', $typeD, 'defaultValue')),
        );

        self::assertSame(
            "`array{someArgument: {$typeA->toString()}, someArgumentOfObject: ?, someArgumentWithUnionOfObject: ?, someOptionalArgument?: {$typeD->toString()}}`",
            TypeHelper::dumpArguments($arguments)
        );
    }
}
