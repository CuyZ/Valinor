<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Library;

use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use DateTimeImmutable;
use DateTimeInterface;
use Psr\SimpleCache\CacheInterface;
use Throwable;

/** @internal */
final class Settings
{
    /** @var array<class-string|interface-string, callable> */
    public array $inferredMapping = [];

    /** @var array<class-string, null> */
    public array $nativeConstructors = [];

    /** @var list<callable> */
    public array $customConstructors = [];

    /** @var list<callable> */
    public array $valueModifier = [];

    /** @var CacheInterface<mixed> */
    public CacheInterface $cache;

    public bool $enableFlexibleCasting = false;

    public bool $allowSuperfluousKeys = false;

    public bool $allowPermissiveTypes = false;

    /** @var callable(Throwable): ErrorMessage */
    public $exceptionFilter;

    public function __construct()
    {
        $this->inferredMapping[DateTimeInterface::class] = static fn () => DateTimeImmutable::class;
        $this->exceptionFilter = fn (Throwable $exception) => throw $exception;
    }
}
