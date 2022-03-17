<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Library;

use CuyZ\Valinor\Mapper\Tree\Node;
use DateTimeImmutable;
use DateTimeInterface;

use function sys_get_temp_dir;

/** @internal */
final class Settings
{
    /** @var array<class-string, callable> */
    public array $interfaceMapping = [];

    /** @var list<callable> */
    public array $objectBinding = [];

    /** @var list<callable> */
    public array $valueModifier = [];

    /** @var array<callable(Node): void> */
    public array $nodeVisitors = [];

    public string $cacheDir;

    public bool $enableLegacyDoctrineAnnotations = PHP_VERSION_ID < 8_00_00;

    public function __construct()
    {
        $this->cacheDir = sys_get_temp_dir();
        $this->interfaceMapping[DateTimeInterface::class] = static fn () => DateTimeImmutable::class;
    }
}
