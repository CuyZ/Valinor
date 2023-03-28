<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Source;

use CuyZ\Valinor\Mapper\Source\Exception\InvalidYaml;
use CuyZ\Valinor\Mapper\Source\Exception\SourceNotIterable;
use CuyZ\Valinor\Mapper\Source\YamlSource;
use PHPUnit\Framework\TestCase;

use function iterator_to_array;

/**
 * @requires extension yaml
 */
final class YamlSourceTest extends TestCase
{
    public function test_valid_yaml_is_parsed_correctly(): void
    {
        $source = new YamlSource('foo: bar');

        self::assertSame(['foo' => 'bar'], iterator_to_array($source));
    }

    public function test_invalid_yaml_throws_exception(): void
    {
        try {
            new YamlSource('@ invalid yaml');

            self::fail();
        } catch (InvalidYaml $exception) {
            self::assertSame(1629990223, $exception->getCode());
            self::assertSame('Invalid YAML source.', $exception->getMessage());
            self::assertSame('@ invalid yaml', $exception->source());
        }
    }

    public function test_invalid_yaml_type_throws_exception(): void
    {
        try {
            new YamlSource('foo');

            self::fail();
        } catch (SourceNotIterable $exception) {
            self::assertSame(1566307291, $exception->getCode());
            self::assertSame('Invalid source, expected an iterable.', $exception->getMessage());
            self::assertSame('foo', $exception->source());
        }
    }
}
