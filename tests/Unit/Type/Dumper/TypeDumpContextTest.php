<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Dumper;

use CuyZ\Valinor\Tests\Unit\UnitTestCase;
use CuyZ\Valinor\Type\Dumper\TypeDumpContext;

use function str_repeat;

final class TypeDumpContextTest extends UnitTestCase
{
    public function test_max_length(): void
    {
        $context = new TypeDumpContext();
        $context = $context->write(str_repeat('a', 150));

        self::assertFalse($context->isTooLong());

        $context = $context->write('a');

        self::assertTrue($context->isTooLong());
    }
}
