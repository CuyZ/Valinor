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
    /** @var non-empty-array<non-empty-string> */
    public const DEFAULT_SUPPORTED_DATETIME_FORMATS = [
        'Y-m-d\\TH:i:sP', // RFC 3339
        'Y-m-d\\TH:i:s.uP', // RFC 3339 with microseconds
        'U', // Unix Timestamp
    ];

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

    /** @var non-empty-array<non-empty-string> */
    public array $supportedDateFormats = self::DEFAULT_SUPPORTED_DATETIME_FORMATS;

    public bool $enableFlexibleCasting = false;

    public bool $allowSuperfluousKeys = false;

    public bool $allowPermissiveTypes = false;

    /** @var callable(Throwable): ErrorMessage */
    public $exceptionFilter;

    /** @var array<int, list<callable>> */
    public array $transformers = [];

    /** @var array<class-string, null> */
    public array $transformerAttributes = [];

    public function __construct()
    {
        $this->inferredMapping[DateTimeInterface::class] = static fn () => DateTimeImmutable::class;
        $this->exceptionFilter = fn (Throwable $exception) => throw $exception;
    }

    /**
     * @return list<callable>
     */
    public function transformersSortedByPriority(): array
    {
        krsort($this->transformers);

        $callables = [];

        foreach ($this->transformers as $list) {
            $callables = [...$callables, ...$list];
        }

        return $callables;
    }
}
