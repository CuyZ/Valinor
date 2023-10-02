<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Functional\Type\Parser\Lexer;

use CuyZ\Valinor\Tests\Fixture\Object\AbstractObject;
use CuyZ\Valinor\Type\CompositeTraversableType;
use CuyZ\Valinor\Type\Parser\Exception\Generic\AssignedGenericNotFound;
use CuyZ\Valinor\Type\Parser\Exception\Generic\CannotAssignGeneric;
use CuyZ\Valinor\Type\Parser\Exception\Generic\ExtendTagTypeError;
use CuyZ\Valinor\Type\Parser\Exception\Generic\GenericClosingBracketMissing;
use CuyZ\Valinor\Type\Parser\Exception\Generic\GenericCommaMissing;
use CuyZ\Valinor\Type\Parser\Exception\Generic\InvalidAssignedGeneric;
use CuyZ\Valinor\Type\Parser\Exception\Generic\InvalidExtendTagClassName;
use CuyZ\Valinor\Type\Parser\Exception\Generic\InvalidExtendTagType;
use CuyZ\Valinor\Type\Parser\Exception\Generic\MissingGenerics;
use CuyZ\Valinor\Type\Parser\Exception\Generic\SeveralExtendTagsFound;
use CuyZ\Valinor\Type\Parser\Exception\Template\DuplicatedTemplateName;
use CuyZ\Valinor\Type\Parser\Exception\Template\InvalidClassTemplate;
use CuyZ\Valinor\Type\Parser\Factory\LexingTypeParserFactory;
use CuyZ\Valinor\Type\Parser\TypeParser;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\ClassType;
use CuyZ\Valinor\Type\Types\InterfaceType;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use stdClass;

final class GenericLexerTest extends TestCase
{
    private TypeParser $parser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->parser = (new LexingTypeParserFactory())->get();
    }

    /**
     * @dataProvider parse_valid_types_returns_valid_result_data_provider
     *
     * @param class-string<Type> $type
     */
    public function test_parse_valid_types_returns_valid_result(string $raw, string $transformed, string $type): void
    {
        $result = $this->parser->parse($raw);

        self::assertSame($transformed, $result->toString());
        self::assertInstanceOf($type, $result);
    }

    public function parse_valid_types_returns_valid_result_data_provider(): array
    {
        return [
            'Class name with no template' => [
                'raw' => stdClass::class,
                'transformed' => stdClass::class,
                'type' => ClassType::class,
            ],
            'Abstract class name with no template' => [
                'raw' => AbstractObject::class,
                'transformed' => AbstractObject::class,
                'type' => ClassType::class,
            ],
            'Interface name with no template' => [
                'raw' => DateTimeInterface::class,
                'transformed' => DateTimeInterface::class,
                'type' => InterfaceType::class,
            ],
            'Class name with generic with one template' => [
                'raw' => $this->classWithOneTemplate() . '<int>',
                'transformed' => $this->classWithOneTemplate() . '<int>',
                'type' => ClassType::class,
            ],
            'Class name with generic with three templates' => [
                'raw' => $this->classWithThreeTemplates() . '<int, string, float>',
                'transformed' => $this->classWithThreeTemplates() . '<int, string, float>',
                'type' => ClassType::class,
            ],
            'Class name with generic with template of array-key with string' => [
                'raw' => $this->classWithTemplateOfArrayKey() . '<string>',
                'transformed' => $this->classWithTemplateOfArrayKey() . '<string>',
                'type' => ClassType::class,
            ],
            'Class name with generic with template of array-key with integer' => [
                'raw' => $this->classWithTemplateOfArrayKey() . '<int>',
                'transformed' => $this->classWithTemplateOfArrayKey() . '<int>',
                'type' => ClassType::class,
            ],
            'Simple array of class name with no template' => [
                'raw' => stdClass::class . '[]',
                'transformed' => stdClass::class . '[]',
                'type' => CompositeTraversableType::class,
            ],
        ];
    }

    public function test_missing_generics_throws_exception(): void
    {
        $genericClassName = $this->classWithThreeTemplates();

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
        $className = $this->classWithThreeTemplates();

        $this->expectException(GenericCommaMissing::class);
        $this->expectExceptionCode(1615829484);
        $this->expectExceptionMessage("A comma is missing for the generic `$className<int, string, ?>`.");

        $this->parser->parse("$className<int, string bool>");
    }

    public function test_assigned_generic_not_found_throws_exception(): void
    {
        $className = $this->classWithThreeTemplates();

        $this->expectException(AssignedGenericNotFound::class);
        $this->expectExceptionCode(1604656730);
        $this->expectExceptionMessage("No generic was assigned to the template(s) `TemplateB`, `TemplateC` for the class `$className`.");

        $this->parser->parse("$className<int>");
    }

    public function test_generic_with_no_template_throws_exception(): void
    {
        $className = $this->classWithOneTemplate();

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

        $this->parser->parse("$className<int, string>");
    }

    public function test_several_extends_tags_throws_exception(): void
    {
        $className = SomeChildClassWithSeveralExtendTags::class;

        $this->expectException(SeveralExtendTagsFound::class);
        $this->expectExceptionCode(1670195494);
        $this->expectExceptionMessage("Only one `@extends` tag should be set for the class `$className`.");

        $this->parser->parse($className);
    }

    public function test_wrong_extends_tag_throws_exception(): void
    {
        $childClassName = SomeChildClassWithInvalidExtendTag::class;
        $parentClassName = SomeParentAbstractClass::class;

        $this->expectException(InvalidExtendTagType::class);
        $this->expectExceptionCode(1670181134);
        $this->expectExceptionMessage("The `@extends` tag of the class `$childClassName` has invalid type `string`, it should be `$parentClassName`.");

        $this->parser->parse($childClassName);
    }

    public function test_wrong_extends_tag_class_name_throws_exception(): void
    {
        $childClassName = SomeChildClassWithInvalidExtendTagClassName::class;
        $parentClassName = SomeParentAbstractClass::class;

        $this->expectException(InvalidExtendTagClassName::class);
        $this->expectExceptionCode(1670183564);
        $this->expectExceptionMessage("The `@extends` tag of the class `$childClassName` has invalid class `stdClass`, it should be `$parentClassName`.");

        $this->parser->parse($childClassName);
    }

    public function test_extend_tag_type_error_throws_exception(): void
    {
        $className = SomeChildClassWithMissingGenericsInExtendTag::class;

        $this->expectException(ExtendTagTypeError::class);
        $this->expectExceptionCode(1670193574);
        $this->expectExceptionMessage("The `@extends` tag of the class `$className` is not valid: Cannot parse unknown symbol `InvalidType`.");

        $this->parser->parse($className);
    }

    private function classWithOneTemplate(): string
    {
        $object =
            /**
             * @template TemplateA
             */
            new class () {};

        return $object::class;
    }

    private function classWithThreeTemplates(): string
    {
        $object =
            /**
             * @template TemplateA
             * @template TemplateB
             * @template TemplateC
             */
            new class () {};

        return $object::class;
    }

    private function classWithTemplateOfArrayKey(): string
    {
        $object =
            /**
             * @template TemplateA of array-key
             */
            new class () {};

        return $object::class;
    }
}

/**
 * @template T
 */
abstract class SomeParentAbstractClass {}

/**
 * @template T
 */
abstract class SomeOtherParentAbstractClass {}

/**
 * @phpstan-ignore-next-line
 * @extends SomeParentAbstractClass<string>
 * @extends SomeOtherParentAbstractClass<string>
 */
final class SomeChildClassWithSeveralExtendTags extends SomeParentAbstractClass {}

/**
 * @phpstan-ignore-next-line
 * @extends string
 */
final class SomeChildClassWithInvalidExtendTag extends SomeParentAbstractClass {}

/**
 * @phpstan-ignore-next-line
 * @extends stdClass
 */
final class SomeChildClassWithInvalidExtendTagClassName extends SomeParentAbstractClass {}

/**
 * @phpstan-ignore-next-line
 * @extends SomeParentAbstractClass<InvalidType>
 */
final class SomeChildClassWithMissingGenericsInExtendTag extends SomeParentAbstractClass {}
