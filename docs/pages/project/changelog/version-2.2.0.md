# Changelog 2.2.0 — 29th of September 2025

!!! info inline end "[See release on GitHub]"
    [See release on GitHub]: https://github.com/CuyZ/Valinor/releases/tag/2.2.0

## Notable new features

**Mapping error messages improvements**

Feedback has been improved in mapping error messages, especially the expected
signature of the failing nodes.

This gets rid of the infamous `?` that was used whenever an object was present
in a type, leading to incomplete and misleading messages.

Example of a new message:

```php
final class User
{
    public function __construct(
        public string $name,
        public int $age,
    ) {}
}

(new MapperBuilder())
    ->mapper()
    ->map(User::class, 'invalid value');

// Could not map type `User`. An error occurred at path *root*: Value
// 'invalid value' does not match `array{name: string, age: int}`.
```

### Features

* Improve mapping error messages types signatures ([ce1b0a](https://github.com/CuyZ/Valinor/commit/ce1b0a294c5de3bf688290647b5dd5e5bbca1c77))

### Bug Fixes

* Prevent undefined values in `non-empty-list` ([9739cd](https://github.com/CuyZ/Valinor/commit/9739cd0598a21d092c4dae528e9b6fa70205fe47))
* Properly detect nested invalid types during mapping ([ad756a](https://github.com/CuyZ/Valinor/commit/ad756aeb9aa30d0e72ce374e756f3d585848e0d1))
* Use proper error message for invalid nullable scalar value ([b84cbe](https://github.com/CuyZ/Valinor/commit/b84cbec459deaf6e6a7d83726c26505848e4ebf9))

### Other

* Add safeguard in type parsing when reading next type ([da0de0](https://github.com/CuyZ/Valinor/commit/da0de013ecd92605f9c7d2ee4d465e805cdd6e5c))
* Improve type parsing error when an unexpected token is found ([5ae904](https://github.com/CuyZ/Valinor/commit/5ae904414ac4660a7da079d074fdbae8dd3b80ba))
* Lighten types initialization ([6f0b3f](https://github.com/CuyZ/Valinor/commit/6f0b3f162c5feb63444433f4313b8559697d3fc5))
* Parse `iterable` type the same way it is done with `array` ([6291a7](https://github.com/CuyZ/Valinor/commit/6291a700eda0e7443cd5f64774f33d190296c2ad))
* Rework how type traversing is used ([20f17f](https://github.com/CuyZ/Valinor/commit/20f17fbe75e787723e60b235b08cd8c0308900e6))
* Set default exception error code to `unknown` ([c8ef49](https://github.com/CuyZ/Valinor/commit/c8ef491c2d694e05fbd1e6be247f7ff6fa89c3e0))
