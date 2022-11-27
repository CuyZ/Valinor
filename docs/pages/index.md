---
hide:
 - toc
---

&nbsp;
======

<div align="center">
   <img src="img/valinor-banner.svg" />

   <div>— From boring old arrays to shiny typed objects —</div>
</div>

---

Valinor takes care of the construction and validation of raw inputs (JSON, plain
arrays, etc.) into objects, ensuring a perfectly valid state. It allows the 
objects to be used without having to worry about their integrity during the 
whole application lifecycle.

The validation system will detect any incorrect value and help the developers by
providing precise and human-readable error messages. 

The mapper can handle native PHP types as well as other advanced types supported
by [PHPStan] and [Psalm] like shaped arrays, generics, integer ranges and more.

## Why?

There are many benefits of using objects instead of plain arrays in a codebase:

1. **Type safety** — the structure of an object is known and guaranteed, no 
   need for type checks once the object is constructed.
2. **Data integrity** — the object cannot be in an invalid state, it will always
   contain valid data.
3. **Encapsulation** — the logic of an object is isolated from the outside.

---

Validating and transforming raw data into an object can be achieved easily with 
native PHP, but it requires a lot a boilerplate code.

Below is a simple example of doing that without a mapper:

```php
final class Person
{
    public readonly string $name;
    
    public readonly DateTimeInterface $birthDate;
}

$data = $client->request('GET', 'https://example.com/person/42')->toArray();

if (! isset($data['name']) || ! is_string($data['name'])) {
    // Cumbersome error handling
}

if (! isset($data['birthDate']) || ! is_string($data['birthDate'])) {
    // Another cumbersome error handling
}

$birthDate = DateTimeImmutable::createFromFormat('Y-m-d', $data['birthDate']);

if (! $birthDate instanceof DateTimeInterface) {
    // Yet another cumbersome error handling
}

$person = new Person($data['name'], $birthDate);
```

Using a mapper saves a lot of time and energy, especially on objects with a lot
of properties:

```php
$data = $client->request('GET', 'https://example.com/person/42')->toArray();

try {
    $person = (new \CuyZ\Valinor\MapperBuilder())
        ->mapper()
        ->map(Person::class, $data);
} catch (\CuyZ\Valinor\Mapper\MappingError $error) {
    // Detailed error handling
}
```

---

This library provides advanced features for more complex cases, check out the
[next chapter](getting-started.md) to get started.
