<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Object;

use CuyZ\Valinor\Mapper\Object\DateTimeObjectBuilder;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class DateTimeObjectBuilderTest extends TestCase
{
    public function test_arguments_instance_stays_the_same(): void
    {
        $objectBuilder = new DateTimeObjectBuilder(DateTimeImmutable::class);

        $argumentsA = $objectBuilder->describeArguments();
        $argumentsB = $objectBuilder->describeArguments();

        self::assertSame($argumentsA, $argumentsB);
    }

    public function test_signature_is_correct(): void
    {
        $objectBuilder = new DateTimeObjectBuilder(DateTimeImmutable::class);

        self::assertSame('Internal date object builder', $objectBuilder->signature());
    }
}
