<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition;

use CuyZ\Valinor\Definition\Repository\FunctionDefinitionRepository;
use IteratorAggregate;
use Traversable;

use function array_keys;

/**
 * @internal
 *
 * @implements IteratorAggregate<string|int, FunctionObject>
 */
final class FunctionsContainer implements IteratorAggregate
{
    /** @var array<FunctionObject> */
    private array $functions = [];

    public function __construct(
        private FunctionDefinitionRepository $functionDefinitionRepository,
        /** @var array<callable> */
        private array $callables
    ) {
    }

    public function has(string|int $key): bool
    {
        return isset($this->callables[$key]);
    }

    public function get(string|int $key): FunctionObject
    {
        return $this->function($key);
    }

    public function getIterator(): Traversable
    {
        foreach (array_keys($this->callables) as $key) {
            yield $key => $this->function($key);
        }
    }

    private function function(string|int $key): FunctionObject
    {
        return $this->functions[$key] ??= new FunctionObject(
            $this->functionDefinitionRepository->for($this->callables[$key]),
            $this->callables[$key]
        );
    }
}
