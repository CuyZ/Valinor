<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\HasCode;
use CuyZ\Valinor\Mapper\Tree\Message\HasParameters;

use function array_filter;
use function array_keys;
use function implode;
use function in_array;

/** @internal */
final class UnexpectedKeysInSource implements ErrorMessage, HasCode, HasParameters
{
    private string $body = 'Unexpected key(s) {keys}, expected {expected_keys}.';

    private string $code = 'unexpected_keys';

    /** @var array<string, string> */
    private array $parameters;

    /**
     * @param array<mixed> $value
     * @param non-empty-list<int|string> $children
     */
    public function __construct(array $value, array $children)
    {
        $superfluous = array_filter(
            array_keys($value),
            fn (string $key) => ! in_array($key, $children, true),
        );

        $this->parameters = [
            'keys' => '`' . implode('`, `', $superfluous) . '`',
            'expected_keys' => '`' . implode('`, `', $children) . '`',
        ];
    }

    public function body(): string
    {
        return $this->body;
    }

    public function code(): string
    {
        return $this->code;
    }

    public function parameters(): array
    {
        return $this->parameters;
    }
}
