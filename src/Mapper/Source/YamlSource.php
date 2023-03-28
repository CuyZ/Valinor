<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Source;

use CuyZ\Valinor\Mapper\Source\Exception\InvalidSource;
use CuyZ\Valinor\Mapper\Source\Exception\InvalidYaml;
use CuyZ\Valinor\Mapper\Source\Exception\SourceNotIterable;
use CuyZ\Valinor\Mapper\Source\Exception\YamlExtensionNotEnabled;
use Iterator;
use IteratorAggregate;

use Traversable;

use function function_exists;
use function is_iterable;
use function yaml_parse;

/**
 * @api
 *
 * @implements IteratorAggregate<mixed>
 */
final class YamlSource implements IteratorAggregate
{
    /** @var iterable<mixed> */
    private iterable $source;

    /**
     * @throws InvalidSource
     */
    public function __construct(string $yamlSource)
    {
        /** @infection-ignore-all */
        // @codeCoverageIgnoreStart
        if (! function_exists('yaml_parse')) {
            throw new YamlExtensionNotEnabled();
        }
        // @codeCoverageIgnoreEnd

        $source = @yaml_parse($yamlSource);

        if ($source === false) {
            throw new InvalidYaml($yamlSource);
        }

        if (! is_iterable($source)) {
            throw new SourceNotIterable($yamlSource);
        }

        $this->source = $source;
    }

    /**
     * @return Iterator<mixed>
     */
    public function getIterator(): Traversable
    {
        yield from $this->source;
    }
}
