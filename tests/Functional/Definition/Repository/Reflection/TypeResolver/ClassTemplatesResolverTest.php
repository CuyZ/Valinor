<?php

namespace CuyZ\Valinor\Tests\Functional\Definition\Repository\Reflection\TypeResolver;

use CuyZ\Valinor\Definition\Repository\Reflection\TypeResolver\ClassTemplatesResolver;
use CuyZ\Valinor\Type\Parser\Exception\Template\DuplicatedTemplateName;
use PHPUnit\Framework\TestCase;
use stdClass;

final class ClassTemplatesResolverTest extends TestCase
{
    public function test_no_template_found_for_class_returns_empty_array(): void
    {
        $templates = (new ClassTemplatesResolver())->resolveTemplatesFrom(stdClass::class);

        self::assertEmpty($templates);
    }

    public function test_templates_are_parsed_and_returned(): void
    {
        $class =
            /**
             * @template TemplateA
             * @template TemplateB of string
             */
            new class () {};

        $templates = (new ClassTemplatesResolver())->resolveTemplatesFrom($class::class);

        self::assertSame([
            'TemplateA' => null,
            'TemplateB' => 'string',
        ], $templates);
    }

    public function test_duplicated_template_name_throws_exception(): void
    {
        $class =
            /**
             * @template TemplateA
             * @template TemplateA of string
             */
            new class () {};

        $className = $class::class;

        $this->expectException(DuplicatedTemplateName::class);
        $this->expectExceptionMessage("The template `TemplateA` in class `$className` was defined at least twice.");

        (new ClassTemplatesResolver())->resolveTemplatesFrom($className);
    }
}
