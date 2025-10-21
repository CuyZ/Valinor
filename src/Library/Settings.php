<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Library;

use Closure;
use CuyZ\Valinor\Cache\Cache;
use CuyZ\Valinor\Mapper\Object\Constructor;
use CuyZ\Valinor\Mapper\Object\DynamicConstructor;
use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use DateTimeImmutable;
use DateTimeInterface;
use ReflectionFunction;
use Throwable;

use function array_keys;
use function array_map;
use function array_merge;
use function array_values;
use function hash;
use function implode;
use function krsort;
use function serialize;

/** @internal */
final class Settings
{
    /** @var non-empty-array<non-empty-string> */
    public const DEFAULT_SUPPORTED_DATETIME_FORMATS = [
        'Y-m-d\\TH:i:sP', // RFC 3339
        'Y-m-d\\TH:i:s.uP', // RFC 3339 with microseconds
        'U', // Unix Timestamp
        'U.u', // Unix Timestamp with microseconds
    ];

    /** @var array<class-string|interface-string, callable> */
    public array $inferredMapping = [];

    /** @var array<class-string, null> */
    public array $nativeConstructors = [];

    /** @var list<callable> */
    public array $customConstructors = [];

    /** @pure */
    public Cache $cache;

    /** @var non-empty-list<non-empty-string> */
    public array $supportedDateFormats = self::DEFAULT_SUPPORTED_DATETIME_FORMATS;

    public bool $allowScalarValueCasting = false;

    public bool $allowNonSequentialList = false;

    public bool $allowUndefinedValues = false;

    public bool $allowSuperfluousKeys = false;

    public bool $allowPermissiveTypes = false;

    /** @var array<int, list<callable>> */
    public array $mapperConverters = [];

    /** @var array<class-string, null> */
    public array $mapperConverterAttributes = [];

    /** @var callable(Throwable): ErrorMessage */
    public mixed $exceptionFilter;

    /** @var array<int, list<callable>> */
    public array $normalizerTransformers = [];

    /** @var array<class-string, null> */
    public array $normalizerTransformerAttributes = [];

    private string $hash;

    public function __construct()
    {
        $this->inferredMapping[DateTimeInterface::class] = static fn () => DateTimeImmutable::class;
        $this->exceptionFilter = fn (Throwable $exception) => throw $exception;
    }

    /**
     * @return non-empty-list<class-string>
     */
    public function allowedAttributes(): array
    {
        return [
            Constructor::class,
            DynamicConstructor::class,
            ...array_keys($this->mapperConverterAttributes),
            ...array_keys($this->normalizerTransformerAttributes),
        ];
    }

    /**
     * @return list<callable>
     */
    public function convertersSortedByPriority(): array
    {
        krsort($this->mapperConverters);

        $callables = [];

        foreach ($this->mapperConverters as $list) {
            $callables = [...$callables, ...$list];
        }

        return $callables;
    }

    /**
     * @return list<callable>
     */
    public function transformersSortedByPriority(): array
    {
        krsort($this->normalizerTransformers);

        $callables = [];

        foreach ($this->normalizerTransformers as $list) {
            $callables = [...$callables, ...$list];
        }

        return $callables;
    }

    /**
     * Returns a unique hash, based on all the settings values that were set in
     * this instance.
     */
    public function hash(): string
    {
        return $this->hash ??= hash('xxh128', serialize([
            $this->nativeConstructors,
            $this->supportedDateFormats,
            $this->allowScalarValueCasting,
            $this->allowNonSequentialList,
            $this->allowUndefinedValues,
            $this->allowSuperfluousKeys,
            $this->allowPermissiveTypes,
            $this->mapperConverterAttributes,
            $this->normalizerTransformerAttributes,
            implode('', array_map(function (callable $callable) {
                $reflection = new ReflectionFunction(Closure::fromCallable($callable));

                return ($reflection->getClosureCalledClass()->name ?? $reflection->getFileName()) . $reflection->getStartLine() . $reflection->getEndLine();
            }, $this->callables())),
        ]));
    }

    /**
     * @return non-empty-list<callable>
     */
    public function callables(): array
    {
        return array_values([
            $this->exceptionFilter,
            ...$this->inferredMapping,
            ...$this->customConstructors,
            ...array_merge(...$this->mapperConverters),
            ...array_merge(...$this->normalizerTransformers),
        ]);
    }
}
