<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Utility\Reflection;

use CuyZ\Valinor\Utility\Reflection\Annotations;
use PHPUnit\Framework\TestCase;

final class AnnotationsTest extends TestCase
{
    public function test_local_alias_annotations_are_parsed_properly(): void
    {
        $class = (
            /**
             * @phpstan-type SomeType = string
             * @template SomeTemplate
             * @phpstan-type SomeOtherType = int
             */
        new class () {})::class;

        $annotations = Annotations::forLocalAliases($class);

        self::assertSame('@phpstan-type', $annotations[0]->name());
        self::assertSame('SomeType = string', $annotations[0]->raw());

        self::assertSame('@phpstan-type', $annotations[1]->name());
        self::assertSame('SomeOtherType = int', $annotations[1]->raw());
    }

    public function test_annotation_with_no_end_trailing_space_does_not_block_annotation_parsing(): void
    {
        $class = (
            /**
             * @phpstan-type SomeType = string
             * @some-unrelated-annotation-with-no-end-trailing-space*/
        new class () {})::class;

        $annotations = Annotations::forLocalAliases($class);

        self::assertSame('@phpstan-type', $annotations[0]->name());
        self::assertSame('SomeType = string', $annotations[0]->raw());
    }
}
