<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Constant;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use RuntimeException;

use function str_contains;

/** @internal */
final class ClassConstantCaseNotFound extends RuntimeException implements InvalidType
{
    /**
     * @param class-string $className
     */
    public function __construct(string $className, string $case)
    {
        $message = str_contains($case, '*')
            ? "Cannot find class constant case with pattern `$className::$case`."
            : "Unknown class constant case `$className::$case`.";

        parent::__construct($message);
    }
}
