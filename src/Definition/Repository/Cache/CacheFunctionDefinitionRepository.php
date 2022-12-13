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
    public function __construct(
        private FunctionDefinitionRepository $delegate,
        /** @var CacheInterface<FunctionDefinition> */
        private CacheInterface $cache
    ) {
    }

    public function for(callable $function): FunctionDefinition
    {
        $reflection = Reflection::function($function);
        $key = "function-definition-{$reflection->getFileName()}-{$reflection->getStartLine()}-{$reflection->getEndLine()}";

        $entry = $this->cache->get($key);

        if ($entry) {
            return $entry;
        }

        $definition = $this->delegate->for($function);

        $this->cache->set($key, $definition);

        return $definition;
    }
}
