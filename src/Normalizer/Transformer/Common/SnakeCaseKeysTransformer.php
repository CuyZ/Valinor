<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Common;

use function is_array;
use function lcfirst;
use function preg_replace;
use function strtolower;

/** @api */
final class SnakeCaseKeysTransformer
{
    public function __invoke(object $object, callable $next): mixed
    {
        $result = $next();

        if (! is_array($result)) {
            return $result;
        }

        $snakeCased = [];

        foreach ($result as $key => $value) {
            $lcFirstKey = preg_replace('/[A-Z]/', '_$0', lcfirst($key));
            $newKey = strtolower($lcFirstKey ?? $key);

            $snakeCased[$newKey] = $value;
        }

        return $snakeCased;
    }
}
