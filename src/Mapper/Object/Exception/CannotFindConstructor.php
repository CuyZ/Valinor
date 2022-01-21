<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Exception;

use CuyZ\Valinor\Definition\MethodDefinition;
use CuyZ\Valinor\Mapper\Object\Factory\ObjectBuilderNotFound;
use CuyZ\Valinor\Mapper\Tree\Message\Message;
use RuntimeException;

use function array_keys;
use function count;
use function get_debug_type;
use function implode;
use function ksort;

/** @api */
final class CannotFindConstructor extends RuntimeException implements Message, ObjectBuilderNotFound
{
    /**
     * @param mixed $source
     * @param non-empty-list<MethodDefinition> $constructors
     */
    public function __construct($source, array $constructors)
    {
        $type = get_debug_type($source);

        $signatures = [];
        $sortedSignatures = [];

        foreach ($constructors as $method) {
            $parameters = [];

            foreach ($method->parameters() as $parameter) {
                $parameters[] = $parameter->isOptional()
                    ? "{$parameter->name()}?: {$parameter->type()}"
                    : "{$parameter->name()}: {$parameter->type()}";
            }

            $signatures[count($parameters)]['array{' . implode(', ', $parameters) . '}'] = true;
        }

        ksort($signatures);

        foreach ($signatures as $list) {
            foreach (array_keys($list) as $signature) {
                $sortedSignatures[] = $signature;
            }
        }

        parent::__construct(
            "Invalid value, got `$type` but expected one of `" . implode('`, `', $sortedSignatures) . '`.',
            1642183169
        );
    }
}
