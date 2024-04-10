<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Formatter;

final class JsonFormatter implements Formatter
{
    public function __construct(
        private int $jsonEncodingOptions,
    ) {}

    public function format(mixed $value): string
    {
        /** @var resource $resource */
        $resource = fopen('php://memory', 'w');

        (new JsonStreamFormatter($resource, $this->jsonEncodingOptions))->format($value);

        rewind($resource);

        /** @var string */
        $json = stream_get_contents($resource);

        fclose($resource);

        return $json;
    }
}
