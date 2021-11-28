<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Template;

use LogicException;

final class DuplicatedTemplateName extends LogicException implements InvalidTemplate
{
    public function __construct(string $template)
    {
        parent::__construct(
            "The template `$template` was defined at least twice.",
            1604612898
        );
    }
}
