<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Factory;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Mapper\Object\ObjectBuilder;
use Psr\SimpleCache\CacheInterface;

/** @internal */
final class CacheObjectBuilderFactory implements ObjectBuilderFactory
{
    private ObjectBuilderFactory $delegate;

    /** @var CacheInterface<iterable<ObjectBuilder>> */
    private CacheInterface $cache;

    /**
     * @param CacheInterface<iterable<ObjectBuilder>> $cache
     */
    public function __construct(ObjectBuilderFactory $delegate, CacheInterface $cache)
    {
        $this->delegate = $delegate;
        $this->cache = $cache;
    }

    public function for(ClassDefinition $class): iterable
    {
        $signature = $class->type()->toString();

        if ($this->cache->has($signature)) {
            $entry = $this->cache->get($signature);

            if ($entry) {
                return $entry;
            }
        }

        $builders = $this->delegate->for($class);

        $this->cache->set($signature, $builders);

        return $builders;
    }
}
