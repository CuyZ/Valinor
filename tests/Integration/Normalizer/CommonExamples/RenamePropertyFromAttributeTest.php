<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Normalizer\CommonExamples;

use Attribute;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Normalizer\Format;
use PHPUnit\Framework\TestCase;

final class RenamePropertyFromAttributeTest extends TestCase
{
    public function test_rename_attribute_works_properly(): void
    {
        $result = (new MapperBuilder())
            ->registerTransformer(Rename::class)
            ->normalizer(Format::array())
            ->normalize(new class () {
                public function __construct(
                    public string $street = '221B Baker Street',
                    public string $zipCode = 'NW1 6XE',
                    #[Rename('town')]
                    public string $city = 'London',
                ) {}
            });

        self::assertSame([
            'street' => '221B Baker Street',
            'zipCode' => 'NW1 6XE',
            'town' => 'London',
        ], $result);
    }
}

#[Attribute(Attribute::TARGET_PROPERTY)]
final class Rename
{
    public function __construct(private string $keyName) {}

    public function normalizeKey(): string
    {
        return $this->keyName;
    }
}
