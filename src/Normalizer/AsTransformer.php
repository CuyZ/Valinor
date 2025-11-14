<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer;

use Attribute;

/**
 * This attribute can be used to automatically register a transformer attribute.
 *
 * When there is no control over the transformer attribute class, the following
 * method can be used:
 * {@see \CuyZ\Valinor\NormalizerBuilder::registerTransformer()}
 *
 * ```
 * namespace My\App;
 *
 * #[\CuyZ\Valinor\Normalizer\AsTransformer]
 * #[\Attribute(\Attribute::TARGET_PROPERTY)]
 * final class DateTimeFormat
 * {
 *     public function __construct(private string $format) {}
 *
 *     public function normalize(\DateTimeInterface $date): string
 *     {
 *         return $date->format($this->format);
 *     }
 * }
 *
 * final readonly class Event
 * {
 *     public function __construct(
 *         public string $eventName,
 *         #[\My\App\DateTimeFormat('Y/m/d')]
 *         public \DateTimeInterface $date,
 *     ) {}
 * }
 *
 * (new \CuyZ\Valinor\NormalizerBuilder())
 *     ->normalizer(\CuyZ\Valinor\Normalizer\Format::array())
 *     ->normalize(new \My\App\Event(
 *         eventName: 'Release of legendary album',
 *         date: new \DateTimeImmutable('1971-11-08'),
 *     ));
 *
 * // [
 * //     'eventName' => 'Release of legendary album',
 * //     'date' => '1971/11/08',
 * // ]
 * ```
 *
 * @api
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class AsTransformer {}
