<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object;

use CuyZ\Valinor\Mapper\Object\Exception\CannotParseToDateTime;
use CuyZ\Valinor\Type\Types\NonEmptyStringType;
use CuyZ\Valinor\Type\Types\NullType;
use CuyZ\Valinor\Type\Types\PositiveIntegerType;
use CuyZ\Valinor\Type\Types\ShapedArrayElement;
use CuyZ\Valinor\Type\Types\ShapedArrayType;
use CuyZ\Valinor\Type\Types\StringValueType;
use CuyZ\Valinor\Type\Types\UnionType;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;

use function assert;
use function is_array;
use function is_int;
use function is_null;
use function is_string;

/** @internal */
final class DateTimeObjectBuilder implements ObjectBuilder
{
    public const DATE_MYSQL = 'Y-m-d H:i:s';
    public const DATE_PGSQL = 'Y-m-d H:i:s.u';
    public const DATE_WITHOUT_TIME = '!Y-m-d';

    /** @var class-string<DateTime|DateTimeImmutable> */
    private string $className;

    private Arguments $arguments;

    /**
     * @param class-string<DateTime|DateTimeImmutable> $className
     */
    public function __construct(string $className)
    {
        $this->className = $className;
    }

    public function describeArguments(): Arguments
    {
        return $this->arguments ??= new Arguments(
            Argument::required('value', new UnionType(
                new UnionType(PositiveIntegerType::get(), NonEmptyStringType::get()),
                new ShapedArrayType(
                    new ShapedArrayElement(
                        new StringValueType('datetime'),
                        new UnionType(PositiveIntegerType::get(), NonEmptyStringType::get())
                    ),
                    new ShapedArrayElement(
                        new StringValueType('format'),
                        new UnionType(NullType::get(), NonEmptyStringType::get()),
                        true
                    ),
                )
            ))
        );
    }

    public function build(array $arguments): DateTimeInterface
    {
        $datetime = $arguments['value'];
        $format = null;

        if (is_array($datetime)) {
            $format = $datetime['format'] ?? null;
            $datetime = $datetime['datetime'];
        }

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
            throw new CannotParseToDateTime($datetime);
        }

        return $date;
    }

    public function signature(): string
    {
        return 'Internal date object builder';
    }

    private function tryAllFormats(string $value): ?DateTimeInterface
    {
        $formats = [
            self::DATE_MYSQL, self::DATE_PGSQL, DATE_ATOM, DATE_RFC850, DATE_COOKIE,
            DATE_RFC822, DATE_RFC1036, DATE_RFC1123, DATE_RFC2822, DATE_RFC3339,
            DATE_RFC3339_EXTENDED, DATE_RFC7231, DATE_RSS, DATE_W3C, self::DATE_WITHOUT_TIME
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
