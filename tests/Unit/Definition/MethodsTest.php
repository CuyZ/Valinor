<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Definition;

use CuyZ\Valinor\Definition\Exception\MethodNotFound;
use CuyZ\Valinor\Definition\Methods;
use CuyZ\Valinor\Tests\Fake\Definition\FakeMethodDefinition;
use CuyZ\Valinor\Tests\Traits\IteratorTester;
use PHPUnit\Framework\TestCase;

use function array_values;

final class MethodsTest extends TestCase
{
    use IteratorTester;

    public function test_method_can_be_found(): void
    {
        $method = FakeMethodDefinition::new();
        $methods = new Methods($method);

        self::assertFalse($methods->has('unknownMethod'));
        self::assertFalse($methods->hasConstructor());

        self::assertTrue($methods->has($method->name()));
        self::assertSame($method, $methods->get($method->name()));
    }

    public function test_get_non_existing_method_throws_exception(): void
    {
        $this->expectException(MethodNotFound::class);
        $this->expectExceptionCode(1_510_936_269);
        $this->expectExceptionMessage('The method `unknownMethod` does not exist.');

        (new Methods())->get('unknownMethod');
    }

    public function test_constructor_is_found(): void
    {
        $method = FakeMethodDefinition::constructor();
        $methods = new Methods($method);

        self::assertTrue($methods->hasConstructor());
        self::assertSame($method, $methods->constructor());
    }

    public function test_methods_are_countable(): void
    {
        $methods = new Methods(
            FakeMethodDefinition::new('methodA'),
            FakeMethodDefinition::new('methodB'),
            FakeMethodDefinition::new('methodC'),
        );

        self::assertCount(3, $methods);
    }

    public function test_methods_are_iterable(): void
    {
        $methodsInstances = [
            'methodA' => FakeMethodDefinition::new('methodA'),
            'methodB' => FakeMethodDefinition::new('methodB'),
            'methodC' => FakeMethodDefinition::new('methodC'),
        ];

        $methods = new Methods(...array_values($methodsInstances));

        $this->checkIterable($methods, $methodsInstances);
    }
}
