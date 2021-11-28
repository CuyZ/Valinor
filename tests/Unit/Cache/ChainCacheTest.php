<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Cache;

use CuyZ\Valinor\Cache\ChainCache;
use CuyZ\Valinor\Tests\Fake\Cache\FakeCache;
use CuyZ\Valinor\Tests\Fake\Cache\FakeFailingCache;
use PHPUnit\Framework\TestCase;

use function iterator_to_array;

final class ChainCacheTest extends TestCase
{
    /** @var ChainCache<mixed> */
    private ChainCache $chain;

    private FakeCache $first;

    private FakeCache $second;

    private FakeCache $third;

    protected function setUp(): void
    {
        parent::setUp();

        $this->first = new FakeCache();
        $this->second = new FakeCache();
        $this->third = new FakeCache();
        $this->chain = new ChainCache($this->first, $this->second, $this->third);
    }

    public function test_chain_has_key_if_the_first_delegate_has_it(): void
    {
        $this->first->set('foo', 'bar');

        self::assertTrue($this->chain->has('foo'));
    }

    public function test_chain_has_key_if_a_middle_delegate_has_it(): void
    {
        $this->second->set('foo', 'bar');

        self::assertTrue($this->chain->has('foo'));
    }

    public function test_chain_has_key_if_the_last_delegate_has_it(): void
    {
        $this->third->set('foo', 'bar');

        self::assertTrue($this->chain->has('foo'));
    }

    public function test_returns_null_if_no_delegate_has_a_key(): void
    {
        self::assertFalse($this->chain->has('foo'));
        self::assertFalse($this->first->has('foo'));
        self::assertFalse($this->second->has('foo'));
        self::assertFalse($this->third->has('foo'));

        self::assertNull($this->chain->get('foo'));
        self::assertNull($this->first->get('foo'));
        self::assertNull($this->second->get('foo'));
        self::assertNull($this->third->get('foo'));
    }

    public function test_setting_a_value_in_the_chain_sets_it_in_all_delegates(): void
    {
        self::assertTrue($this->chain->set('foo', 'bar'));

        self::assertTrue($this->chain->has('foo'));
        self::assertTrue($this->first->has('foo'));
        self::assertTrue($this->second->has('foo'));
        self::assertTrue($this->third->has('foo'));

        self::assertSame('bar', $this->chain->get('foo'));
        self::assertSame('bar', $this->first->get('foo'));
        self::assertSame('bar', $this->second->get('foo'));
        self::assertSame('bar', $this->third->get('foo'));
    }

    public function test_getting_a_value_from_the_first_delegate_does_not_save_the_value_in_the_next_ones(): void
    {
        $this->first->set('foo', 'bar');

        $this->chain->get('foo');

        self::assertTrue($this->first->has('foo'));
        self::assertFalse($this->second->has('foo'));
        self::assertFalse($this->third->has('foo'));

        self::assertSame('bar', $this->first->get('foo'));
        self::assertNull($this->second->get('foo'));
        self::assertNull($this->third->get('foo'));
    }

    public function test_getting_a_value_from_a_middle_delegate_saves_it_in_delegates_above_it(): void
    {
        $this->second->set('foo', 'bar');

        $this->chain->get('foo');

        self::assertTrue($this->first->has('foo'));
        self::assertTrue($this->second->has('foo'));
        self::assertFalse($this->third->has('foo'));

        self::assertSame('bar', $this->first->get('foo'));
        self::assertSame('bar', $this->second->get('foo'));
        self::assertNull($this->third->get('foo'));
    }

    public function test_getting_a_value_from_the_last_delegate_saves_it_in_all_delegates(): void
    {
        $this->third->set('foo', 'bar');

        $this->chain->get('foo');

        self::assertTrue($this->first->has('foo'));
        self::assertTrue($this->second->has('foo'));
        self::assertTrue($this->third->has('foo'));

        self::assertSame('bar', $this->first->get('foo'));
        self::assertSame('bar', $this->second->get('foo'));
        self::assertSame('bar', $this->third->get('foo'));
    }

    public function test_removing_a_key_from_the_chain_removes_it_in_all_delegates(): void
    {
        $this->chain->set('foo', 'bar');

        self::assertTrue($this->chain->delete('foo'));

        self::assertFalse($this->chain->has('foo'));
        self::assertFalse($this->first->has('foo'));
        self::assertFalse($this->second->has('foo'));
        self::assertFalse($this->third->has('foo'));
    }

    public function test_removing_a_key_from_all_delegates_removes_it_from_the_chain(): void
    {
        $this->chain->set('foo', 'bar');

        $this->first->delete('foo');
        $this->second->delete('foo');
        $this->third->delete('foo');

        self::assertFalse($this->chain->has('foo'));
    }

    public function test_clearing_from_the_chain_clears_all_delegates(): void
    {
        $this->chain->set('foo', 'bar');
        $this->chain->set('fiz', 'buz');

        self::assertTrue($this->chain->clear());

        self::assertFalse($this->chain->has('foo'));
        self::assertFalse($this->chain->has('fiz'));

        self::assertFalse($this->first->has('foo'));
        self::assertFalse($this->first->has('fiz'));

        self::assertFalse($this->second->has('foo'));
        self::assertFalse($this->second->has('fiz'));

        self::assertFalse($this->third->has('foo'));
        self::assertFalse($this->third->has('fiz'));
    }

    public function test_clearing_all_delegates_clears_the_chain(): void
    {
        $this->chain->set('foo', 'bar');
        $this->chain->set('fiz', 'buz');

        $this->first->clear();
        $this->second->clear();
        $this->third->clear();

        self::assertFalse($this->chain->has('foo'));
        self::assertFalse($this->chain->has('fiz'));
    }

    public function test_setting_values_in_the_chain_sets_them_in_all_delegates(): void
    {
        $result = $this->chain->setMultiple([
            'a' => 'foo',
            'b' => 'bar',
        ]);

        self::assertTrue($result);

        self::assertTrue($this->chain->has('a'));
        self::assertTrue($this->chain->has('b'));

        self::assertTrue($this->first->has('a'));
        self::assertTrue($this->first->has('b'));

        self::assertTrue($this->second->has('a'));
        self::assertTrue($this->second->has('b'));

        self::assertTrue($this->third->has('a'));
        self::assertTrue($this->third->has('b'));

        self::assertSame('foo', $this->chain->get('a'));
        self::assertSame('bar', $this->chain->get('b'));

        self::assertSame('foo', $this->first->get('a'));
        self::assertSame('bar', $this->first->get('b'));

        self::assertSame('foo', $this->second->get('a'));
        self::assertSame('bar', $this->second->get('b'));

        self::assertSame('foo', $this->third->get('a'));
        self::assertSame('bar', $this->third->get('b'));
    }

    public function test_getting_values_from_the_first_delegate_does_not_save_the_values_in_the_next_ones(): void
    {
        $this->first->setMultiple([
            'a' => 'foo',
            'b' => 'bar',
        ]);

        $this->chain->get('a');
        $this->chain->get('b');

        self::assertTrue($this->first->has('a'));
        self::assertTrue($this->first->has('b'));

        self::assertFalse($this->second->has('a'));
        self::assertFalse($this->second->has('b'));

        self::assertFalse($this->third->has('a'));
        self::assertFalse($this->third->has('b'));

        self::assertSame('foo', $this->first->get('a'));
        self::assertSame('bar', $this->first->get('b'));

        self::assertNull($this->second->get('a'));
        self::assertNull($this->second->get('b'));

        self::assertNull($this->third->get('a'));
        self::assertNull($this->third->get('b'));
    }

    public function test_getting_values_from_a_middle_delegate_saves_them_in_delegates_above_it(): void
    {
        $this->second->setMultiple([
            'a' => 'foo',
            'b' => 'bar',
        ]);

        $this->chain->get('a');
        $this->chain->get('b');

        self::assertTrue($this->first->has('a'));
        self::assertTrue($this->first->has('b'));

        self::assertTrue($this->second->has('a'));
        self::assertTrue($this->second->has('b'));

        self::assertFalse($this->third->has('a'));
        self::assertFalse($this->third->has('b'));

        self::assertSame('foo', $this->first->get('a'));
        self::assertSame('bar', $this->first->get('b'));

        self::assertSame('foo', $this->second->get('a'));
        self::assertSame('bar', $this->second->get('b'));

        self::assertNull($this->third->get('a'));
        self::assertNull($this->third->get('b'));
    }

    public function test_getting_values_from_the_last_delegate_saves_them_in_all_delegates(): void
    {
        $this->third->setMultiple([
            'a' => 'foo',
            'b' => 'bar',
        ]);

        $this->chain->get('a');
        $this->chain->get('b');

        self::assertTrue($this->first->has('a'));
        self::assertTrue($this->first->has('b'));

        self::assertTrue($this->second->has('a'));
        self::assertTrue($this->second->has('b'));

        self::assertTrue($this->third->has('a'));
        self::assertTrue($this->third->has('b'));

        self::assertSame('foo', $this->first->get('a'));
        self::assertSame('bar', $this->first->get('b'));

        self::assertSame('foo', $this->second->get('a'));
        self::assertSame('bar', $this->second->get('b'));

        self::assertSame('foo', $this->third->get('a'));
        self::assertSame('bar', $this->third->get('b'));
    }

    public function test_get_multiple_returns_values_from_delegates(): void
    {
        $this->first->set('a', 'foo');
        $this->second->set('b', 'bar');
        $this->third->set('c', 'baz');

        $result = iterator_to_array($this->chain->getMultiple(['b', 'a', 'c']));

        self::assertSame('foo', $result['a']);
        self::assertSame('bar', $result['b']);
        self::assertSame('baz', $result['c']);
    }

    public function test_delete_multiple_deletes_values_in_delegates(): void
    {
        $this->first->set('a', 'foo');
        $this->second->set('b', 'bar');
        $this->third->set('c', 'baz');

        self::assertTrue($this->chain->deleteMultiple(['a', 'b', 'c']));
    }

    public function test_set_returns_false_if_one_delegate_fails_to_save(): void
    {
        $cache = new ChainCache(new FakeCache(), new FakeFailingCache(), new FakeCache());

        self::assertFalse($cache->set('foo', 'bar'));
    }

    public function test_delete_returns_false_if_one_delegate_fails_to_save(): void
    {
        $cache = new ChainCache(new FakeCache(), new FakeFailingCache(), new FakeCache());

        self::assertFalse($cache->delete('foo'));
    }

    public function test_clear_returns_false_if_one_delegate_fails_to_save(): void
    {
        $cache = new ChainCache(new FakeCache(), new FakeFailingCache(), new FakeCache());

        self::assertFalse($cache->clear());
    }

    public function test_set_multiple_returns_false_if_one_delegate_fails_to_save(): void
    {
        $cache = new ChainCache(new FakeCache(), new FakeFailingCache(), new FakeCache());

        self::assertFalse($cache->setMultiple(['foo' => 'bar']));
    }

    public function test_delete_multiple_returns_false_if_one_delegate_fails_to_save(): void
    {
        $cache = new ChainCache(new FakeCache(), new FakeFailingCache(), new FakeCache());

        self::assertFalse($cache->deleteMultiple(['foo']));
    }
}
