<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Cache;

use CuyZ\Valinor\Cache\Exception\InvalidSignatureToWarmup;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Tests\Fake\Cache\FakeCache;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use DateTimeInterface;

final class CacheWarmupTest extends IntegrationTestCase
{
    private FakeCache $cache;

    private MapperBuilder $mapper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cache = new FakeCache();
        $this->mapper = $this->mapperBuilder()->withCache($this->cache);
    }

    public function test_will_warmup_type_parser_cache_for_object_with_properties(): void
    {
        $this->mapper->warmupCacheFor(ObjectToWarmupWithProperties::class);
        $this->mapper->warmupCacheFor(ObjectToWarmupWithProperties::class, SomeObjectJ::class);

        self::assertSame(10, $this->cache->countEntries());
        self::assertSame(10, $this->cache->timeSetWasCalled());
    }

    public function test_will_warmup_type_parser_cache_for_object_with_constructor(): void
    {
        $mapper = $this->mapper->registerConstructor(
            ObjectToWarmupWithConstructors::constructorA(...),
            ObjectToWarmupWithConstructors::constructorB(...),
        );

        $mapper->warmupCacheFor(ObjectToWarmupWithConstructors::class);
        $mapper->warmupCacheFor(ObjectToWarmupWithConstructors::class, SomeObjectC::class);

        self::assertSame(6, $this->cache->countEntries());
        self::assertSame(6, $this->cache->timeSetWasCalled());
    }

    public function test_will_warmup_type_parser_cache_for_interface(): void
    {
        $mapper = $this->mapper
            ->infer(
                SomeInterface::class,
                /** @return class-string<ObjectToWarmupWithProperties|ObjectToWarmupWithConstructors> */
                fn (string $foo, SomeObjectI $objectI) => $foo === 'foo' ? ObjectToWarmupWithProperties::class : ObjectToWarmupWithConstructors::class
            );

        $mapper->warmupCacheFor(SomeInterface::class);
        $mapper->warmupCacheFor(SomeInterface::class, SomeObjectJ::class);

        self::assertSame(13, $this->cache->countEntries());
        self::assertSame(13, $this->cache->timeSetWasCalled());
    }

    public function test_warmup_invalid_signature_throws_exception(): void
    {
        $this->expectException(InvalidSignatureToWarmup::class);
        $this->expectExceptionCode(1653330261);
        $this->expectExceptionMessage('Cannot warm up invalid signature `SomeInvalidClass`: Cannot parse unknown symbol `SomeInvalidClass`.');

        $this->mapper->warmupCacheFor('SomeInvalidClass');
    }
}

interface SomeInterface {}

final class ObjectToWarmupWithProperties implements SomeInterface
{
    public string $string;

    public SomeObjectA $objectA;

    /** @var array<string> */
    public array $arrayOfStrings;

    /** @var array<SomeObjectB> */
    public array $arrayOfObjects;

    /** @var list<SomeObjectC> */
    public array $listOfObjects;

    /** @var iterable<SomeObjectD> */
    public iterable $iterableOfObjects;

    /** @var array{foo: string, object: SomeObjectE} */
    public array $shapedArrayContainingObject;

    public string|SomeObjectF $unionContainingObject;

    /** @var SomeObjectG&DateTimeInterface */
    public object $intersectionOfObjects;

    public static function someUnnecessaryMethod(string $string, SomeObjectH $object): SomeObjectI
    {
        return new SomeObjectI();
    }
}

final class ObjectToWarmupWithConstructors implements SomeInterface
{
    public static function constructorA(string $string, SomeObjectA $objectA): self
    {
        return new self();
    }

    public static function constructorB(string $string, SomeObjectB $objectb): self
    {
        return new self();
    }
}

class SomeObjectA {}

class SomeObjectB {}

class SomeObjectC {}

class SomeObjectD {}

class SomeObjectE {}

class SomeObjectF {}

class SomeObjectG {}

class SomeObjectH {}

class SomeObjectI {}

class SomeObjectJ {}
