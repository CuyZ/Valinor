<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Cache;

use CuyZ\Valinor\Cache\Compiled\CompiledPhpFileCache;
use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Definition\FunctionDefinition;
use CuyZ\Valinor\Definition\Repository\Cache\Compiler\ClassDefinitionCompiler;
use CuyZ\Valinor\Definition\Repository\Cache\Compiler\FunctionDefinitionCompiler;
use Psr\SimpleCache\CacheInterface;
use Traversable;

use function current;
use function get_class;
use function next;
use function sys_get_temp_dir;

/**
 * @api
 *
 * @implements CacheInterface<ClassDefinition|FunctionDefinition>
 */
final class FileSystemCache implements CacheInterface
{
    /** @var array<string, CacheInterface<ClassDefinition|FunctionDefinition>> */
    private array $delegates;

    public function __construct(string $cacheDir = null)
    {
        $cacheDir ??= sys_get_temp_dir();

        // @infection-ignore-all
        $this->delegates = [
            ClassDefinition::class => new CompiledPhpFileCache($cacheDir . DIRECTORY_SEPARATOR . 'classes', new ClassDefinitionCompiler()),
            FunctionDefinition::class => new CompiledPhpFileCache($cacheDir . DIRECTORY_SEPARATOR . 'functions', new FunctionDefinitionCompiler()),
        ];
    }

    public function has($key): bool
    {
        foreach ($this->delegates as $delegate) {
            if ($delegate->has($key)) {
                return true;
            }
        }

        return false;
    }

    public function get($key, $default = null)
    {
        while ($delegate = current($this->delegates)) {
            if ($delegate->has($key)) {
                return $delegate->get($key, $default);
            }

            // @infection-ignore-all
            next($this->delegates);
        }

        return $default;
    }

    public function set($key, $value, $ttl = null): bool
    {
        return $this->delegates[get_class($value)]->set($key, $value, $ttl);
    }

    public function delete($key): bool
    {
        $deleted = true;

        foreach ($this->delegates as $delegate) {
            $deleted = $delegate->delete($key) && $deleted;
        }

        return $deleted;
    }

    public function clear(): bool
    {
        $cleared = true;

        foreach ($this->delegates as $delegate) {
            $cleared = $delegate->clear() && $cleared;
        }

        return $cleared;
    }

    /**
     * @return Traversable<string, ClassDefinition|FunctionDefinition|null>
     */
    public function getMultiple($keys, $default = null): Traversable
    {
        foreach ($keys as $key) {
            yield $key => $this->get($key, $default);
        }
    }

    public function setMultiple($values, $ttl = null): bool
    {
        $set = true;

        foreach ($values as $key => $value) {
            $set = $this->set($key, $value, $ttl) && $set;
        }

        return $set;
    }

    public function deleteMultiple($keys): bool
    {
        $deleted = true;

        foreach ($keys as $key) {
            $deleted = $this->delete($key) && $deleted;
        }

        return $deleted;
    }
}
