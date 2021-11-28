<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Definition;

use CuyZ\Valinor\Definition\CombinedAttributes;
use CuyZ\Valinor\Definition\DoctrineAnnotations;
use CuyZ\Valinor\Definition\NativeAttributes;
use CuyZ\Valinor\Tests\Fixture\Annotation\AnnotationWithArguments;
use CuyZ\Valinor\Tests\Fixture\Annotation\BasicAnnotation;
use CuyZ\Valinor\Tests\Fixture\Attribute\AttributeWithArguments;
use CuyZ\Valinor\Tests\Fixture\Attribute\BasicAttribute;
use CuyZ\Valinor\Tests\Fixture\Object\ObjectWithAttributes;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

use function iterator_to_array;

final class CombinedAttributesTest extends TestCase
{
    public function test_empty_attributes_returns_empty_results(): void
    {
        $attributes = new CombinedAttributes(
            new DoctrineAnnotations(new ReflectionClass(stdClass::class)),
        );

        self::assertEmpty(iterator_to_array($attributes));
        self::assertCount(0, $attributes);
        self::assertNull($attributes->nativeAttributes());
        self::assertFalse($attributes->has(BasicAttribute::class));
        self::assertFalse($attributes->has(BasicAnnotation::class));
    }

    public function test_only_annotations_are_retrieved(): void
    {
        $doctrineAnnotations = new DoctrineAnnotations(new ReflectionClass($this->classWithAnnotations()));
        $attributes = new CombinedAttributes($doctrineAnnotations);

        self::assertNull($attributes->nativeAttributes());
        self::assertCount(2, iterator_to_array($attributes));
        self::assertCount(2, $attributes);
        self::assertCount(1, $attributes->ofType(BasicAnnotation::class));
        self::assertCount(1, $attributes->ofType(AnnotationWithArguments::class));
    }

    /**
     * @requires PHP >= 8
     */
    public function test_only_attributes_are_retrieved(): void
    {
        $doctrineAnnotations = new DoctrineAnnotations(new ReflectionClass(stdClass::class));
        $nativeAttributes = new NativeAttributes(new ReflectionClass(ObjectWithAttributes::class));
        $attributes = new CombinedAttributes($doctrineAnnotations, $nativeAttributes);

        self::assertCount(2, iterator_to_array($attributes));
        self::assertCount(2, $attributes);
        self::assertTrue($attributes->has(BasicAttribute::class));
        self::assertCount(1, $attributes->ofType(BasicAttribute::class));
        self::assertCount(1, $attributes->ofType(AttributeWithArguments::class));
    }

    /**
     * @requires PHP >= 8
     */
    public function test_both_annotations_and_attributes_are_retrieved(): void
    {
        $doctrineAnnotations = new DoctrineAnnotations(new ReflectionClass($this->classWithAnnotations()));
        $nativeAttributes = new NativeAttributes(new ReflectionClass(ObjectWithAttributes::class));
        $attributes = new CombinedAttributes($doctrineAnnotations, $nativeAttributes);

        self::assertSame($nativeAttributes, $attributes->nativeAttributes());
        self::assertSame($doctrineAnnotations, $attributes->doctrineAnnotations());
        self::assertCount(4, $attributes);
        self::assertTrue($attributes->has(BasicAttribute::class));
        self::assertTrue($attributes->has(AttributeWithArguments::class));
        self::assertTrue($attributes->has(BasicAnnotation::class));
        self::assertTrue($attributes->has(AnnotationWithArguments::class));
        self::assertCount(1, $attributes->ofType(BasicAttribute::class));
        self::assertCount(1, $attributes->ofType(AttributeWithArguments::class));
        self::assertCount(1, $attributes->ofType(BasicAnnotation::class));
        self::assertCount(1, $attributes->ofType(AnnotationWithArguments::class));
    }

    private function classWithAnnotations(): object
    {
        return
            /**
             * @BasicAnnotation
             * @AnnotationWithArguments(foo="foo")
             */
            new class () { };
    }
}
