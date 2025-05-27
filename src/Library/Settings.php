<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Library;

use Closure;
use CuyZ\Valinor\Mapper\Object\Constructor;
use CuyZ\Valinor\Mapper\Object\DynamicConstructor;
use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Normalizer\AsTransformer;
use DateTimeImmutable;
use DateTimeInterface;
use Psr\SimpleCache\CacheInterface;
use ReflectionFunction;
use Throwable;

use function array_keys;
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

    /** @var CacheInterface<mixed> */
    public CacheInterface $cache;

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
            implode('', array_map($this->callableSignature(...), $this->inferredMapping)),
            $this->nativeConstructors,
            implode('', array_map($this->callableSignature(...), $this->customConstructors)),
            implode('', array_map($this->callableSignature(...), $this->valueModifier)),
            $this->supportedDateFormats,
            $this->allowScalarValueCasting,
            $this->allowNonSequentialList,
            $this->allowUndefinedValues,
            $this->allowSuperfluousKeys,
            $this->allowPermissiveTypes,
            $this->callableSignature($this->exceptionFilter),
            array_map(
                fn (array $transformers) => implode('', array_map($this->callableSignature(...), $transformers)),
                $this->transformers,
            ),
            $this->transformerAttributes,
        ]));
    }

    private function callableSignature(callable $callable): string
    {
        $reflection = new ReflectionFunction(Closure::fromCallable($callable));

        return ($reflection->getClosureCalledClass()->name ?? $reflection->getFileName()) . $reflection->getStartLine() . $reflection->getEndLine();
    }
}
