<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Object;

use CuyZ\Valinor\Mapper\Object\Argument;
use CuyZ\Valinor\Mapper\Object\Arguments;
use CuyZ\Valinor\Mapper\Object\Exception\InvalidArgumentIndex;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
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
        $typeB = FakeType::permissive();

        $arguments = new Arguments(
            Argument::required('someArgumentA', $typeA),
            Argument::optional('someArgumentB', $typeB, 'defaultValue')
        );

        self::assertSame("array{someArgumentA: $typeA, someArgumentB?: $typeB}", $arguments->signature());
    }
}
