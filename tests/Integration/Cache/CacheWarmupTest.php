<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Cache;

use CuyZ\Valinor\Cache\Exception\InvalidSignatureToWarmup;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Tests\Fake\Cache\FakeCache;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;
use DateTimeInterface;

final class CacheWarmupTest extends IntegrationTest
{
    private FakeCache $cache;

    private MapperBuilder $mapper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cache = new FakeCache();
        $this->mapper = (new MapperBuilder())->withCache($this->cache);
    }

    public function test_will_warmup_type_parser_cache(): void
    {
        $this->mapper->warmup(ObjectToWarmup::class);
        $this->mapper->warmup(ObjectToWarmup::class, SomeObjectJ::class);

        self::assertSame(11, $this->cache->countEntries());
        self::assertSame(11, $this->cache->timeSetWasCalled());
    }

    public function test_warmup_invalid_signature_throws_exception(): void
    {
        $this->expectException(InvalidSignatureToWarmup::class);
        $this->expectExceptionCode(1653330261);
        $this->expectExceptionMessage('Cannot warm up invalid signature `SomeInvalidClass`: Cannot parse unknown symbol `SomeInvalidClass`.');

        $this->mapper->warmup('SomeInvalidClass');
    }
}

final class ObjectToWarmup
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

    /** @var string|SomeObjectF */
    public $unionContainingObject; // @PHP8.0 Native union

    /** @var SomeObjectG&DateTimeInterface */
    public object $intersectionOfObjects;

    public static function someMethod(string $string, SomeObjectH $object): SomeObjectI
    {
        return new SomeObjectI();
    }
}

class SomeObjectA
{
}

class SomeObjectB
{
}

class SomeObjectC
{
}

class SomeObjectD
{
}

class SomeObjectE
{
}

class SomeObjectF
{
}

class SomeObjectG
{
}

class SomeObjectH
{
}

class SomeObjectI
{
}

class SomeObjectJ
{
}
