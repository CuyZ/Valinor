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
    private FunctionDefinitionRepository $functionDefinitionRepository;

    /** @var array<callable> */
    private array $callables;

    /** @var array<FunctionObject> */
    private array $functions = [];

    /**
     * @param array<callable> $callables
     */
    public function __construct(FunctionDefinitionRepository $functionDefinitionRepository, array $callables)
    {
        $this->functionDefinitionRepository = $functionDefinitionRepository;
        $this->callables = $callables;
    }

    /**
     * @param string|int $key
     */
    public function has($key): bool
    {
        return isset($this->callables[$key]);
    }

    /**
     * @param string|int $key
     */
    public function get($key): FunctionObject
    {
        return $this->function($key);
    }

    public function getIterator(): Traversable
    {
        foreach (array_keys($this->callables) as $key) {
            yield $key => $this->function($key);
        }
    }

    /**
     * @param string|int $key
     */
    private function function($key): FunctionObject
    {
        return $this->functions[$key] ??= new FunctionObject(
            $this->functionDefinitionRepository->for($this->callables[$key]),
            $this->callables[$key]
        );
    }
}
