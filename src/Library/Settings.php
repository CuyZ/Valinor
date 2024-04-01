<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Library;

use Closure;
use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use DateTimeImmutable;
use DateTimeInterface;
use Psr\SimpleCache\CacheInterface;
use ReflectionFunction;
use Throwable;

use function array_map;
use function implode;
use function serialize;
use function sha1;

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

    /**
     * Returns a unique hash, based on all the settings values that were set in
     * this instance.
     */
    public function hash(): string
    {
        return sha1(serialize([
            implode('', array_map($this->callableSignature(...), $this->inferredMapping)),
            $this->nativeConstructors,
            implode('', array_map($this->callableSignature(...), $this->customConstructors)),
            implode('', array_map($this->callableSignature(...), $this->valueModifier)),
            $this->supportedDateFormats,
            $this->enableFlexibleCasting,
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

        return $reflection->getFileName() . $reflection->getStartLine() . $reflection->getEndLine();
    }
}
