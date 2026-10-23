<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Configurator;

use CuyZ\Valinor\Mapper\Configurator\MapExplodedStringToList;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

final class MapExplodedStringToListTest extends IntegrationTestCase
{
    public function test_attribute_explodes_string_to_list(): void
    {
        $class = new class () {
            /** @var list<string> */
            #[MapExplodedStringToList(separator: ',')]
            public array $value;
        };

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($class::class, 'XS,S,M,L,XL');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(['XS', 'S', 'M', 'L', 'XL'], $result->value);
    }

    public function test_attribute_explodes_string_to_list_on_promoted_property(): void
    {
        $class = new class ([]) {
            /**
             * @param list<string> $value
             */
            public function __construct(
                #[MapExplodedStringToList(separator: ',')]
                public array $value,
            ) {}
        };

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($class::class, 'XS,S,M,L,XL');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(['XS', 'S', 'M', 'L', 'XL'], $result->value);
    }

    public function test_attribute_explodes_string_with_multi_character_separator(): void
    {
        $class = new class () {
            /** @var list<string> */
            #[MapExplodedStringToList(separator: ', ')]
            public array $value;
        };

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($class::class, 'admin, editor, viewer');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(['admin', 'editor', 'viewer'], $result->value);
    }

    public function test_attribute_explodes_string_then_casts_items(): void
    {
        $class = new class () {
            /** @var list<int> */
            #[MapExplodedStringToList(separator: ',')]
            public array $value;
        };

        try {
            $result = $this->mapperBuilder()
                ->allowScalarValueCasting()
                ->mapper()
                ->map($class::class, '1,2,3');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame([1, 2, 3], $result->value);
    }
}
