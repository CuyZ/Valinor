<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Exception;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Tree\Message\Message;
use CuyZ\Valinor\Mapper\Tree\Node;
use Iterator;
use RuntimeException;
use Throwable;

use function array_filter;
use function array_values;
use function iterator_to_array;

final class CannotMapObject extends RuntimeException implements MappingError
{
    /** @var array<string, array<Throwable&Message>> */
    private array $errors;

    public function __construct(Node $node)
    {
        $this->errors = iterator_to_array($this->errors($node));

        parent::__construct(
            "Could not map an object of type `{$node->type()}` with the given source.",
            1617193185
        );
    }

    public function describe(): array
    {
        return $this->errors;
    }

    /**
     * @return Iterator<string, array<Throwable&Message>>
     */
    private function errors(Node $node): Iterator
    {
        $errors = array_filter(
            $node->messages(),
            static fn (Message $message) => $message instanceof Throwable
        );

        if (! empty($errors)) {
            yield $node->path() => array_values($errors);
        }

        foreach ($node->children() as $child) {
            yield from $this->errors($child);
        }
    }
}
