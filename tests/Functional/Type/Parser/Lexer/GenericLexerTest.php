<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Functional\Type\Parser\Lexer;

use CuyZ\Valinor\Tests\Fake\Type\Parser\Factory\FakeTypeParserFactory;
use CuyZ\Valinor\Tests\Fixture\Object\AbstractObject;
use CuyZ\Valinor\Type\CompositeTraversableType;
use CuyZ\Valinor\Type\Parser\Exception\Generic\AssignedGenericNotFound;
use CuyZ\Valinor\Type\Parser\Exception\Generic\CannotAssignGeneric;
use CuyZ\Valinor\Type\Parser\Exception\Generic\GenericClosingBracketMissing;
use CuyZ\Valinor\Type\Parser\Exception\Generic\GenericCommaMissing;
use CuyZ\Valinor\Type\Parser\Exception\Generic\InvalidAssignedGeneric;
use CuyZ\Valinor\Type\Parser\Exception\Generic\MissingGenerics;
use CuyZ\Valinor\Type\Parser\Exception\Template\InvalidClassTemplate;
use CuyZ\Valinor\Type\Parser\Lexer\ClassGenericLexer;
use CuyZ\Valinor\Type\Parser\Lexer\NativeLexer;
use CuyZ\Valinor\Type\Parser\LexingParser;
use CuyZ\Valinor\Type\Parser\Template\BasicTemplateParser;
use CuyZ\Valinor\Type\Parser\TypeParser;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ClassType;
use CuyZ\Valinor\Type\Types\InterfaceType;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use stdClass;

use function get_class;

final class GenericLexerTest extends TestCase
{
    private TypeParser $parser;

    protected function setUp(): void
    {
        parent::setUp();

        $lexer = new NativeLexer();
        $lexer = new ClassGenericLexer($lexer, new FakeTypeParserFactory(), new BasicTemplateParser());

        $this->parser = new LexingParser($lexer);
    }

    /**
     * @dataProvider parse_valid_types_returns_valid_result_data_provider
     *
     * @param class-string<Type> $type
     */
    public function test_parse_valid_types_returns_valid_result(string $raw, string $transformed, string $type): void
    {
        $result = $this->parser->parse($raw);

        self::assertSame($transformed, (string)$result);
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
                'type' => InterfaceType::class,
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
        $this->expectExceptionCode(1_618_054_357);
        $this->expectExceptionMessage("There are 2 missing generics for `$genericClassName<int, ?, ?>`.");

        $this->parser->parse("$genericClassName<int,");
    }

    public function test_missing_generic_closing_bracket_throws_exception(): void
    {
        $genericClassName = stdClass::class;

        $this->expectException(GenericClosingBracketMissing::class);
        $this->expectExceptionCode(1_604_333_677);
        $this->expectExceptionMessage("The closing bracket is missing for the generic `$genericClassName<string>`.");

        $this->parser->parse("$genericClassName<string");
    }

    public function test_missing_comma_in_generics_throws_exception(): void
    {
        $className = $this->classWithThreeTemplates();

        $this->expectException(GenericCommaMissing::class);
        $this->expectExceptionCode(1_615_829_484);
        $this->expectExceptionMessage("A comma is missing for the generic `$className<int, string, ?>`.");

        $this->parser->parse("$className<int, string bool>");
    }

    public function test_assigned_generic_not_found_throws_exception(): void
    {
        $className = $this->classWithThreeTemplates();

        $this->expectException(AssignedGenericNotFound::class);
        $this->expectExceptionCode(1_604_656_730);
        $this->expectExceptionMessage("No generic was assigned to the template(s) `TemplateB`, `TemplateC` for the class `$className`.");

        $this->parser->parse("$className<int>");
    }

    public function test_generic_with_no_template_throws_exception(): void
    {
        $className = $this->classWithOneTemplate();

        $this->expectException(CannotAssignGeneric::class);
        $this->expectExceptionCode(1_604_660_485);
        $this->expectExceptionMessage("Could not find a template to assign the generic(s) `string`, `bool` for the class `$className`.");

        $this->parser->parse("$className<int, string, bool>");
    }

    public function test_generic_with_non_matching_type_for_template_throws_exception(): void
    {
        $object =
            /**
             * @template Template of string
             */
            new class () { };

        $className = get_class($object);

        $this->expectException(InvalidAssignedGeneric::class);
        $this->expectExceptionCode(1_604_613_633);
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
            new class () { };

        $className = get_class($object);

        $this->expectException(InvalidClassTemplate::class);
        $this->expectExceptionCode(1_630_092_678);
        $this->expectExceptionMessage("Template error for class `$className`: The template `TemplateA` was defined at least twice.");

        $this->parser->parse("$className<int, string>");
    }

    public function test_invalid_template_type_throws_exception(): void
    {
        $object =
            /**
             * @template Template of InvalidType
             */
            new class () { };

        $className = get_class($object);

        $this->expectException(InvalidClassTemplate::class);
        $this->expectExceptionCode(1_630_092_678);
        $this->expectExceptionMessageMatches("/Template error for class `.*`: Invalid type `InvalidType` for the template `Template`: .*/");

        $this->parser->parse("$className<int, string>");
    }

    private function classWithOneTemplate(): string
    {
        $object =
            /**
             * @template TemplateA
             */
            new class () { };

        return get_class($object);
    }

    private function classWithThreeTemplates(): string
    {
        $object =
            /**
             * @template TemplateA
             * @template TemplateB
             * @template TemplateC
             */
            new class () { };

        return get_class($object);
    }

    private function classWithTemplateOfArrayKey(): string
    {
        $object =
            /**
             * @template TemplateA of array-key
             */
            new class () { };

        return get_class($object);
    }
}
