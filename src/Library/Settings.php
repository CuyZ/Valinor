<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Library;

use Closure;
use CuyZ\Valinor\Cache\Cache;
use CuyZ\Valinor\Mapper\Object\Constructor;
use CuyZ\Valinor\Mapper\Object\DynamicConstructor;
use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Normalizer\AsTransformer;
use DateTimeImmutable;
use DateTimeInterface;
use ReflectionFunction;
use Throwable;

use function array_keys;
use function array_values;
use function hash;

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

    /** @var list<callable> */
    public array $valueModifier = [];

    public Cache $cache;

    /** @var non-empty-list<non-empty-string> */
    public array $supportedDateFormats = self::DEFAULT_SUPPORTED_DATETIME_FORMATS;

    public bool $allowScalarValueCasting = false;

    public bool $allowNonSequentialList = false;

    public bool $allowUndefinedValues = false;

    public bool $allowSuperfluousKeys = false;

    public bool $allowPermissiveTypes = false;

    /** @var callable(Throwable): ErrorMessage */
    public $exceptionFilter;

    /** @var array<int, list<callable>> */
    public array $transformers = [];

    /** @var array<class-string, null> */
    public array $transformerAttributes = [];

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
            AsTransformer::class,
            Constructor::class,
            DynamicConstructor::class,
            ...array_keys($this->transformerAttributes),
        ];
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
            $this->transformerAttributes,
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
            ...$this->valueModifier,
            ...array_merge(...$this->transformers),
        ]);
    }
}
