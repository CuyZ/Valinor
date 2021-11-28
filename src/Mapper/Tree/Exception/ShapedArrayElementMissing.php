<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\Message;
use CuyZ\Valinor\Type\Types\ShapedArrayElement;
use RuntimeException;

final class ShapedArrayElementMissing extends RuntimeException implements Message
{
    public function __construct(ShapedArrayElement $element)
    {
        parent::__construct(
            "Missing value `{$element->key()}` of type `{$element->type()}`.",
            1631613641
        );
    }
}
