<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Parser\Template;

use CuyZ\Valinor\Tests\Fake\Type\Parser\FakeTypeParser;
use CuyZ\Valinor\Type\Parser\Exception\Template\DuplicatedTemplateName;
use CuyZ\Valinor\Type\Parser\Exception\Template\InvalidTemplateType;
use CuyZ\Valinor\Type\Parser\Template\BasicTemplateParser;
use CuyZ\Valinor\Type\Types\NativeStringType;
use CuyZ\Valinor\Type\Types\MixedType;
use PHPUnit\Framework\TestCase;

final class BasicTemplateParserTest extends TestCase
{
    private BasicTemplateParser $parser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->parser = new BasicTemplateParser();
    }

    public function test_no_template_found_returns_empty_array(): void
    {
        self::assertEmpty($this->parser->templates('foo', new FakeTypeParser()));
    }

    public function test_templates_are_parsed_and_returned(): void
    {
        $templates = $this->parser->templates(
            <<<TXT
            @template TemplateA
            @template TemplateB of string
            TXT,
            new FakeTypeParser()
        );

        self::assertSame([
            'TemplateA' => MixedType::get(),
            'TemplateB' => NativeStringType::get(),
        ], $templates);
    }

    public function test_duplicated_template_name_throws_exception(): void
    {
        $this->expectException(DuplicatedTemplateName::class);
        $this->expectExceptionCode(1604612898);
        $this->expectExceptionMessage('The template `TemplateA` was defined at least twice.');

        $this->parser->templates(
            <<<TXT
            @template TemplateA
            @template TemplateA
            TXT,
            new FakeTypeParser()
        );
    }

    public function test_invalid_template_type_throws_exception(): void
    {
        $this->expectException(InvalidTemplateType::class);
        $this->expectExceptionCode(1607445951);
        $this->expectExceptionMessageMatches('/^Invalid type `InvalidType` for the template `T`: .*$/');

        $this->parser->templates('@template T of InvalidType', new FakeTypeParser());
    }
}
