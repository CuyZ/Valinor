<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Parser\Factory\Specification;

use CuyZ\Valinor\Tests\Fake\Type\Parser\Lexer\Token\FakeToken;
use CuyZ\Valinor\Tests\Integration\Mapping\Fixture;
use CuyZ\Valinor\Tests\Integration\Mapping\Fixture as SimpleObject;
use CuyZ\Valinor\Tests\Unit\Type\Parser\Factory\Specification as PathAlias;
use CuyZ\Valinor\Tests\Unit\UnitTestCase;
use CuyZ\Valinor\Type\Parser\Factory\Specifications\AliasSpecification;
use CuyZ\Valinor\Type\Parser\Lexer\Token\ObjectToken;
use CuyZ\Valinor\Type\Parser\Lexer\TokenStream;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use DateTimeInterface as SecondClassAlias;
use G\H\SomeClass;
use ObjectWithNameMatchingRootNamespace\ObjectWithNameMatchingRootNamespace;
use ReflectionClass;
use ReflectionFunction;
use stdClass;
use stdClass as FirstClassAlias;

use function spl_autoload_register;
use function spl_autoload_unregister;

final class AliasSpecificationTest extends UnitTestCase
{
    public function test_resolve_unsupported_type_in_class_returns_same_type(): void
    {
        $specification = new AliasSpecification(new ReflectionClass(stdClass::class));

        $token = new FakeToken('SomeType');
        $newToken = $specification->manipulateToken($token);

        self::assertSame($token, $newToken);
    }

    public function test_class_alias_are_checked_in_class(): void
    {
        $specification = new AliasSpecification(new ReflectionClass(ClassWithAlias::class));

        $token = new FakeToken('FirstClassAlias');
        $newToken = $specification->manipulateToken($token);

        self::assertSame(stdClass::class, $newToken->symbol());
    }

    public function test_class_alias_with_namespace_parts_is_checked_in_class(): void
    {
        $specification = new AliasSpecification(new ReflectionClass(ClassWithAlias::class));

        $token = new FakeToken('PathAlias\\AliasSpecificationTest');
        $newToken = $specification->manipulateToken($token);

        self::assertSame(AliasSpecificationTest::class, $newToken->symbol());
    }

    public function test_same_namespace_classes_are_checked_in_class(): void
    {
        $specification = new AliasSpecification(new ReflectionClass(ClassWithAlias::class));

        $token = new FakeToken('ClassWithAlias');
        $newToken = $specification->manipulateToken($token);

        self::assertSame(ClassWithAlias::class, $newToken->symbol());
    }

    public function test_object_with_same_name_as_root_namespace_are_checked_in_class(): void
    {
        $specification = new AliasSpecification(new ReflectionClass(ClassWithAlias::class));

        $token = new FakeToken(ObjectWithNameMatchingRootNamespace::class);
        $newToken = $specification->manipulateToken($token);

        self::assertSame(ObjectWithNameMatchingRootNamespace::class, $newToken->symbol());
    }

    public function test_object_with_partial_namespace_alias_matching_class_name_are_checked_in_class(): void
    {
        $specification = new AliasSpecification(new ReflectionClass(ClassWithAlias::class));

        $token = new FakeToken('SimpleObject\SimpleObject');
        $newToken = $specification->manipulateToken($token);

        self::assertSame(SimpleObject\SimpleObject::class, $newToken->symbol());
    }

    public function test_namespace_import_with_namespace_parts_is_checked_in_class(): void
    {
        $specification = new AliasSpecification(new ReflectionClass(ClassWithAlias::class));

        $token = new FakeToken('Fixture\\SimpleObject');
        $newToken = $specification->manipulateToken($token);

        self::assertSame(SimpleObject\SimpleObject::class, $newToken->symbol());
    }

    public function test_object_token_is_not_resolved_against_imports_in_class(): void
    {
        require_once 'imported-class-name-matching-root-namespace.php';

        $specification = new AliasSpecification(new ReflectionClass(SomeClass::class));

        $token = ObjectToken::from(SomeClass::class);
        $newToken = $specification->manipulateToken($token);

        self::assertSame($token, $newToken);
    }

    public function test_unknown_class_after_namespace_import_is_unresolvable_in_class(): void
    {
        $specification = new AliasSpecification(new ReflectionClass(ClassWithAlias::class));

        $token = $specification->manipulateToken(new FakeToken('Fixture\\UnknownClass'));
        $type = $token->traverse(new TokenStream());

        self::assertInstanceOf(UnresolvableType::class, $type);
        self::assertSame('Cannot parse unknown symbol `CuyZ\\Valinor\\Tests\\Integration\\Mapping\\Fixture\\UnknownClass`.', $type->message());
    }

    public function test_fully_qualified_name_is_returned_without_looking_for_classes_in_class(): void
    {
        $specification = new AliasSpecification(new ReflectionClass(ClassWithAlias::class));

        $requestedClasses = [];
        $autoloader = static function (string $className) use (&$requestedClasses): void {
            $requestedClasses[] = $className;
        };

        spl_autoload_register($autoloader);

        try {
            $token = new FakeToken('\\' . stdClass::class);
            $newToken = $specification->manipulateToken($token);
        } finally {
            spl_autoload_unregister($autoloader);
        }

        self::assertSame($token, $newToken);
        self::assertSame([], $requestedClasses);
    }

    public function test_resolve_unsupported_type_in_function_returns_same_type(): void
    {
        $function = fn () => 42;

        $specification = new AliasSpecification(new ReflectionFunction($function));

        $token = new FakeToken('SomeType');
        $newToken = $specification->manipulateToken($token);

        self::assertSame($token, $newToken);
    }

    public function test_class_alias_are_checked_in_function(): void
    {
        $function = fn () => 42;

        $specification = new AliasSpecification(new ReflectionFunction($function));

        $token = new FakeToken('FirstClassAlias');
        $newToken = $specification->manipulateToken($token);

        self::assertSame(stdClass::class, $newToken->symbol());
    }

    public function test_class_alias_with_namespace_parts_is_checked_in_function(): void
    {
        $function = fn () => 42;

        $specification = new AliasSpecification(new ReflectionFunction($function));

        $token = new FakeToken('PathAlias\\AliasSpecificationTest');
        $newToken = $specification->manipulateToken($token);

        self::assertSame(AliasSpecificationTest::class, $newToken->symbol());
    }

    public function test_same_namespace_classes_are_checked_in_function(): void
    {
        $function = fn () => 42;

        $specification = new AliasSpecification(new ReflectionFunction($function));

        $token = new FakeToken('ClassWithAlias');
        $newToken = $specification->manipulateToken($token);

        self::assertSame(ClassWithAlias::class, $newToken->symbol());
    }

    public function test_namespace_import_with_namespace_parts_is_checked_in_function(): void
    {
        $function = fn () => 42;

        $specification = new AliasSpecification(new ReflectionFunction($function));

        $token = new FakeToken('Fixture\\SimpleObject');
        $newToken = $specification->manipulateToken($token);

        self::assertSame(SimpleObject\SimpleObject::class, $newToken->symbol());
    }

    public function test_object_with_same_name_as_root_namespace_are_checked_in_function(): void
    {
        $function = fn () => 42;

        $specification = new AliasSpecification(new ReflectionFunction($function));

        $token = new FakeToken(ObjectWithNameMatchingRootNamespace::class);
        $newToken = $specification->manipulateToken($token);

        self::assertSame(ObjectWithNameMatchingRootNamespace::class, $newToken->symbol());
    }

    public function test_object_with_partial_namespace_alias_matching_class_name_are_checked_in_function(): void
    {
        $function = fn () => 42;

        $specification = new AliasSpecification(new ReflectionFunction($function));

        $token = new FakeToken('SimpleObject\SimpleObject');
        $newToken = $specification->manipulateToken($token);

        self::assertSame(SimpleObject\SimpleObject::class, $newToken->symbol());
    }

    public function test_object_with_partial_namespace_alias_matching_class_name_are_checked_in_function_bind_to(): void
    {
        $function = (fn () => 42)->bindTo(null, null);

        $specification = new AliasSpecification(new ReflectionFunction($function));

        $token = new FakeToken('SimpleObject\SimpleObject');
        $newToken = $specification->manipulateToken($token);

        self::assertSame(SimpleObject\SimpleObject::class, $newToken->symbol());
    }
}

final class ClassWithAlias
{
    public FirstClassAlias $propertyWithFirstAliasType;

    public SecondClassAlias $propertyWithSecondAliasType;

    public PathAlias\AliasSpecificationTest $propertyWithPathAliasType;

    public Fixture\SimpleObject $propertyWithNamespaceImportType;
}
