<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object;

use CuyZ\Valinor\Mapper\Object\Exception\CannotParseToDateTime;
use CuyZ\Valinor\Type\Types\NonEmptyStringType;
use CuyZ\Valinor\Type\Types\NullType;
use CuyZ\Valinor\Type\Types\PositiveIntegerType;
use CuyZ\Valinor\Type\Types\UnionType;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;

use function assert;
use function is_array;
use function is_int;
use function is_null;
use function is_string;

final class DateTimeObjectBuilder implements ObjectBuilder
{
    /** @var class-string<DateTime|DateTimeImmutable> */
    private string $className;

    /**
     * @param class-string<DateTime|DateTimeImmutable> $className
     */
    public function __construct(string $className)
    {
        $this->className = $className;
    }

    public function describeArguments($source): iterable
    {
        $datetime = $source;
        $format = null;

        if (is_array($source)) {
            $datetime = $source['datetime'] ?? null;
            $format = $source['format'] ?? null;
        }

        yield new Argument('datetime', new UnionType(PositiveIntegerType::get(), NonEmptyStringType::get()), $datetime);
        yield new Argument('format', new UnionType(NullType::get(), NonEmptyStringType::get()), $format);
    }

    public function build(array $arguments): DateTimeInterface
    {
        $datetime = $arguments['datetime'];
        $format = $arguments['format'];

        assert(is_string($datetime) || is_int($datetime));
        assert(is_string($format) || is_null($format));

        if ($format) {
            $date = $this->tryFormat((string)$datetime, $format);
        } elseif (is_int($datetime)) {
            $date = $this->tryFormat((string)$datetime, 'U');
        } else {
            $date = $this->tryAllFormats($datetime);
        }

        if (! $date) {
            // @PHP8.0 use throw exception expression
            throw new CannotParseToDateTime((string)$datetime, $this->className);
        }

        return $date;
    }

    private function tryAllFormats(string $value): ?DateTimeInterface
    {
        $formats = [
            DATE_ATOM, DATE_RFC850, DATE_COOKIE, DATE_RFC822, DATE_RFC1036,
            DATE_RFC1123, DATE_RFC2822, DATE_RFC3339, DATE_RFC3339_EXTENDED,
            DATE_RFC7231, DATE_RSS, DATE_W3C,
        ];

        foreach ($formats as $format) {
            $date = $this->tryFormat($value, $format);

            if ($date instanceof DateTimeInterface) {
                return $date;
            }
        }

        return null;
    }

    private function tryFormat(string $value, string $format): ?DateTimeInterface
    {
        return ($this->className)::createFromFormat($format, $value) ?: null;
    }
}
