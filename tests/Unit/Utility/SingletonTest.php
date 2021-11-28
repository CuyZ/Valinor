<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Utility;

use CuyZ\Valinor\Utility\Singleton;
use PHPUnit\Framework\TestCase;

final class SingletonTest extends TestCase
{
    public function test_annotation_reader_is_singleton(): void
    {
        $instanceA = Singleton::annotationReader();
        $instanceB = Singleton::annotationReader();

        self::assertSame($instanceA, $instanceB);
    }

    public function test_php_parser_is_singleton(): void
    {
        $instanceA = Singleton::phpParser();
        $instanceB = Singleton::phpParser();

        self::assertSame($instanceA, $instanceB);
    }
}
