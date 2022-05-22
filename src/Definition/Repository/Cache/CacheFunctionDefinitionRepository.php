<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Cache;

use CuyZ\Valinor\Definition\FunctionDefinition;
use CuyZ\Valinor\Definition\Repository\FunctionDefinitionRepository;
use CuyZ\Valinor\Utility\Reflection\Reflection;
use Psr\SimpleCache\CacheInterface;

/** @internal */
final class CacheFunctionDefinitionRepository implements FunctionDefinitionRepository
{
    private FunctionDefinitionRepository $delegate;

    /** @var CacheInterface<FunctionDefinition> */
    private CacheInterface $cache;

    /**
     * @param CacheInterface<FunctionDefinition> $cache
     */
    public function __construct(FunctionDefinitionRepository $delegate, CacheInterface $cache)
    {
        $this->delegate = $delegate;
        $this->cache = $cache;
    }

    public function for(callable $function): FunctionDefinition
    {
        $reflection = Reflection::function($function);
        $key = "function-definition-{$reflection->getFileName()}-{$reflection->getStartLine()}-{$reflection->getEndLine()}";

        if ($this->cache->has($key)) {
            $entry = $this->cache->get($key);

            if ($entry) {
                return $entry;
            }
        }

        $definition = $this->delegate->for($function);

        $this->cache->set($key, $definition);

        return $definition;
    }
}
