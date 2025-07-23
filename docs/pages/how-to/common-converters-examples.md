# Common converters examples

Instead of providing converters out-of-the-box, this library focuses on easing
the creation of custom ones. This way, the mapper is not tied up to a
third-party library release-cycle and can be adapted to fit the needs of the
application's business logics.

Below is a list of common features that can inspire or be implemented by
third-party libraries or applications.

!!! info inline end
    These examples are not available out-of-the-box, they can be implemented
    using the library's API and should be adapted to fit the needs of the
    application.

- [Converting keys format from `snake_case` to `camelCase`](#converting-keys-format-from-snake_case-to-camelcase)
- [Renaming keys](#renaming-keys)
- [Casting scalar values](#casting-scalar-values)
- [Custom datetime format](#custom-datetime-format)
- [Explode string to list](#explode-string-to-list)
- [Array to list](#array-to-list)
- [Json decode](#json-decode)

## Converting keys format from `snake_case` to `camelCase`

The following example is a classical use case where the input array keys are in
`snake_case` format, but the target object properties are in `camelCase` format.
A converter is used to convert the keys before mapping the values to the object.

<details>
<summary>Show code example — snake_case to camelCase key conversion</summary>

```php
namespace My\App;

final readonly class User
{
    public string $userName;
    public \DateTimeInterface $birthDate;
}

(new \CuyZ\Valinor\MapperBuilder())
    ->registerConverter(
        /**
         * Note that this converter will only be called when the input is an
         * array and the target type is an object.
         */
        function (array $values, callable $next): object {
            $camelCaseConverted = array_combine(
                array_map(
                    fn ($key) => lcfirst(str_replace('_', '', ucwords($key, '_'))),
                    array_keys($values),
                ),
                $values,
            );

            return $next($camelCaseConverted);
        }
    )
    ->mapper()
    ->map(\My\App\User::class, [
        // Note that the input keys have the `snake_case` format, but the
        // properties of `User` have the `camelCase` format.
        'user_name' => 'John Doe',
        'birth_date' => '1971-11-08T00:00:00+00:00',
    ]);
```
</details>

For a more granular control, an attribute can be used to target specific
objects, as shown in the example below:

<details>
<summary>Show code example — snake_case to camelCase key conversion attribute</summary>

```php
namespace My\App;

#[\CuyZ\Valinor\Mapper\AsConverter]
#[\Attribute(\Attribute::TARGET_CLASS)]
final class CamelCaseKeys
{
    /**
     * @param array<mixed> $value
     * @param callable(array<mixed>): object $next
     */
    public function map(array $value, callable $next): object
    {
        $transformed = [];

        foreach ($value as $key => $item) {
            $camelCaseKey = lcfirst(str_replace('_', '', ucwords($key, '_')));

            $transformed[$camelCaseKey] = $item;
        }

        return $next($transformed);
    }
}

#[\My\App\CamelCaseKeys]
final readonly class User
{
    public string $userName;
    public \DateTimeInterface $birthDate;
}

(new \CuyZ\Valinor\MapperBuilder())
    ->mapper()
    ->map(\My\App\User::class, [
        // Note that the input keys have the `snake_case` format, but the
        // properties of `User` have the `camelCase` format.
        'user_name' => 'John Doe',
        'birth_date' => '1971-11-08T00:00:00+00:00',
    ]);
```
</details>

## Renaming keys

Keys coming from the input may differ from the target object properties and
need to be renamed before mapping the values to the object.

With the following converter example, keys will be globally renamed:

<details>
<summary>Show code example — Keys renaming</summary>

```php
namespace My\App;

final readonly class Location
{
    public string $city;
    public string $zipCode;
}

(new \CuyZ\Valinor\MapperBuilder())
    ->registerConverter(
        function (array $value, callable $next): mixed {
            $mapping = [
                'town' => 'city',
                'postalCode' => 'zipCode',
            ];

            $renamed = [];

            foreach ($value as $key => $item) {
                $renamed[$mapping[$key] ?? $key] = $item;
            }

            return $next($renamed);
        }
    )
    ->mapper()
    ->map(\My\App\Location::class, [
        'town' => 'Lyon', // `town` will be renamed to `city`
        'postalCode' => '69000', // `postalCode` will be renamed to `zipCode`
    ]);
```
</details>

For a more granular control, an attribute can be used to target specific
objects, as shown in the example below:

<details>
<summary>Show code example — Keys renaming attribute</summary>

```php
namespace My\App;

#[\CuyZ\Valinor\Mapper\AsConverter]
#[\Attribute(\Attribute::TARGET_CLASS)]
final class RenameKeys
{
    public function __construct(
        /** @var non-empty-array<non-empty-string, non-empty-string> */
        private array $mapping,
    ) {}

    /**
     * @param array<mixed> $value
     * @param callable(array<mixed>): object $next
     */
    public function map(array $value, callable $next): object
    {
        $renamed = [];

        foreach ($value as $key => $item) {
            $renamed[$this->mapping[$key] ?? $key] = $item;
        }

        return $next($renamed);
    }
}

#[\My\App\RenameKeys([
    'town' => 'city',
    'postalCode' => 'zipCode',
])]
final readonly class Location
{
    public string $city;
    public string $zipCode;
}

(new \CuyZ\Valinor\MapperBuilder())
    ->mapper()
    ->map(\My\App\Location::class, [
        'town' => 'Lyon', // `town` will be renamed to `city`
        'postalCode' => '69000', // `postalCode` will be renamed to `zipCode`
    ]);
```
</details>

## Casting scalar values

Sometimes the input data is not in the expected format for a scalar value.
Converters can be used to convert the input data to the expected type, allowing
the mapper to handle the data correctly.

!!! note
    Scalar value casting can also be globally enabled, [see documentation about
    `MapperBuilder::allowScalarValueCasting()`](../usage/type-strictness-and-flexibility.md#allowing-scalar-value-casting).

=== "Casting to boolean"

    Values such as `'yes'`, `'no'`, `'on'`, and `'off'` can be used in malformatted
    external APIs or user inputs, and need to be converted to a boolean value.
    
    This can be achieved by creating a custom converter that will handle all boolean
    values and convert them to the expected type.
    
    <details>
    <summary>Show code example — Cast to boolean</summary>
    
    ```php
    namespace My\App;

    final readonly class User
    {
        public function __construct(
            public string $name,
            public bool $isActive,
        ) {}
    }
    
    $user = (new \CuyZ\Valinor\MapperBuilder())
        ->registerConverter(function (string $value, callable $next): bool {
            $value = match ($value) {
                'yes', 'on' => true,
                'no', 'off' => false,
                default => $value,
            };
    
            return $next($value);
        })
        ->mapper()
        ->map(\My\App\User::class, [
            'name' => 'John Doe',
            'isActive' => 'yes',
        ]);
    
    $user->name === 'John Doe';
    $user->isActive === true;
    ```
    </details>
    
    For a more granular control, an attribute can be used to target specific
    properties, as shown in the example below:
    
    <details>
    <summary>Show code example — Cast to boolean attribute</summary>
    
    ```php
    namespace My\App;
    
    #[\CuyZ\Valinor\Mapper\AsConverter]
    #[\Attribute(\Attribute::TARGET_PROPERTY)]
    final class CastToBool
    {
        /**
         * @param callable(mixed): bool $next
         */
        public function map(string $value, callable $next): bool
        {
            $value = match ($value) {
                'yes', 'on' => true,
                'no', 'off' => false,
                default => $value,
            };
    
            return $next($value);
        }
    }
    
    final readonly class User
    {
        public function __construct(
            public string $name,
            #[\My\App\CastToBool]
            public bool $isActive,
        ) {}
    }
    
    $user = (new \CuyZ\Valinor\MapperBuilder())
        ->mapper()
        ->map(\My\App\User::class, [
            'name' => 'John Doe',
            'isActive' => 'yes',
        ]);
    
    $user->name === 'John Doe';
    $user->isActive === true;
    ```
    </details>

=== "Casting to string"

    If the input data should be a string, but is provided as an integer or a float,
    a custom converter can handle the conversion.
    
    <details>
    <summary>Show code example — Cast to string</summary>
    
    ```php
    namespace My\App;

    final readonly class User
    {
        public function __construct(
            public string $id,
            public string $name,
        ) {}
    }
    
    $user = (new \CuyZ\Valinor\MapperBuilder())
        ->registerConverter(
            fn (int|float $value): string => (string)$value
        )
        ->mapper()
        ->map(\My\App\User::class, [
            'id' => 1337, // Integer 1337 will be converted to string '1337'
            'name' => 'John Doe',
        ]);
    
    $user->id === '1337';
    $user->name === 'John Doe';
    ```
    </details>
    
    For a more granular control, an attribute can be used to target specific
    properties, as shown in the example below:
    
    <details>
    <summary>Show code example — Cast to string attribute</summary>
    
    ```php
    namespace My\App;
    
    #[\CuyZ\Valinor\Mapper\AsConverter]
    #[\Attribute(\Attribute::TARGET_PROPERTY)]
    final class CastToString
    {
        public function map(int|float $value): string
        {
            return (string)$value;
        }
    }
    
    final readonly class User
    {
        public function __construct(
            #[\My\App\CastToString]
            public string $id,
            public string $name,
        ) {}
    }
    
    $user = (new \CuyZ\Valinor\MapperBuilder())
        ->mapper()
        ->map(\My\App\User::class, [
            'id' => 1337, // Integer 1337 will be converted to string '1337'
            'name' => 'John Doe',
        ]);
    
    $user->id === '1337';
    $user->name === 'John Doe';
    ```
    </details>

=== "Casting to integer"
    
    In some cases, the input data may be a string representation of an integer, and
    must be converted to an actual integer value.
    
    This can be achieved by creating a custom converter that will handle all string
    values that represent an integer, and convert them to the expected type.
    
    <details>
    <summary>Show code example — Cast to integer</summary>
    
    ```php
    namespace My\App;

    final readonly class User
    {
        public function __construct(
            public string $name,
            public int $age,
        ) {}
    }
    
    $user = (new \CuyZ\Valinor\MapperBuilder())
        ->registerConverter(function (string $value, callable $next): int {
            if (filter_var($value, FILTER_VALIDATE_INT)) {
                return (int)$value;
            }
    
            return $next($value);
        })
        ->mapper()
        ->map(\My\App\User::class, [
            'name' => 'John Doe',
            'age' => '42', // String '42' will be converted to integer 42
        ]);
    
    $user->name === 'John Doe';
    $user->age === 42;
    ```
    </details>
    
    For a more granular control, an attribute can be used to target specific
    properties, as shown in the example below:
    
    <details>
    <summary>Show code example — Cast to integer attribute</summary>
    
    ```php
    namespace My\App;
    
    #[\CuyZ\Valinor\Mapper\AsConverter]
    #[\Attribute(\Attribute::TARGET_PROPERTY)]
    final class CastToInt
    {
        /**
         * @param callable(string): int $next
         */
        public function map(string $value, callable $next): int
        {
            if (filter_var($value, FILTER_VALIDATE_INT)) {
                return (int)$value;
            }
    
            return $next($value);
        }
    }
    
    final readonly class User
    {
        public function __construct(
            public string $name,
            #[\My\App\CastToInt]
            public int $age,
        ) {}
    }
    
    $user = (new \CuyZ\Valinor\MapperBuilder())
        ->mapper()
        ->map(\My\App\User::class, [
            'name' => 'John Doe',
            'age' => '42', // String '42' will be converted to integer 42
        ]);
    
    $user->name === 'John Doe';
    $user->age === 42;
    ```
    </details>

=== "Casting to float"
    
    In some cases, the input data may be a string representation of a float, and
    must be converted to an actual float value.
    
    This can be achieved by creating a custom converter that will handle all string
    values that represent a float, and convert them to the expected type.
    
    <details>
    <summary>Show code example — Cast to float</summary>
    
    ```php
    namespace My\App;
    
    final readonly class User
    {
        public function __construct(
            public string $name,
            public float $accountBalance,
        ) {}
    }
    
    $user = (new \CuyZ\Valinor\MapperBuilder())
        ->registerConverter(function (string $value, callable $next): float {
            if (is_numeric($value)) {
                return (float)$value;
            }
    
            return $next($value);
        })
        ->mapper()
        ->map(\My\App\User::class, [
            'name' => 'John Doe',
            'accountBalance' => '1337.42', // String '1337.42' will be converted to float 1337.42
        ]);
    
    $user->name === 'John Doe';
    $user->accountBalance === 1337.42;
    ```
    </details>
    
    For a more granular control, an attribute can be used to target specific
    properties, as shown in the example below:
    
    <details>
    <summary>Show code example — Cast to float attribute</summary>
    
    ```php
    namespace My\App;
    
    #[\CuyZ\Valinor\Mapper\AsConverter]
    #[\Attribute(\Attribute::TARGET_PROPERTY)]
    final class CastToFloat
    {
        /**
         * @param callable(string): float $next
         */
        public function map(string $value, callable $next): float
        {
            if (is_numeric($value)) {
                return (float)$value;
            }
    
            return $next($value);
        }
    }
    
    final readonly class User
    {
        public function __construct(
            public string $name,
            #[\My\App\CastToFloat]
            public float $accountBalance,
        ) {}
    }
    
    $user = (new \CuyZ\Valinor\MapperBuilder())
        ->mapper()
        ->map(\My\App\User::class, [
            'name' => 'John Doe',
            'accountBalance' => '1337.42', // String '1337.42' will be converted to float 1337.42
        ]);
    
    $user->name === 'John Doe';
    $user->accountBalance === 1337.42;
    ```
    </details>

## Custom datetime format

Global datetime format customization can be enabled with the mapper builder, see
[`MapperBuilder::supportDateFormats()`](deal-with-dates.md).

For a more granular control, an attribute can be used to target specific
properties, as shown in the example below:

<details>
<summary>Show code example — Custom datetime format attribute</summary>

```php
namespace My\App;

#[\CuyZ\Valinor\Mapper\AsConverter]
#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class DateTimeFormat
{
    public function __construct(
        /** @var non-empty-string */
        private string $format,
    ) {}

    public function map(string $value): \DateTimeInterface
    {
        $date = \DateTimeImmutable::createFromFormat($this->format, $value);

        if ($date === false) {
            throw new \RuntimeException("Invalid datetime value `$value`");
        }

        return $date;
    }
}

final readonly class User
{
    public string $name;

    #[\My\App\DateTimeFormat('Y-m-d')]
    public \DateTimeInterface $birthdate;

    #[\My\App\DateTimeFormat('Y/m/d')]
    public \DateTimeInterface $profileCreatedAt;
}

(new \CuyZ\Valinor\MapperBuilder())
    ->mapper()
    ->map(\My\App\User::class, [
        'name' => 'John Doe',
        'birthdate' => '1971-11-08',
        'profileCreatedAt' => '2025/05/17',
    ]);
```
</details>

## Explode string to list

When dealing with input data that is a string representation of an array, for
instance a comma-separated list, a converter attribute can be used to convert
the string into a list.

<details>
<summary>Show code example — Explode attribute</summary>

```php
namespace My\App;

#[\CuyZ\Valinor\Mapper\AsConverter]
#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class Explode
{
    public function __construct(
        /** @var non-empty-string */
        private string $separator,
    ) {}

    /**
     * @return array<mixed>
     */
    public function map(string $value): array
    {
        return explode($this->separator, $value);
    }
}

final readonly class Product
{
    public string $name;

    /** @var list<non-empty-string> */
    #[\My\App\Explode(separator: ',')] public array $size;
}

(new \CuyZ\Valinor\MapperBuilder())
    ->mapper()
    ->map(\My\App\Product::class, [
        'name' => 'T-Shirt',
        'size' => 'XS,S,M,L,XL',
    ]);
```
</details>

## Array to list

Global array to list conversion can be enabled with the mapper builder, see
[`MapperBuilder::allowNonSequentialList()`](../usage/type-strictness-and-flexibility.md#allowing-non-sequential-lists).

For a more granular control, an attribute can be used to target specific
properties, as shown in the example below:

<details>
<summary>Show code example — Array to list attribute</summary>

```php
namespace My\App;

#[\CuyZ\Valinor\Mapper\AsConverter]
#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class ArrayToList
{
    /**
     * @param array<mixed> $value
     * @return list<mixed>
     */
    public function map(array $value): array
    {
        return array_values($value);
    }
}

final readonly class Person
{
    public string $name;

    /** @var list<non-empty-string> */
    #[\My\App\ArrayToList] public array $pets;
}

$person = (new \CuyZ\Valinor\MapperBuilder())
    ->mapper()
    ->map(\My\App\Person::class, [
        'name' => 'John Doe',
        'pets' => [
            'Dog' => 'Dog',
            'Cat' => 'Cat',
        ],
    ]);

$person->pets === [0 => 'Dog', 1 => 'Cat'];
```
</details>

## Json decode

When working with data that can contain JSON strings, a converter can be used to
decode it directly into the expected type.

<details>
<summary>Show code example — Json decode attribute</summary>

```php
namespace My\App;

#[\CuyZ\Valinor\Mapper\AsConverter]
#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class JsonDecode
{
    /**
     * @param callable(mixed): mixed $next
     */
    public function map(string $value, callable $next): mixed
    {
        $decoded = json_decode($value, associative: true, flags: JSON_THROW_ON_ERROR);

        return $next($decoded);
    }
}

final readonly class UserProfile
{
    public string $username;

    public string $email;

    /** @var array<string, scalar> */
    #[\My\App\JsonDecode] public array $preferences;

    /** @var list<string> */
    #[\My\App\JsonDecode] public array $tags;
}

$userProfile = (new \CuyZ\Valinor\MapperBuilder())
    ->mapper()
    ->map(\My\App\UserProfile::class, [
        'username' => 'john_doe',
        'email' => 'john.doe@example.com',
        'preferences' => '{"theme": "dark", "notifications": true}',
        'tags' => '["developer", "php", "api"]',
    ]);

$userProfile->preferences === ['theme' => 'dark', 'notifications' => true];
$userProfile->tags === ['developer', 'php', 'api'];
```
</details>
