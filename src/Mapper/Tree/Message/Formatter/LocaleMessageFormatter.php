<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Message\Formatter;

use CuyZ\Valinor\Mapper\Tree\Message\NodeMessage;

/** @api */
final class LocaleMessageFormatter implements MessageFormatter
{
    private string $locale;

    public function __construct(string $locale)
    {
        $this->locale = $locale;
    }

    public function format(NodeMessage $message): NodeMessage
    {
        return $message->withLocale($this->locale);
    }
}
