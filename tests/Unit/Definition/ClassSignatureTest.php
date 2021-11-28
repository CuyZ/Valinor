<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Definition;

use CuyZ\Valinor\Definition\ClassSignature;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use PHPUnit\Framework\TestCase;
use stdClass;

final class ClassSignatureTest extends TestCase
{
    public function test_signature_data_can_be_retrieved(): void
    {
        $className = stdClass::class;
        $generics = [
            'TemplateA' => $genericA = new FakeType(),
            'TemplateB' => $genericB = new FakeType(),
        ];

        $signature = new ClassSignature($className, $generics);

        self::assertSame($className, $signature->className());
        self::assertSame("$className<$genericA, $genericB>", $signature->toString());
        self::assertSame($generics, $signature->generics());
    }

    public function test_left_backslash_is_trimmed_from_class_name(): void
    {
        $signature = new ClassSignature('\\' . stdClass::class);

        self::assertSame(stdClass::class, $signature->className());
    }
}
