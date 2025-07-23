<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Parser\Factory\Specification;

use CuyZ\Valinor\Tests\Fake\Type\Parser\Lexer\Token\FakeToken;
use CuyZ\Valinor\Type\Parser\Factory\Specifications\ClassContextSpecification;
use PHPUnit\Framework\TestCase;
use stdClass;

final class ClassContextSpecificationTest extends TestCase
{
    public function test_self_returns_class_name(): void
    {
        $specification = new ClassContextSpecification(stdClass::class);

        $token = new FakeToken('self');
        $newToken = $specification->manipulateToken($token);

        self::assertSame(stdClass::class, $newToken->symbol());
    }

    public function test_static_returns_class_name(): void
    {
        $specification = new ClassContextSpecification(stdClass::class);

        $token = new FakeToken('static');
        $newToken = $specification->manipulateToken($token);

        self::assertSame(stdClass::class, $newToken->symbol());
    }
}
