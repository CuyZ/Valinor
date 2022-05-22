<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Library;

use CuyZ\Valinor\Mapper\Tree\Node;
use DateTimeImmutable;
use DateTimeInterface;
use Psr\SimpleCache\CacheInterface;

/** @internal */
final class Settings
{
    /** @var array<class-string, callable> */
    public array $interfaceMapping = [];

    /** @var array<class-string, null> */
    public array $nativeConstructors = [];

    /** @var list<callable> */
    public array $customConstructors = [];

    /** @var list<callable> */
    public array $valueModifier = [];

    /** @var array<callable(Node): void> */
    public array $nodeVisitors = [];

    /** @var CacheInterface<mixed> */
    public CacheInterface $cache;

    public bool $enableLegacyDoctrineAnnotations = PHP_VERSION_ID < 8_00_00;

    public function __construct()
    {
        $this->interfaceMapping[DateTimeInterface::class] = static fn () => DateTimeImmutable::class;
    }
}
