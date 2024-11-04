# Changelog 1.14.0 — 4th of November 2024

!!! info inline end "[See release on GitHub]"
    [See release on GitHub]: https://github.com/CuyZ/Valinor/releases/tag/1.14.0

## Notable changes

**PHP 8.4 support 🐘**

Enjoy the upcoming PHP 8.4 version before it is even officially released!

**Pretty JSON output**

The `JSON_PRETTY_PRINT` option is now supported by the JSON normalizer and will
format the ouput with whitespaces and line breaks:

```php
$input = [
    'value' => 'foo',
    'list' => [
        'foo',
        42,
        ['sub']
    ],
    'associative' => [
        'value' => 'foo',
        'sub' => [
            'string' => 'foo',
            'integer' => 42,
        ],
    ],
];

(new \CuyZ\Valinor\MapperBuilder())
    ->normalizer(\CuyZ\Valinor\Normalizer\Format::json())
    ->withOptions(\JSON_PRETTY_PRINT)
    ->normalize($input);

// Result:
// {
//     "value": "foo",
//     "list": [
//         "foo",
//         42,
//         [
//             "sub"
//         ]
//     ],
//     "associative": {
//         "value": "foo",
//         "sub": {
//             "string": "foo",
//             "integer": 42
//         }
//     }
// }
```

**Force array as object in JSON output**

The `JSON_FORCE_OBJECT` option is now supported by the JSON normalizer and will
force the output of an array to be an object:

```php
(new \CuyZ\Valinor\MapperBuilder())
    ->normalizer(Format::json())
    ->withOptions(JSON_FORCE_OBJECT)
    ->normalize(['foo', 'bar']);

// {"0":"foo","1":"bar"}
```

### Features

* Add support for `JSON_FORCE_OBJECT` option in JSON normalizer ([f3e8c1](https://github.com/CuyZ/Valinor/commit/f3e8c1e09c0b947693cd40941d2ae5ab9b31bbd9))
* Add support for PHP 8.4 ([07a06a](https://github.com/CuyZ/Valinor/commit/07a06a2fc730fb9bc9049adfc26cc1749afbbe41))
* Handle `JSON_PRETTY_PRINT` option with the JSON normalizer ([950395](https://github.com/CuyZ/Valinor/commit/950395ec8d00695e3b311a101af19ea81074427e))

### Bug Fixes

* Handle float type casting properly ([8742b2](https://github.com/CuyZ/Valinor/commit/8742b273f1bdd6e7037f1398b9d8822508ffda63))
* Handle namespace for Closure without class scope ([7a0fc2](https://github.com/CuyZ/Valinor/commit/7a0fc21db0413fbec852c65d641c0e08372a2869))
* Prevent cache corruption when normalizing and mapping to enum ([e695b2](https://github.com/CuyZ/Valinor/commit/e695b29f06720368e471e1101381aa8414556be2))
* Properly handle class sharing class name and namespace group name ([6e68d6](https://github.com/CuyZ/Valinor/commit/6e68d6f2c0102369e9626303007e67a07aa9f690))

### Other

* Change implicitly nullable parameter types ([304db3](https://github.com/CuyZ/Valinor/commit/304db395bf1b16d1b8c4db3278f38fcac3f020d1))
* Fix typo in property type annotation ([b9c6ad](https://github.com/CuyZ/Valinor/commit/b9c6add856ba6b2ee2034deaaee312a99159e885))
* Use `xxh128` hash algorithm for cache keys ([546c45](https://github.com/CuyZ/Valinor/commit/546c45887ca64605cfb2967cf1afe4d14298cdbc))
