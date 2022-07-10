<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Utility;

use CuyZ\Valinor\Mapper\Object\Argument;
use CuyZ\Valinor\Mapper\Object\Arguments;
use CuyZ\Valinor\Tests\Fake\Type\FakeObjectType;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Type\Types\UnionType;
use CuyZ\Valinor\Utility\TypeHelper;
use PHPUnit\Framework\TestCase;

final class TypeHelperTest extends TestCase
{
    public function test_arguments_dump_is_correct(): void
    {
        $typeA = FakeType::permissive();
        $typeB = new FakeObjectType();
        $typeC = new UnionType(new FakeObjectType(), new FakeObjectType());
        $typeD = FakeType::permissive();

        $arguments = new Arguments(
            Argument::required('someArgument', $typeA),
            Argument::required('someArgumentOfObject', $typeB),
            Argument::required('someArgumentWithUnionOfObject', $typeC),
            Argument::optional('someOptionalArgument', $typeD, 'defaultValue')
        );

        self::assertSame(
            "`array{someArgument: {$typeA->toString()}, someArgumentOfObject: ?, someArgumentWithUnionOfObject: ?, someOptionalArgument?: {$typeD->toString()}}`",
            TypeHelper::dumpArguments($arguments)
        );
    }
}
