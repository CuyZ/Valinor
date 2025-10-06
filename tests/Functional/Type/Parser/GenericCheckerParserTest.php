<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Functional\Type\Parser;

use CuyZ\Valinor\Type\Parser\Exception\Generic\AssignedGenericNotFound;
use CuyZ\Valinor\Type\Parser\Exception\Generic\InvalidAssignedGeneric;
use CuyZ\Valinor\Type\Parser\Exception\Template\InvalidClassTemplate;
use CuyZ\Valinor\Type\Parser\Factory\LexingTypeParserFactory;
use CuyZ\Valinor\Type\Parser\GenericCheckerParser;
use PHPUnit\Framework\TestCase;

final class GenericCheckerParserTest extends TestCase
{
    private GenericCheckerParser $parser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->parser = new GenericCheckerParser(
            (new LexingTypeParserFactory())->buildDefaultTypeParser(),
            new LexingTypeParserFactory(),
        );
    }

    public function test_assigned_generic_not_found_throws_exception(): void
    {
        $object =
            /**
             * @template TemplateA
             * @template TemplateB
             * @template TemplateC
             */
            new class () {};

        $className = $object::class;

        $this->expectException(AssignedGenericNotFound::class);
        $this->expectExceptionMessage("No generic was assigned to the template(s) `TemplateB`, `TemplateC` for the class `$className`.");

        $this->parser->parse("$className<int>");
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
        $this->expectExceptionMessage("The generic `bool` is not a subtype of `array-key` for the template `Template` of the class `$className`.");

        $this->parser->parse("$className<bool>");
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
        $this->expectExceptionMessage("Invalid template `Template` for class `$className`: Cannot parse unknown symbol `InvalidType`.");

        $this->parser->parse("$className<int>");
    }
}
