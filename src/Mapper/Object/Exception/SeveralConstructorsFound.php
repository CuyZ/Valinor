<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Exception;

use CuyZ\Valinor\Definition\MethodDefinition;
use CuyZ\Valinor\Mapper\Object\Factory\ObjectBuilderNotFound;
use CuyZ\Valinor\Mapper\Tree\Message\Message;
use RuntimeException;

use function get_debug_type;

/** @api */
final class SeveralConstructorsFound extends RuntimeException implements Message, ObjectBuilderNotFound
{
    /** @var non-empty-list<MethodDefinition> */
    private array $constructors;

    /**
     * @param mixed $source
     * @param non-empty-list<MethodDefinition> $constructors
     */
    public function __construct($source, array $constructors)
    {
        $this->constructors = $constructors;

        $type = get_debug_type($source);

        parent::__construct(
            "Could not map input of type `$type`.",
            1642787246
        );
    }

    /**
     * @return non-empty-list<MethodDefinition>
     */
    public function constructors(): array
    {
        return $this->constructors;
    }
}
