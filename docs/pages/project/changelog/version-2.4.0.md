# Changelog 2.4.0 — 23rd of March 2026

!!! info inline end "[See release on GitHub]"
    [See release on GitHub]: https://github.com/CuyZ/Valinor/releases/tag/2.4.0

## Notable changes

This release brings a whole set of new features to the library:

- [HTTP request mapping support](#http-request-mapping-support)
- [Mapper/Normalizer configurators support](#mappernormalizer-configurators-support)
- [CamelCase/snake_case keys conversion support](#camelcasesnake_case-keys-conversion-support)
- [Keys case restriction support](#keys-case-restriction-support)

Enjoy! 🎉

---

### HTTP request mapping support

This library now provides a way to map an HTTP request to controller
action parameters or object properties. Parameters can be mapped from
route, query and body values.

Three attributes are available to explicitly bind a parameter to a
single source, ensuring the value is never resolved from the wrong
source:

- `#[FromRoute]` — for parameters extracted from the URL path by router
- `#[FromQuery]` — for query string parameters
- `#[FromBody]` — for request body values

Those attributes can be omitted entirely if the parameter is not bound
to a specific source, in which case a collision error is raised if the
same key is found in more than one source.

This gives controllers a clean, type-safe signature without coupling to
a framework's request object, while benefiting from the library's
validation and error handling.

Normal mapping rules apply there: parameters are required unless they
have a default value.

Route and query parameter values coming from an HTTP request are
typically strings. The mapper automatically handles scalar value
casting for these parameters: a string `"42"` will be properly mapped to
an `int` parameter.

#### Mapping a request using attributes

Consider an API that lists articles for a given author. The author
identifier comes from the URL path, while filtering and pagination come
from the query string.

```php
use CuyZ\Valinor\Mapper\Http\FromQuery;
use CuyZ\Valinor\Mapper\Http\FromRoute;
use CuyZ\Valinor\Mapper\Http\HttpRequest;
use CuyZ\Valinor\MapperBuilder;

final class ListArticles
{
    /**
     * GET /api/authors/{authorId}/articles?status=X&page=X&limit=X
     *
     * @param non-empty-string $page
     * @param positive-int $page
     * @param int<10, 100> $limit
     */
    public function __invoke(
        // Comes from the route
        #[FromRoute] string $authorId,

        // All come from query parameters
        #[FromQuery] string $status,
        #[FromQuery] int $page = 1,
        #[FromQuery] int $limit = 10,
    ): ResponseInterface { … }
}

// GET /api/authors/42/articles?status=published&page=2
$request = new HttpRequest(
    routeParameters: ['authorId' => 42],
    queryParameters: [
        'status' => 'published',
        'page' => 2,
    ],
);

$controller = new ListArticles();

$arguments = (new MapperBuilder())
    ->argumentsMapper()
    ->mapArguments($controller, $request);

$response = $controller(...$arguments);
```

#### Mapping a request without using attributes

When it is unnecessary to distinguish which source a parameter comes
from, the attribute can be omitted entirely — the mapper will resolve
each parameter from whichever source contains the matching key.

```php
use CuyZ\Valinor\Mapper\Http\HttpRequest;
use CuyZ\Valinor\MapperBuilder;

final class PostComment
{
    /**
     * POST /api/posts/{postId}/comments
     *
     * @param non-empty-string $author
     * @param non-empty-string $content
     */
    public function __invoke(
        int $postId,
        string $author,
        string $content,
    ): ResponseInterface { … }
}

// POST /api/posts/1337/comments
$request = new HttpRequest(
    routeParameters: ['postId' => 1337],
    bodyValues: [
        'author' => 'jane.doe@example.com',
        'content' => 'Great article, thanks for sharing!',
    ],
);

$controller = new PostComment();

$arguments = (new MapperBuilder())
    ->argumentsMapper()
    ->mapArguments($controller, $request);

$response = $controller(...$arguments);
```

!!! note

    If the same key is found in more than one source for a parameter 
    that has no attribute, a collision error is raised.

#### Mapping all parameters at once

Instead of mapping individual query parameters or body values to
separate parameters, the `asRoot` option can be used to map all of them
at once to a single parameter. This is useful when working with complex
data structures or when the number of parameters is large.

```php
use CuyZ\Valinor\Mapper\Http\FromQuery;
use CuyZ\Valinor\Mapper\Http\FromRoute;

final readonly class ArticleFilters
{
    public function __construct(
        /** @var non-empty-string */
        public string $status,
        /** @var positive-int */
        public int $page = 1,
        /** @var int<10, 100> */
        public int $limit = 10,
    ) {}
}

final class ListArticles
{
    /**
     * GET /api/authors/{authorId}/articles?status=X&page=X&limit=X
     */
    public function __invoke(
        #[FromRoute] string $authorId,
        #[FromQuery(asRoot: true)] ArticleFilters $filters,
    ): ResponseInterface { … }
}
```

The same approach works with `#[FromBody(asRoot: true)]` for body
values.

!!! hint

    A shaped array can be used alongside `asRoot` to map all values to a
    single parameter:
   
    ```php
    use CuyZ\Valinor\Mapper\Http\FromQuery;
    use CuyZ\Valinor\Mapper\Http\FromRoute;
   
    final class ListArticles
    {
        /**
         * GET /api/authors/{authorId}/articles?status=X&&page=X&limit=X
         *
         * @param array{
         *     status: non-empty-string,
         *     page?: positive-int,
         *     limit?: int<10, 100>,
         * } $filters
         */
        public function __invoke(
            #[FromRoute] string $authorId,
            #[FromQuery(asRoot: true)] array $filters,
        ): ResponseInterface { … }
    }
    ```

#### Mapping to an object

Instead of mapping to a callable's arguments, an `HttpRequest` can be
mapped directly to an object. The attributes work the same way on
constructor parameters or promoted properties.

```php
use CuyZ\Valinor\Mapper\Http\FromBody;
use CuyZ\Valinor\Mapper\Http\FromRoute;
use CuyZ\Valinor\Mapper\Http\HttpRequest;
use CuyZ\Valinor\MapperBuilder;

final readonly class PostComment
{
    public function __construct(
        #[FromRoute] public int $postId,
        /** @var non-empty-string */
        #[FromBody] public string $author,
        /** @var non-empty-string */
        #[FromBody] public string $content,
    ) {}
}

$request = new HttpRequest(
    routeParameters: ['postId' => 1337],
    bodyValues: [
        'author' => 'jane.doe@example.com',
        'content' => 'Great article, thanks for sharing!',
    ],
);

$comment = (new MapperBuilder())
    ->mapper()
    ->map(PostComment::class, $request);

// $comment->postId  === 1337
// $comment->author  === 'jane.doe@example.com'
// $comment->content === 'Great article, thanks for sharing!'
```

#### Using PSR-7 requests

An `HttpRequest` instance can be built directly from a [PSR-7]
`ServerRequestInterface`. This is the recommended approach when
integrating with frameworks that use PSR-7.

```php
use CuyZ\Valinor\Mapper\Http\HttpRequest;
use CuyZ\Valinor\MapperBuilder;

// `$psrRequest` is a PSR-7 `ServerRequestInterface` instance
// `$routeParameters` are the parameters extracted by the router
$request = HttpRequest::fromPsr($psrRequest, $routeParameters);

$arguments = (new MapperBuilder())
    ->argumentsMapper()
    ->mapArguments($controller, $request);
```

The factory method extracts query parameters from `getQueryParams()` and
body values from `getParsedBody()`. It also passes the original PSR-7
request object through, so it can be injected into controller parameters
if needed (see below).

[PSR-7]: https://www.php-fig.org/psr/psr-7/

#### Accessing the original request object

When building an `HttpRequest`, an original request object can be
provided. If a controller parameter's type matches this object, it will
be injected automatically; no attribute is needed.

```php
use CuyZ\Valinor\Mapper\Http\FromRoute;
use CuyZ\Valinor\Mapper\Http\HttpRequest;
use CuyZ\Valinor\MapperBuilder;
use Psr\Http\Message\ServerRequestInterface;

final class ListArticles
{
    /**
     * GET /api/authors/{authorId}/articles
     */
    public function __invoke(
        // Request object injected automatically
        ServerRequestInterface $request,

        #[FromRoute] string $authorId,
    ): ResponseInterface {
        $acceptHeader = $request->getHeaderLine('Accept');

        // …
    }
}

$request = HttpRequest::fromPsr($psrRequest, $routeParameters);

$arguments = (new MapperBuilder())
    ->argumentsMapper()
    ->mapArguments(new ListArticles(), $request);

// $arguments['request'] is the original PSR-7 request instance
```

#### Error handling

When the mapping fails — for instance because a required query parameter
is missing or a body value has the wrong type — a `MappingError` is
thrown, just like with regular mapping.

Read [the validation and error handling chapter] for more information.

[the validation and error handling chapter]: https://valinor-php.dev/latest/usage/validation-and-error-handling/

---

### Mapper/Normalizer configurators support

Introduce `MapperBuilderConfigurator` and `NormalizerBuilderConfigurator`
interfaces along with a `configureWith()` method on both builders.

A configurator is a reusable piece of configuration logic that can be applied to
a `MapperBuilder` or a `NormalizerBuilder` instance. This is useful when the
same configuration needs to be applied in multiple places across an application,
or when configuration logic needs to be distributed as a package.

In the example below, we apply two configuration settings to a `MapperBuilder`
inside a single class, but this could contain any number of customizations,
depending on the needs of the application.

```php
namespace My\App;

use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Mapper\Configurator\MapperBuilderConfigurator;

final class ApplicationMappingConfigurator implements MapperBuilderConfigurator
{
    public function configureMapperBuilder(MapperBuilder $builder): MapperBuilder
    {
        return $builder
            ->allowSuperfluousKeys()
            ->registerConstructor(
                \My\App\CustomerId::fromString(...),
            );
    }
}
```

This configurator can be registered within the `MapperBuilder` instance:

```php
$result = (new \CuyZ\Valinor\MapperBuilder())
    ->configureWith(new \My\App\ApplicationMappingConfigurator())
    ->mapper()
    ->map(\My\App\User::class, [
        'id' => '604e4b36-5b76-4b1a-9e6c-02d5acb53a4d',
        'name' => 'John Doe',
        'extraField' => 'ignored because superfluous keys are allowed',
    ]);
```

#### Composing multiple configurators

Multiple configurators can be combined to compose the final
configuration. Each configurator is applied in order, allowing layered
and modular configuration.

```php
namespace My\App;

use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Mapper\Configurator\MapperBuilderConfigurator;

final class FlexibleMappingConfigurator implements MapperBuilderConfigurator
{
    public function configureMapperBuilder(MapperBuilder $builder): MapperBuilder
    {
        return $builder
            ->allowScalarValueCasting()
            ->allowSuperfluousKeys();
    }
}

final class DomainConstructorsConfigurator implements MapperBuilderConfigurator
{
    public function configureMapperBuilder(MapperBuilder $builder): MapperBuilder
    {
        return $builder
            ->registerConstructor(
                \My\App\CustomerId::fromString(...),
                \My\App\Email::fromString(...),
            );
    }
}

$result = (new \CuyZ\Valinor\MapperBuilder())
    ->configureWith(
        new \My\App\FlexibleMappingConfigurator(),
        new \My\App\DomainConstructorsConfigurator(),
    )
    ->mapper()
    ->map(\My\App\User::class, $someData);
```

This approach keeps each configurator focused on a single concern,
making them easier to test and reuse independently.

#### Using `NormalizerBuilderConfigurator`

The same configurator logic can be applied on `NormalizerBuilder`:

```php
namespace My\App;

use CuyZ\Valinor\NormalizerBuilder;
use CuyZ\Valinor\Normalizer\Configurator\NormalizerBuilderConfigurator;

final class DomainObjectConfigurator implements NormalizerBuilderConfigurator
{
    public function configureNormalizerBuilder(NormalizerBuilder $builder): NormalizerBuilder
    {
        return $builder
            ->registerTransformer(
                fn (\DateTimeInterface $date) => $date->format('Y-m-d')
            )
            ->registerTransformer(
                fn (\My\App\Money $money) => [
                    'amount' => $money->amount,
                    'currency' => $money->currency->value,
                ]
            );
    }
}

final class SensitiveDataConfigurator implements NormalizerBuilderConfigurator
{
    public function configureNormalizerBuilder(NormalizerBuilder $builder): NormalizerBuilder
    {
        return $builder
            ->registerTransformer(
                fn (\My\App\EmailAddress $email) => '***@' . $email->domain()
            );
    }
}

$json = (new \CuyZ\Valinor\NormalizerBuilder())
    ->configureWith(
        new \My\App\DomainObjectConfigurator(),
        new \My\App\SensitiveDataConfigurator(),
    )
    ->normalizer(\CuyZ\Valinor\Normalizer\Format::json())
    ->normalize($someObject);
```

---

### CamelCase/snake_case keys conversion support

Two configurators are available to convert the keys of input data before mapping
them to object properties or shaped array keys. This allows accepting data with
a different naming convention than the one used in the PHP codebase.

#### `ConvertKeysToCamelCase`

| Conversion                 |
|----------------------------|
| `first_name` → `firstName` |
| `FirstName` → `firstName`  |
| `first-name` → `firstName` |

```php
$user = (new \CuyZ\Valinor\MapperBuilder())
    ->configureWith(
        new \CuyZ\Valinor\Mapper\Configurator\ConvertKeysToCamelCase()
    )
    ->mapper()
    ->map(\My\App\User::class, [
        'first_name' => 'John', // mapped to `$firstName`
        'last_name' => 'Doe',   // mapped to `$lastName`
    ]);
```

#### `ConvertKeysToSnakeCase`

| Conversion                  |
|-----------------------------|
| `firstName` → `first_name`  |
| `FirstName` → `first_name`  |
| `first-name` → `first_name` |

```php
$user = (new \CuyZ\Valinor\MapperBuilder())
    ->configureWith(
        new \CuyZ\Valinor\Mapper\Configurator\ConvertKeysToSnakeCase()
    )
    ->mapper()
    ->map(\My\App\User::class, [
        'firstName' => 'John', // mapped to `$first_name`
        'lastName' => 'Doe',   // mapped to `$last_name`
    ]);
```

This configurator can be combined with a key restriction configurator to both
validate and convert keys in a single step. The restriction configurator must be
registered *before* the conversion so that the validation runs on the original
input keys.

```php
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Mapper\Configurator\ConvertKeysToCamelCase;
use CuyZ\Valinor\Mapper\Configurator\RestrictKeysToSnakeCase;

$user = (new MapperBuilder())
    ->configureWith(
        new RestrictKeysToSnakeCase(),
        new ConvertKeysToCamelCase(),
    )
    ->mapper()
    ->map(User::class, [
        'first_name' => 'John',
        'last_name' => 'Doe',
    ]);
```

---

### Keys case restriction support

Four configurators restrict which key case is accepted when mapping input data
to objects or shaped arrays. If a key does not match the expected case, a
mapping error will be raised.

This is useful, for instance, to enforce a consistent naming convention across
an API's input to ensure that a JSON payload only contains `camelCase`,
`snake_case`, `PascalCase` or `kebab-case` keys.

Available configurators:

| Configurator                     | Example      |
|----------------------------------|--------------|
| `new RestrictKeysToCamelCase()`  | `firstName`  |
| `new RestrictKeysToPascalCase()` | `FirstName`  |
| `new RestrictKeysToSnakeCase()`  | `first_name` |
| `new RestrictKeysToKebabCase()`  | `first-name` |

```php
$user = (new \CuyZ\Valinor\MapperBuilder())
    ->configureWith(
        new \CuyZ\Valinor\Mapper\Configurator\RestrictKeysToCamelCase()
    )
    ->mapper()
    ->map(\My\App\User::class, [
        'firstName' => 'John', // Ok
        'last_name' => 'Doe',  // Error
    ]);
```

### Features

* Add HTTP request mapping support ([385f0c](https://github.com/CuyZ/Valinor/commit/385f0ce1ab901f65f428dd2352bed37018c58d82))
* Add configurator support for mapper and normalizer builders ([49dd0a](https://github.com/CuyZ/Valinor/commit/49dd0a20c4d995b6c3e135a91cbc13fa3bf7c943))
* Add mapper configurators to convert keys to camelCase/snake_case ([a92bd3](https://github.com/CuyZ/Valinor/commit/a92bd342459973ef76bbc44d8d5c7c9bb0f1ac24))
* Add mapper configurators to restrict keys cases ([0be7dc](https://github.com/CuyZ/Valinor/commit/0be7dcd4d2eb08f755df733d034ae4ebc4dffcc5))
* Introduce key converters to transform source keys ([bfd4ab](https://github.com/CuyZ/Valinor/commit/bfd4ab10766322bcbde4fe796ed8cab276d4acf1))

### Bug Fixes

* Allow mapping a single value to a list type ([7241a6](https://github.com/CuyZ/Valinor/commit/7241a600087fd96235af416494cc4c316736ac30))
* Disallow duplicate converted keys ([498dcf](https://github.com/CuyZ/Valinor/commit/498dcfcc0d1e562f17473a7b9a2780fddd34bd4a))
* Handle concurrent cache directory race condition ([13f06d](https://github.com/CuyZ/Valinor/commit/13f06d2415be29186df455cbc266368fafe52b0d))
* Properly invalidate cache entries when using `FileWatchingCache` ([d445e4](https://github.com/CuyZ/Valinor/commit/d445e4396ab8cb0ab58e6df30c9e57c93bb88a17))

### Internal


##### Deps

* Update dependencies ([73a1cb](https://github.com/CuyZ/Valinor/commit/73a1cb98c097259143eacdf5b39046629444fe24))
* Update mkdocs dependencies ([67a6b4](https://github.com/CuyZ/Valinor/commit/67a6b4b55ca2d2f73eebd74d1d49d7e9698aa456))
