<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition;

use CuyZ\Valinor\Definition\Exception\CallbackNotFound;
use CuyZ\Valinor\Definition\Exception\FunctionNotFound;
use CuyZ\Valinor\Definition\Repository\FunctionDefinitionRepository;
use IteratorAggregate;
use Traversable;

use function array_keys;

/**
 * @internal
 *
 * @implements IteratorAggregate<string|int, FunctionDefinition>
 */
final class FunctionsContainer implements IteratorAggregate
{
    private FunctionDefinitionRepository $functionDefinitionRepository;

    /** @var array<callable> */
    private array $callables;

    /** @var array<array{definition: FunctionDefinition, callback: callable}> */
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
    public function get($key): FunctionDefinition
    {
        if (! $this->has($key)) {
            throw new FunctionNotFound($key);
        }

        return $this->function($key)['definition'];
    }

    public function callback(FunctionDefinition $function): callable
    {
        foreach ($this->functions as $data) {
            if ($function === $data['definition']) {
                return $data['callback'];
            }
        }

        throw new CallbackNotFound($function);
    }

    public function getIterator(): Traversable
    {
        foreach (array_keys($this->callables) as $key) {
            yield $key => $this->function($key)['definition'];
        }
    }

    /**
     * @param string|int $key
     * @return array{definition: FunctionDefinition, callback: callable}
     */
    private function function($key): array
    {
        /** @infection-ignore-all */
        return $this->functions[$key] ??= [
            'callback' => $this->callables[$key],
            'definition' => $this->functionDefinitionRepository->for($this->callables[$key]),
        ];
    }
}
