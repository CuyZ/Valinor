<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Cache;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Definition\ClassSignature;
use CuyZ\Valinor\Definition\Repository\ClassDefinitionRepository;
use Psr\SimpleCache\CacheInterface;

final class CacheClassDefinitionRepository implements ClassDefinitionRepository
{
    private ClassDefinitionRepository $delegate;

    /** @var CacheInterface<ClassDefinition> */
    private CacheInterface $cache;

    /**
     * @param CacheInterface<ClassDefinition> $cache
     */
    public function __construct(ClassDefinitionRepository $delegate, CacheInterface $cache)
    {
        $this->delegate = $delegate;
        $this->cache = $cache;
    }

    public function for(ClassSignature $signature): ClassDefinition
    {
        $key = "class-definition-$signature";

        if ($this->cache->has($key)) {
            return $this->cache->get($key);
        }

        $class = $this->delegate->for($signature);

        $this->cache->set($key, $class);

        return $class;
    }
}
