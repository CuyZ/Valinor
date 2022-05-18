<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Exception;

use CuyZ\Valinor\Mapper\Object\Factory\SuitableObjectBuilderNotFound;
use CuyZ\Valinor\Mapper\Object\ObjectBuilder;
use CuyZ\Valinor\Mapper\Tree\Message\TranslatableMessage;
use CuyZ\Valinor\Utility\String\StringFormatter;
use CuyZ\Valinor\Utility\ValueDumper;
use RuntimeException;

use function array_keys;
use function count;
use function ksort;

/** @api */
final class CannotFindObjectBuilder extends RuntimeException implements TranslatableMessage, SuitableObjectBuilderNotFound
{
    private string $body = 'Value {value} does not match any of {allowed_types}.';

    /** @var array<string, string> */
    private array $parameters;

    /**
     * @param mixed $source
     * @param non-empty-list<ObjectBuilder> $builders
     */
    public function __construct($source, array $builders)
    {
        $this->parameters = [
            'value' => ValueDumper::dump($source),
            'allowed_types' => (function () use ($builders) {
                $signatures = [];
                $sortedSignatures = [];

                foreach ($builders as $builder) {
                    $arguments = $builder->describeArguments();
                    $count = count($arguments);
                    $signature = $arguments->signature();

                    $signatures[$count][$signature] = null;
                }

                ksort($signatures);

                foreach ($signatures as $list) {
                    foreach (array_keys($list) as $signature) {
                        $sortedSignatures[] = $signature;
                    }
                }

                return implode(', ', $sortedSignatures);
            })(),
        ];

        parent::__construct(StringFormatter::for($this), 1642183169);
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
