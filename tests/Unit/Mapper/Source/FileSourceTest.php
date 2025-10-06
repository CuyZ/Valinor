<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Source;

use CuyZ\Valinor\Mapper\Source\Exception\FileExtensionNotHandled;
use CuyZ\Valinor\Mapper\Source\Exception\UnableToReadFile;
use CuyZ\Valinor\Mapper\Source\FileSource;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SplFileObject;

use function function_exists;
use function iterator_to_array;

final class FileSourceTest extends TestCase
{
    private vfsStreamDirectory $files;

    protected function setUp(): void
    {
        parent::setUp();

        $this->files = vfsStream::setup();
    }

    #[DataProvider('file_is_handled_properly_data_provider')]
    public function test_file_is_handled_properly(string $filename, string $content): void
    {
        $file = (vfsStream::newFile($filename))->withContent($content)->at($this->files);

        $source = new FileSource(new SplFileObject($file->url()));

        self::assertSame(['foo' => 'bar'], iterator_to_array($source));
    }

    public static function file_is_handled_properly_data_provider(): iterable
    {
        yield ['test-json.json', '{"foo": "bar"}'];
        yield ['test-json.JSON', '{"foo": "bar"}'];

        if (function_exists('yaml_parse')) {
            yield ['test-yaml.yaml', 'foo: bar'];
            yield ['test-yaml.YAML', 'foo: bar'];
            yield ['test-yml.yml', 'foo: bar'];
            yield ['test-yml.YML', 'foo: bar'];
        }
    }

    public function test_unhandled_extension_throws_exception(): void
    {
        $this->expectException(FileExtensionNotHandled::class);
        $this->expectExceptionMessage('The file extension `foo` is not handled.');

        $file = (vfsStream::newFile('some-unhandled-extension.foo'))
            ->withContent('foo')
            ->at($this->files);

        new FileSource(new SplFileObject($file->url()));
    }

    public function test_unreadable_file_throws_exception(): void
    {
        $file = (vfsStream::newFile('some-file.json'))
            ->withContent('{"foo": "bar"}')
            ->at($this->files);

        $fileObject = new SplFileObject($file->url());

        $file->chmod(0000);

        $this->expectException(UnableToReadFile::class);
        $this->expectExceptionMessage("Unable to read the file `{$file->url()}`.");

        new FileSource($fileObject);
    }
}
