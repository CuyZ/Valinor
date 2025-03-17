<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Cache;

use CuyZ\Valinor\Cache\KeySanitizerCache;
use CuyZ\Valinor\Library\Settings;
use CuyZ\Valinor\Tests\Fake\Cache\FakeCache;
use PHPUnit\Framework\TestCase;

use function iterator_to_array;

final class KeySanitizerCacheTest extends TestCase
{
    private FakeCache $delegate;

    /** @var KeySanitizerCache<mixed> */
    private KeySanitizerCache $cache;

    protected function setUp(): void
    {
        parent::setUp();

        $this->delegate = new FakeCache();
        $this->cache = new KeySanitizerCache($this->delegate, new Settings());
    }

    public function test_set_value_sets_value_in_delegate_with_changed_key(): void
    {
        $this->cache->set('foo', 'foo');

        self::assertTrue($this->cache->has('foo'));
        self::assertFalse($this->delegate->has('foo'));

        self::assertSame('foo', $this->cache->get('foo'));
    }

    public function test_delete_entry_deletes_entry(): void
    {
        $this->cache->set('foo', 'foo');
        $this->cache->delete('foo');

        self::assertFalse($this->cache->has('foo'));
    }

    public function test_clear_entries_clears_everything(): void
    {
        $this->cache->set('foo', 'foo');
        $this->cache->clear();

        self::assertFalse($this->cache->has('foo'));
    }

    public function test_setting_values_sets_values_in_delegate_with_changed_key(): void
    {
        $values = [
            'foo' => 'foo',
            'bar' => 'bar',
        ];

        $this->cache->setMultiple($values);

        self::assertTrue($this->cache->has('foo'));
        self::assertTrue($this->cache->has('bar'));

        self::assertFalse($this->delegate->has('foo'));
        self::assertFalse($this->delegate->has('bar'));

        self::assertSame($values, iterator_to_array($this->cache->getMultiple(['foo', 'bar'])));
    }

    public function test_delete_entries_deletes_correct_entries(): void
    {
        $this->cache->setMultiple([
            'foo' => 'foo',
            'bar' => 'bar',
            'baz' => 'baz',
        ]);

        $this->cache->deleteMultiple(['foo', 'baz']);

        self::assertFalse($this->cache->has('foo'));
        self::assertTrue($this->cache->has('bar'));
        self::assertFalse($this->cache->has('baz'));
    }
}
