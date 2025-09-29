<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Dumper;

use CuyZ\Valinor\Type\Dumper\TypeDumpContext;
use PHPUnit\Framework\TestCase;

final class TypeDumpContextTest extends TestCase
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
