<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Cache;

use CuyZ\Valinor\Cache\Cache;
use CuyZ\Valinor\Cache\CacheEntry;
use CuyZ\Valinor\Definition\FunctionDefinition;
use CuyZ\Valinor\Definition\Repository\Cache\Compiler\FunctionDefinitionCompiler;
use CuyZ\Valinor\Definition\Repository\FunctionDefinitionRepository;
use CuyZ\Valinor\Utility\Reflection\Reflection;

/** @internal */
final class CompiledFunctionDefinitionRepository implements FunctionDefinitionRepository
{
    public function __construct(
        private FunctionDefinitionRepository $delegate,
        /** @var Cache<FunctionDefinition> */
        private Cache $cache,
        private FunctionDefinitionCompiler $compiler,
    ) {}

    public function for(callable $function): FunctionDefinition
    {
        $reflection = Reflection::function($function);

        // @infection-ignore-all
        $key = "function-definition-\0" . $reflection->getFileName() . ':' . $reflection->getStartLine() . '-' . $reflection->getEndLine();

        $entry = $this->cache->get($key);

        if ($entry) {
            return $entry->forCallable($function);
        }

        $definition = $this->delegate->for($function);

        $code = 'fn () => ' . $this->compiler->compile($definition);
        $filesToWatch = $definition->fileName ? [$definition->fileName] : [];

        $this->cache->set($key, new CacheEntry($code, $filesToWatch));

        /** @var FunctionDefinition */
        return $this->cache->get($key)?->forCallable($function);
    }
}
