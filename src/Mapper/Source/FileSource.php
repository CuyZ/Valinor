<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Source;

use CuyZ\Valinor\Mapper\Source\Exception\FileExtensionNotHandled;
use CuyZ\Valinor\Mapper\Source\Exception\UnableToReadFile;
use Iterator;
use IteratorAggregate;
use SplFileObject;
use Traversable;

use function strtolower;

/**
 * @api
 *
 * @implements IteratorAggregate<mixed>
 */
final class FileSource implements IteratorAggregate
{
    private string $filePath;

    /** @var Traversable<mixed> */
    private Traversable $delegate;

    public function __construct(SplFileObject $file)
    {
        $this->filePath = $file->getPathname();

        $content = $file->fread($file->getSize());

        /** @infection-ignore-all */
        if ($content === false || $content === '') {
            throw new UnableToReadFile($this->filePath);
        }

        $this->delegate = match (strtolower($file->getExtension())) {
            'json' => new JsonSource($content),
            'yaml', 'yml' => new YamlSource($content),
            default => throw new FileExtensionNotHandled($file->getExtension()),
        };
    }

    /**
     * @return Iterator<mixed>
     */
    public function getIterator(): Iterator
    {
        yield from $this->delegate;
    }
}
