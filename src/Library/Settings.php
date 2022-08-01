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
    /** @var array<interface-string, callable> */
    public array $interfaceMapping = [];

    /** @var array<class-string, null> */
    public array $nativeConstructors = [];

    /** @var list<callable> */
    public array $customConstructors = [];

    /** @var list<callable> */
    public array $valueModifier = [];

    /** @var CacheInterface<mixed> */
    public CacheInterface $cache;

    public bool $flexible = false;

    /** @var callable(Throwable): ErrorMessage */
    public $exceptionFilter;

    public bool $enableLegacyDoctrineAnnotations = PHP_VERSION_ID < 8_00_00;

    /**
     * @var non-empty-list<non-empty-string>|null
     */
    public ?array $dateTimeFormats = null;

    public function __construct()
    {
        $this->interfaceMapping[DateTimeInterface::class] = static fn () => DateTimeImmutable::class;
        $this->exceptionFilter = function (Throwable $exception) {
            // @PHP8.0 use throw exception expression in short closure
            throw $exception;
        };
    }
}
