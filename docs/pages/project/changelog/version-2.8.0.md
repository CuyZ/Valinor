# Changelog 2.8.0 — 19th of November 2026

!!! info inline end "[See release on GitHub]"
    [See release on GitHub]: https://github.com/CuyZ/Valinor/releases/tag/2.8.0

## Notable changes

### PHP 8.6 support 🐘

PHP 8.6 is released today, and the library supports it from day one!

Since this version, a readonly property can be declared with a default value: it
is initialized when the object is instantiated and cannot be written again. Such
a property is not part of the mapped values: it keeps its default value, and a
value given for it in the source is reported as an unexpected key.

```php
final class Article
{
    public readonly string $status = 'draft';

    public string $title;
}

$article = (new \CuyZ\Valinor\MapperBuilder())
    ->mapper()
    ->map(Article::class, ['title' => 'Hello world']);

$article->status; // 'draft'
```

---

### Features

* Add support for PHP 8.6 ([c6694f](https://github.com/CuyZ/Valinor/commit/c6694ffa14c0a20a39cd2e991aeb4d82e11a4461))

### Bug Fixes

* Allow default value on argument filled by a fixed generic ([dcd615](https://github.com/CuyZ/Valinor/commit/dcd615bd8d2d7937ad5d1315c531bbe5dbfe3b22))
* Do not map readonly properties with default value ([38fe41](https://github.com/CuyZ/Valinor/commit/38fe41db0412eb91eaa12560ed6bd9734b8a69bc))
* Keep keys of non-list array normalized as shaped list ([13d882](https://github.com/CuyZ/Valinor/commit/13d882a5e3176e0668649e77565b63f9b8843d82))
* Return actual native types from types ([0185fd](https://github.com/CuyZ/Valinor/commit/0185fd2d78621de1ac3859cf4631a4b3ca23ca03))
