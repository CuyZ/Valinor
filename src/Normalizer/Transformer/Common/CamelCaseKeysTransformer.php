<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Common;

use function is_array;
use function lcfirst;
use function str_replace;
use function ucwords;

/**  @internal */
final readonly class CamelCaseKeysTransformer
{
    public function __invoke(object $object, callable $next): mixed
    {
        $result = $next();

        if (! is_array($result)) {
            return $result;
        }

        $snakeCased = [];

        foreach ($result as $key => $value) {
            $newKey = lcfirst(str_replace([' ', '_', '-'], '', ucwords($key, ' _-')));

            $snakeCased[$newKey] = $value;
        }

        return $snakeCased;
    }
}
