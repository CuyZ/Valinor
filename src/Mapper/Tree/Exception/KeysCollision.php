<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\HasCode;
use CuyZ\Valinor\Mapper\Tree\Message\HasParameters;

/** @internal */
final class KeysCollision implements ErrorMessage, HasCode, HasParameters
{
    public function __construct(
        private string $key,
        private string $duplicateKey,
    ) {}

    public function body(): string
    {
        return 'Collision between keys `{key}` and `{duplicate_key}`.';
    }

    public function code(): string
    {
        return 'keys_collision';
    }

    public function parameters(): array
    {
        return [
            'key' => $this->key,
            'duplicate_key' => $this->duplicateKey,
        ];
    }
}
