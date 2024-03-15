<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Functional\Type\Parser\Lexer;

use CuyZ\Valinor\Tests\Fixture\Object\AbstractObject;
use CuyZ\Valinor\Type\CompositeTraversableType;
use CuyZ\Valinor\Type\Parser\Exception\Generic\AssignedGenericNotFound;
use CuyZ\Valinor\Type\Parser\Exception\Generic\CannotAssignGeneric;
use CuyZ\Valinor\Type\Parser\Exception\Generic\GenericClosingBracketMissing;
use CuyZ\Valinor\Type\Parser\Exception\Generic\GenericCommaMissing;
use CuyZ\Valinor\Type\Parser\Exception\Generic\InvalidAssignedGeneric;
use CuyZ\Valinor\Type\Parser\Exception\Generic\MissingGenerics;
use CuyZ\Valinor\Type\Parser\Exception\Template\DuplicatedTemplateName;
use CuyZ\Valinor\Type\Parser\Exception\Template\InvalidClassTemplate;
use CuyZ\Valinor\Type\Parser\Factory\LexingTypeParserFactory;
use CuyZ\Valinor\Type\Parser\Factory\Specifications\GenericCheckerSpecification;
use CuyZ\Valinor\Type\Parser\TypeParser;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\ClassType;
use CuyZ\Valinor\Type\Types\InterfaceType;
use DateTimeInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

final class GenericLexerTest extends TestCase
{
    private TypeParser $parser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->parser = (new LexingTypeParserFactory())->get(
            new GenericCheckerSpecification(),
        );
    }

    /**
     * @param class-string<Type> $type
     */
    #[DataProvider('parse_valid_types_returns_valid_result_data_provider')]
    public function test_parse_valid_types_returns_valid_result(string $raw, string $transformed, string $type): void
    {
        $result = $this->parser->parse($raw);

        self::assertSame($transformed, $result->toString());
        self::assertInstanceOf($type, $result);
    }

    public static function parse_valid_types_returns_valid_result_data_provider(): iterable
    {
        yield 'Class name with no template' => [
            'raw' => stdClass::class,
            'transformed' => stdClass::class,
            'type' => ClassType::class,
        ];

        yield 'Abstract class name with no template' => [
            'raw' => AbstractObject::class,
            'transformed' => AbstractObject::class,
            'type' => ClassType::class,
        ];

        yield 'Interface name with no template' => [
            'raw' => DateTimeInterface::class,
            'transformed' => DateTimeInterface::class,
            'type' => InterfaceType::class,
        ];

        yield 'Class name with generic with one template' => [
            'raw' => SomeClassWithOneTemplate::class . '<int>',
            'transformed' => SomeClassWithOneTemplate::class . '<int>',
            'type' => ClassType::class,
        ];

        yield 'Class name with generic with three templates' => [
            'raw' => SomeClassWithThreeTemplates::class . '<int, string, float>',
            'transformed' => SomeClassWithThreeTemplates::class . '<int, string, float>',
            'type' => ClassType::class,
        ];

        yield 'Class name with generic with first template without type and second template with type' => [
            'raw' => SomeClassWithFirstTemplateWithoutTypeAndSecondTemplateWithType::class . '<int, stdClass>',
            'transformed' => SomeClassWithFirstTemplateWithoutTypeAndSecondTemplateWithType::class . '<int, stdClass>',
            'type' => ClassType::class,
        ];

        yield 'Class name with generic with template of array-key with string' => [
            'raw' => SomeClassWithTemplateOfArrayKey::class . '<string>',
            'transformed' => SomeClassWithTemplateOfArrayKey::class . '<string>',
            'type' => ClassType::class,
        ];

        yield 'Class name with generic with template of array-key with integer' => [
            'raw' => SomeClassWithTemplateOfArrayKey::class . '<int>',
            'transformed' => SomeClassWithTemplateOfArrayKey::class . '<int>',
            'type' => ClassType::class,
        ];

        yield 'Simple array of class name with no template' => [
            'raw' => stdClass::class . '[]',
            'transformed' => stdClass::class . '[]',
            'type' => CompositeTraversableType::class,
        ];
    }

    public function test_missing_generics_throws_exception(): void
    {
        $genericClassName = SomeClassWithThreeTemplates::class;

        $this->expectException(MissingGenerics::class);
        $this->expectExceptionCode(1618054357);
        $this->expectExceptionMessage("There are 2 missing generics for `$genericClassName<int, ?, ?>`.");

        $this->parser->parse("$genericClassName<int,");
    }

    public function test_missing_generic_closing_bracket_throws_exception(): void
    {
        $genericClassName = stdClass::class;

        $this->expectException(GenericClosingBracketMissing::class);
        $this->expectExceptionCode(1604333677);
        $this->expectExceptionMessage("The closing bracket is missing for the generic `$genericClassName<string>`.");

        $this->parser->parse("$genericClassName<string");
    }

    public function test_missing_comma_in_generics_throws_exception(): void
    {
        $className = SomeClassWithThreeTemplates::class;

        $this->expectException(GenericCommaMissing::class);
        $this->expectExceptionCode(1615829484);
        $this->expectExceptionMessage("A comma is missing for the generic `$className<int, string, ?>`.");

        $this->parser->parse("$className<int, string bool>");
    }

    public function test_assigned_generic_not_found_throws_exception(): void
    {
        $className = SomeClassWithThreeTemplates::class;

        $this->expectException(AssignedGenericNotFound::class);
        $this->expectExceptionCode(1604656730);
        $this->expectExceptionMessage("No generic was assigned to the template(s) `TemplateB`, `TemplateC` for the class `$className`.");

        $this->parser->parse("$className<int>");
    }

    public function test_generic_with_no_template_throws_exception(): void
    {
        $className = SomeClassWithOneTemplate::class;

        $this->expectException(CannotAssignGeneric::class);
        $this->expectExceptionCode(1604660485);
        $this->expectExceptionMessage("Could not find a template to assign the generic(s) `string`, `bool` for the class `$className`.");

        $this->parser->parse("$className<int, string, bool>");
    }

    public function test_generic_with_non_matching_type_for_template_throws_exception(): void
    {
        $object =
            /**
             * @template Template of string
             */
            new class () {};

        $className = $object::class;

        $this->expectException(InvalidAssignedGeneric::class);
        $this->expectExceptionCode(1604613633);
        $this->expectExceptionMessage("The generic `bool` is not a subtype of `string` for the template `Template` of the class `$className`.");

        $this->parser->parse("$className<bool>");
    }

    public function test_composite_type_containing_generic_with_non_matching_type_for_template_throws_exception(): void
    {
        $object =
            /**
             * @template Template of string
             */
            new class () {};

        $className = $object::class;

        $this->expectException(InvalidAssignedGeneric::class);
        $this->expectExceptionCode(1604613633);
        $this->expectExceptionMessage("The generic `bool` is not a subtype of `string` for the template `Template` of the class `$className`.");

        $this->parser->parse("list<$className<bool>>");
    }

    public function test_generic_with_non_matching_array_key_type_for_template_throws_exception(): void
    {
        $object =
            /**
             * @template Template of array-key
             */
            new class () {};

        $className = $object::class;

        $this->expectException(InvalidAssignedGeneric::class);
        $this->expectExceptionCode(1604613633);
        $this->expectExceptionMessage("The generic `bool` is not a subtype of `int|string` for the template `Template` of the class `$className`.");

        $this->parser->parse("$className<bool>");
    }

    public function test_duplicated_template_name_throws_exception(): void
    {
        $object =
            /**
             * @template TemplateA
             * @template TemplateA
             */
            new class () {};

        $className = $object::class;

        $this->expectException(DuplicatedTemplateName::class);
        $this->expectExceptionCode(1604612898);
        $this->expectExceptionMessage("The template `TemplateA` in class `$className` was defined at least twice.");

        $this->parser->parse("$className<int, string>");
    }

    public function test_invalid_template_type_throws_exception(): void
    {
        $object =
            /**
             * @template Template of InvalidType
             */
            new class () {};

        $className = $object::class;

        $this->expectException(InvalidClassTemplate::class);
        $this->expectExceptionCode(1630092678);
        $this->expectExceptionMessage("Invalid template `Template` for class `$className`: Cannot parse unknown symbol `InvalidType`.");

        $this->parser->parse("$className<int>");
    }
}

/**
 * @template TemplateA
 */
final class SomeClassWithOneTemplate {}

/**
 * @template TemplateA
 * @template TemplateB
 * @template TemplateC
 */
final class SomeClassWithThreeTemplates {}

/**
 * @template TemplateA of array-key
 */
final class SomeClassWithTemplateOfArrayKey {}

/**
 * @template TemplateA
 * @template TemplateB of object
 */
final class SomeClassWithFirstTemplateWithoutTypeAndSecondTemplateWithType {}
