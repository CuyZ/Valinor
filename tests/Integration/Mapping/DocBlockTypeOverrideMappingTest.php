<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping;

use CuyZ\Valinor\Mapper\Exception\TypeErrorDuringMapping;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

final class DocBlockTypeOverrideMappingTest extends IntegrationTestCase
{
    public function test_valinor_param_overrides_unparseable_conditional_type(): void
    {
        $class = new class (0, null) {
            /**
             * @phpstan-param ($a is 1 ? int : null) $b
             * @valinor-param int|null $b
             */
            public function __construct(
                public readonly int $a,
                public readonly ?int $b,
            ) {}
        };

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($class::class, ['a' => 0, 'b' => null]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(0, $result->a);
        self::assertNull($result->b);
    }

    public function test_unparseable_conditional_type_without_override_throws_exception(): void
    {
        $class = (new class (0, null) {
            /**
             * @phpstan-param ($a is 1 ? int : null) $b
             */
            public function __construct(
                public readonly int $a,
                public readonly ?int $b,
            ) {}
        })::class;

        $this->expectException(TypeErrorDuringMapping::class);
        $this->expectExceptionMessage("Error while trying to map to `$class`: the type `(\$a is 1 ? int : null) \$b` for parameter `$class::__construct(\$b)` could not be resolved: unexpected token `a`, expected a valid type.");

        $this->mapperBuilder()->mapper()->map($class, ['a' => 0, 'b' => null]);
    }

    public function test_valinor_var_overrides_unparseable_property_type(): void
    {
        $class = new class () {
            /**
             * @var ($foo is 1 ? int : string)
             * @valinor-var non-empty-string
             * @phpstan-ignore property.phpDocType
             */
            public string $value;
        };

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($class::class, ['value' => 'foo']);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result->value);
    }
}
