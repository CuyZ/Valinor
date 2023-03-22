# Alternatives to this library

Mapping and hydration have been available in the PHP world for a long time.
This library aims to bring powerful features to this aspect, some of which are
missing in similar packages:

- Objects have a valid state when mapping is over.
- Validation is recursively applied on the input during mapping, [reported 
  error messages] bring clarity to missing or invalid values.  
- Type declaration follow community standards developed by popular packages, 
  meaning that [advanced types] like `list<string>`, `non-empty-string`, 
  `positive-int`, `int<0, 42>`, shaped arrays, generic classes and more are 
  handled and validated properly.
- The mapper can be flexible, allowing for instance [type casting] when needed.
- Mapped objects should not rely on inheritance.

You may take a look at alternative projects, but some features listed above
might be missing:

- [`symfony/serializer`](https://github.com/symfony/serializer)
- [`eventsauce/object-hydrator`](https://github.com/EventSaucePHP/ObjectHydrator)
- [`crell/serde`](https://github.com/Crell/Serde)
- [`spatie/laravel-data`](https://github.com/spatie/laravel-data)
- [`jms/serializer`](https://github.com/schmittjoh/serializer)
- [`netresearch/jsonmapper`](https://github.com/cweiske/jsonmapper)
- [`json-mapper/json-mapper`](https://github.com/JsonMapper/JsonMapper)
- [`brick/json-mapper`](https://github.com/brick/json-mapper)

[reported error messages]: ../usage/validation-and-error-handling.md
[advanced types]: ../usage/type-reference.md
[type casting]: ../usage/type-strictness-and-flexibility.md#enabling-flexible-casting
