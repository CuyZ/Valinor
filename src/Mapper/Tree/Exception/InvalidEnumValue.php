<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use BackedEnum;
use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\HasParameters;
use CuyZ\Valinor\Utility\String\StringFormatter;
use CuyZ\Valinor\Utility\ValueDumper;
use RuntimeException;
use UnitEnum;

use function array_map;
use function implode;

/** @internal */
final class InvalidEnumValue extends RuntimeException implements ErrorMessage, HasParameters
{
    private string $body = 'Value {value} does not match any of {allowed_values}.';

    /** @var array<string, string> */
    private array $parameters;

    /**
     * @param class-string<UnitEnum> $enumName
     * @param mixed $value
     */
    public function __construct(string $enumName, $value)
    {
        $this->parameters = [
            'value' => ValueDumper::dump($value),
            'allowed_values' => (function () use ($enumName) {
                $values = array_map(
                    fn (UnitEnum $case) => ValueDumper::dump($case instanceof BackedEnum ? $case->value : $case->name),
                    $enumName::cases()
                );

                return implode(', ', $values);
            })(),
        ];

        parent::__construct(StringFormatter::for($this), 1633093113);
    }

    public function body(): string
    {
        return $this->body;
    }

    public function parameters(): array
    {
        return $this->parameters;
    }
}
