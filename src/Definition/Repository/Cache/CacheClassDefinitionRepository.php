<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Cache;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Definition\Repository\ClassDefinitionRepository;
use CuyZ\Valinor\Type\Types\ClassType;
use Psr\SimpleCache\CacheInterface;

/** @internal */
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

    public function for(ClassType $type): ClassDefinition
    {
        $key = "class-definition-$type";

        if ($this->cache->has($key)) {
            $entry = $this->cache->get($key);

            if ($entry) {
                return $entry;
            }
        }

        $class = $this->delegate->for($type);

        $this->cache->set($key, $class);

        return $class;
    }
}
