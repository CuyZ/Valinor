<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Source\Exception;

use RuntimeException;

/**
 * @internal
 *
 * @codeCoverageIgnore
 * @infection-ignore-all
 */
final class JsonExtensionNotEnabled extends RuntimeException implements SourceException
{
    public function __construct()
    {
        parent::__construct(
            "The PHP JSON extension is not enabled.",
            1629990932
        );
    }
}
