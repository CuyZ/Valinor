<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Source\Exception;

use LogicException;

/**
 * @internal
 *
 * @codeCoverageIgnore
 * @infection-ignore-all
 */
final class YamlExtensionNotEnabled extends LogicException
{
    public function __construct()
    {
        parent::__construct("The PHP YAML extension is not enabled.");
    }
}
