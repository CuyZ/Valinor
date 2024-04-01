<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Cache;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Definition\Repository\ClassDefinitionRepository;
use CuyZ\Valinor\Type\ObjectType;
use Psr\SimpleCache\CacheInterface;

use function sha1;

/** @internal */
final class CacheClassDefinitionRepository implements ClassDefinitionRepository
{
    public function __construct(
        private ClassDefinitionRepository $delegate,
        /** @var CacheInterface<ClassDefinition> */
        private CacheInterface $cache
    ) {}

    public function for(ObjectType $type): ClassDefinition
    {
        // @infection-ignore-all
        $key = 'class-definition' . sha1($type->toString());

        $entry = $this->cache->get($key);

        if ($entry) {
            return $entry;
        }

        $class = $this->delegate->for($type);

        $this->cache->set($key, $class);

        return $class;
    }
}
