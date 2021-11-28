<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Source;

use CuyZ\Valinor\Mapper\Source\Exception\FileExtensionNotHandled;
use CuyZ\Valinor\Mapper\Source\FileSource;
use PHPUnit\Framework\TestCase;
use SplFileObject;

use function file_put_contents;
use function function_exists;
use function iterator_to_array;
use function sys_get_temp_dir;
use function uniqid;

final class FileSourceTest extends TestCase
{
    /**
     * @dataProvider file_is_handled_properly_data_provider
     */
    public function test_file_is_handled_properly(string $filename): void
    {
        $source = new FileSource(new SplFileObject($filename));

        self::assertSame(['foo' => 'bar'], iterator_to_array($source));
        self::assertSame($filename, $source->sourceName());
    }

    public function file_is_handled_properly_data_provider(): iterable
    {
        yield [$this->file('test-json.json', '{"foo": "bar"}')];
        yield [$this->file('test-json.JSON', '{"foo": "bar"}')];

        if (function_exists('yaml_parse')) {
            yield [$this->file('test-yaml.yaml', 'foo: bar')];
            yield [$this->file('test-yaml.YAML', 'foo: bar')];
            yield [$this->file('test-yml.yml', 'foo: bar')];
            yield [$this->file('test-yml.YML', 'foo: bar')];
        }
    }

    public function test_unhandled_extension_throws_exception(): void
    {
        $this->expectException(FileExtensionNotHandled::class);
        $this->expectExceptionCode(1629991744);
        $this->expectExceptionMessage('The file extension `foo` is not handled.');

        $filename = $this->file('some-unhandled-extension.foo', 'foo');

        new FileSource(new SplFileObject($filename));
    }

    private function file(string $name, string $data): string
    {
        $filename = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('', true) . $name;
        file_put_contents($filename, $data);

        return $filename;
    }
}
