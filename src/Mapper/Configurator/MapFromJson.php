<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Configurator;

use Attribute;
use CuyZ\Valinor\Mapper\AsConverter;
use CuyZ\Valinor\Mapper\Tree\Message\MessageBuilder;
use JsonException;

use function json_decode;

use const JSON_THROW_ON_ERROR;

/**
 * Decodes a JSON string and hands the result over to the mapper. This is useful
 * when the input data carries a nested structure as an encoded JSON string, for
 * instance a column stored in a database or a field in a form submission.
 *
 * The decoded value is then mapped against the targeted type, so the usual
 * validation and error reporting still apply. An invalid JSON string raises a
 * mapping error.
 *
 * ```
 * use CuyZ\Valinor\MapperBuilder;
 * use CuyZ\Valinor\Mapper\Configurator\MapFromJson;
 *
 * final readonly class User
 * {
 *     public function __construct(
 *         public string $name,
 *
 *         #[MapFromJson]
 *         /** @var list<string> *\/
 *         public array $roles,
 *     ) {}
 * }
 *
 * $user = (new MapperBuilder())
 *     ->mapper()
 *     ->map(User::class, [
 *         'name' => 'John Doe',
 *         'roles' => '["admin", "editor"]', // mapped to `['admin', 'editor']`
 *     ]);
 * ```
 *
 * @api
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
#[AsConverter]
final class MapFromJson
{
    /**
     * @template T
     * @param callable(mixed): T $next
     * @return T
     */
    public function map(string $value, callable $next): mixed
    {
        try {
            $decoded = json_decode($value, associative: true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw MessageBuilder::newError('Value {source_value} is not valid JSON.')
                ->withCode('invalid_json')
                ->build();
        }

        return $next($decoded);
    }
}
