<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Cache;

use CuyZ\Valinor\Cache\RuntimeCache;
use PHPUnit\Framework\TestCase;
use stdClass;

final class RuntimeCacheTest extends TestCase
{
    /** @var RuntimeCache<mixed> */
    private RuntimeCache $cache;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cache = new RuntimeCache();
    }

    public function test_value_can_be_fetched_and_deleted(): void
    {
        $key = 'foo';
        $value = new stdClass();

        self::assertFalse($this->cache->has($key));
        self::assertTrue($this->cache->set($key, $value));
        self::assertTrue($this->cache->has($key));
        self::assertSame($value, $this->cache->get($key));
        self::assertTrue($this->cache->delete($key));
        self::assertFalse($this->cache->has($key));
    }

    public function test_get_non_existing_entry_returns_default_value(): void
    {
        $defaultValue = new stdClass();

        self::assertSame($defaultValue, $this->cache->get('Schwifty', $defaultValue));
    }

    public function test_get_existing_entry_does_not_return_default_value(): void
    {
        $this->cache->set('foo', 'foo');

        self::assertSame('foo', $this->cache->get('foo', 'bar'));
    }

    public function test_clear_entries_clears_everything(): void
    {
        $keyA = 'foo';
        $keyB = 'bar';

        $this->cache->set($keyA, new stdClass());
        $this->cache->set($keyB, new stdClass());

        self::assertTrue($this->cache->has($keyA));
        self::assertTrue($this->cache->has($keyB));
        self::assertTrue($this->cache->clear());
        self::assertFalse($this->cache->has($keyA));
        self::assertFalse($this->cache->has($keyB));
    }

    public function test_multiple_values_set_can_be_fetched_and_deleted(): void
    {
        $values = [
            'foo' => new stdClass(),
            'bar' => new stdClass(),
        ];

        self::assertFalse($this->cache->has('foo'));
        self::assertFalse($this->cache->has('bar'));

        self::assertTrue($this->cache->setMultiple($values));

        self::assertTrue($this->cache->has('foo'));
        self::assertTrue($this->cache->has('bar'));

        self::assertSame($values, $this->cache->getMultiple(['foo', 'bar']));

        self::assertTrue($this->cache->deleteMultiple(['foo', 'bar']));

        self::assertFalse($this->cache->has('foo'));
        self::assertFalse($this->cache->has('bar'));
    }
}
