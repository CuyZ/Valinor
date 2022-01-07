<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Utility\Reflection;

use CuyZ\Valinor\Utility\IsSingleton;
use CuyZ\Valinor\Utility\Singleton;
use ReflectionClass;

use function array_shift;
use function explode;
use function implode;
use function strtolower;

/** @internal */
final class ClassAliasParser
{
    use IsSingleton;

    /** @var array<class-string, array<string, class-string>> */
    private array $aliases = [];

    /**
     * If the given symbol was imported as an alias in the given class, the
     * original value is returned.
     *
     * @param ReflectionClass<object> $reflection
     */
    public function resolveAlias(string $symbol, ReflectionClass $reflection): string
    {
        $alias = $symbol;

        $namespaceParts = explode('\\', $symbol);
        $lastPart = array_shift($namespaceParts);

        if ($lastPart) {
            $alias = strtolower($lastPart);
        }

        $aliases = $this->aliases($reflection);

        if (! isset($aliases[$alias])) {
            return $symbol;
        }

        $full = $aliases[$alias];

        if (! empty($namespaceParts)) {
            $full .= '\\' . implode('\\', $namespaceParts);
        }

        return $full;
    }

    /**
     * @param ReflectionClass<object> $reflection
     * @return array<string, class-string>
     */
    private function aliases(ReflectionClass $reflection): array
    {
        /** @infection-ignore-all */
        return $this->aliases[$reflection->name] ??= Singleton::phpParser()->parseClass($reflection);
    }
}
