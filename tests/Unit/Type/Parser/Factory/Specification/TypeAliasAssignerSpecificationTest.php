<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Parser\Factory\Specification;

use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Tests\Fake\Type\Parser\Lexer\Token\FakeToken;
use CuyZ\Valinor\Type\Parser\Factory\Specifications\TypeAliasAssignerSpecification;
use CuyZ\Valinor\Type\Parser\Lexer\TokenStream;
use PHPUnit\Framework\TestCase;

final class TypeAliasAssignerSpecificationTest extends TestCase
{
    public function test_non_generic_symbol_is_handled_by_delegate(): void
    {
        $specification = new TypeAliasAssignerSpecification(['TemplateA' => new FakeType()]);

        $token = new FakeToken('foo');
        $newToken = $specification->manipulateToken($token);

        self::assertSame($token, $newToken);
    }

    public function test_symbol_is_generic_is_returned(): void
    {
        $type = new FakeType();
        $specification = new TypeAliasAssignerSpecification(['Template' => $type]);

        $token = new FakeToken('Template');
        $newToken = $specification->manipulateToken($token);

        self::assertSame($type, $newToken->traverse(new TokenStream()));
    }
}
