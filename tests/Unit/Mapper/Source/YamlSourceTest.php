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
        $this->expectException(InvalidYaml::class);
        $this->expectExceptionCode(1629990223);
        $this->expectExceptionMessage('The given value is not a valid YAML entry.');

        new YamlSource('@');
    }

    public function test_invalid_yaml_type_throws_exception(): void
    {
        $this->expectException(SourceNotIterable::class);
        $this->expectExceptionCode(1566307291);
        $this->expectExceptionMessage("Invalid source 'foo', expected an iterable.");

        new YamlSource('foo');
    }
}
