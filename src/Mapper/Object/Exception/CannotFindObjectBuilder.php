<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Exception;

use CuyZ\Valinor\Mapper\Object\Factory\SuitableObjectBuilderNotFound;
use CuyZ\Valinor\Mapper\Object\ObjectBuilder;
use CuyZ\Valinor\Mapper\Tree\Message\Message;
use CuyZ\Valinor\Utility\ValueDumper;
use RuntimeException;

use function array_keys;
use function count;
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
        $value = ValueDumper::dump($source);

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

        parent::__construct(
            "Value $value does not match any of `" . implode('`, `', $sortedSignatures) . '`.',
            1642183169
        );
    }
}
