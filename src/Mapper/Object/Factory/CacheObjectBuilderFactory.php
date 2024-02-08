<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Factory;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Mapper\Object\ObjectBuilder;
use Psr\SimpleCache\CacheInterface;

/** @internal */
final class CacheObjectBuilderFactory implements ObjectBuilderFactory
{
    public function __construct(
        private ObjectBuilderFactory $delegate,
        /** @var CacheInterface<list<ObjectBuilder>> */
        private CacheInterface $cache
    ) {}

    public function for(ClassDefinition $class): array
    {
        $signature = $class->type->toString();

        $entry = $this->cache->get($signature);

        if ($entry) {
            return $entry;
        }

        $builders = $this->delegate->for($class);

        $this->cache->set($signature, $builders);

        return $builders;
    }
}
