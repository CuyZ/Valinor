<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Namespace;

use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

final class RegisteredStaticConstructorWithReturnTypeInDocBlockTest extends IntegrationTestCase
{
    // @see https://github.com/CuyZ/Valinor/issues/461
    public function test_registered_static_constructor_with_return_type_in_doc_block_works_properly(): void
    {
        $result = $this->mapperBuilder()
            ->registerConstructor(
                SomeClassWithStaticConstructor::staticConstructorWithReturnTypeInDocBlock(...),
            )
            ->mapper()
            ->map(SomeClassWithStaticConstructor::class, 'foo');

        self::assertSame('foo', $result->value);
    }
}

interface SomeSimpleInterface {}

final class SomeClassWithStaticConstructor implements SomeSimpleInterface
{
    public function __construct(public string $value) {}

    /**
     * @return SomeSimpleInterface
     */
    public static function staticConstructorWithReturnTypeInDocBlock(string $value)
    {
        return new self($value);
    }
}
