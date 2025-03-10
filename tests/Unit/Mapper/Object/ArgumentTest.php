<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Object;

use CuyZ\Valinor\Mapper\Object\Argument;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use PHPUnit\Framework\TestCase;

final class ArgumentTest extends TestCase
{
    public function test_argument_with_type_returns_clone(): void
    {
        $argument = new Argument('name', new FakeType());
        $argumentWithType = $argument->withType(new FakeType());

        self::assertNotSame($argument, $argumentWithType);
    }
}
