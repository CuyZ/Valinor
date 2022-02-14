<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Exception;

use CuyZ\Valinor\Mapper\Object\Factory\SuitableObjectBuilderNotFound;
use CuyZ\Valinor\Mapper\Object\ObjectBuilder;
use CuyZ\Valinor\Mapper\Tree\Message\Message;
use RuntimeException;

use function array_keys;
use function count;
use function get_debug_type;
use function implode;
use function ksort;

/** @api */
final class CannotFindObjectBuilder extends RuntimeException implements Message, SuitableObjectBuilderNotFound
{
    /**
     * @param mixed $source
     * @param non-empty-list<ObjectBuilder> $builders
     */
    public function __construct($source, array $builders)
    {
        $type = get_debug_type($source);

        $signatures = [];
        $sortedSignatures = [];

        foreach ($builders as $builder) {
            $parameters = [];

            foreach ($builder->describeArguments() as $argument) {
                $parameters[] = $argument->isRequired()
                    ? "{$argument->name()}: {$argument->type()}"
                    : "{$argument->name()}?: {$argument->type()}";
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
