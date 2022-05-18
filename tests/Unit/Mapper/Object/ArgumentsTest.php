<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Object;

use CuyZ\Valinor\Mapper\Object\Argument;
use CuyZ\Valinor\Mapper\Object\Arguments;
use CuyZ\Valinor\Mapper\Object\Exception\InvalidArgumentIndex;
use CuyZ\Valinor\Tests\Fake\Type\FakeObjectType;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Type\Types\UnionType;
use PHPUnit\Framework\TestCase;

final class ArgumentsTest extends TestCase
{
    public function test_get_argument_at_invalid_index_throws_exception(): void
    {
        $this->expectException(InvalidArgumentIndex::class);
        $this->expectExceptionCode(1648672136);
        $this->expectExceptionMessage("Index 1 is out of range, it should be between 0 and 0.");

        (new Arguments(
            Argument::required('someArgument', FakeType::permissive())
        ))->at(1);
    }

    public function test_signature_is_correct(): void
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
            "`array{someArgument: $typeA, someArgumentOfObject: ?, someArgumentWithUnionOfObject: ?, someOptionalArgument?: $typeD}`",
            $arguments->signature()
        );
    }
}
