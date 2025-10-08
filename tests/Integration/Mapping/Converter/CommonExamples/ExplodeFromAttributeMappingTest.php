<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Converter\CommonExamples;

use Attribute;
use CuyZ\Valinor\Mapper\AsConverter;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

use function explode;

final class ExplodeFromAttributeMappingTest extends IntegrationTestCase
{
    public function test_can_use_explode_converter_attribute(): void
    {
        $class = new class () {
            /** @var list<string> */
            #[Explode(separator: ',')]
            public array $value;
        };

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($class::class, 'foo,bar,baz');

            self::assertSame(['foo', 'bar', 'baz'], $result->value);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }
    }
}

#[Attribute, AsConverter]
final class Explode
{
    public function __construct(
        /** @var non-empty-string */
        private string $separator,
    ) {}

    /**
     * @return list<string>
     */
    public function map(string $value): array
    {
        return explode($this->separator, $value);
    }
}
