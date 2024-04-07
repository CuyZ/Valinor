<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Parser\Lexer;

use CuyZ\Valinor\Type\Parser\Lexer\TokenizedAnnotation;
use CuyZ\Valinor\Type\Parser\Lexer\Annotations;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function array_map;

final class AnnotationsTest extends TestCase
{
    /**
     * @param array<non-empty-string, list<non-empty-string>> $expectedAnnotations
     */
    #[DataProvider('annotations_are_parsed_properly_data_provider')]
    public function test_annotations_are_parsed_properly(string $docBlock, array $expectedAnnotations): void
    {
        $annotations = new Annotations($docBlock);

        foreach ($expectedAnnotations as $name => $expected) {
            $result = array_map(
                fn (TokenizedAnnotation $annotation) => $annotation->raw(),
                $annotations->allOf($name)
            );

            self::assertSame($expected, $result);
        }
    }

    public static function annotations_are_parsed_properly_data_provider(): iterable
    {
        yield 'annotation followed by text' => [
            'docBlock' => '@annotation some comment',
            'expectedAnnotations' => [
                '@annotation' => ['some comment'],
            ],
        ];

        yield 'annotation followed by another annotation' => [
            'docBlock' => '@annotation @another-annotation',
            'expectedAnnotations' => [
                '@annotation' => [],
                '@another-annotation' => [],
            ],
        ];
    }
}
