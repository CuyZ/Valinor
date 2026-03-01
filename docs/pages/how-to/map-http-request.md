# Mapping an HTTP request

This library provides a way to map an HTTP request to controller action
parameters or object properties, using PHP attributes to declare which part of
the request each parameter comes from.

Three attributes are available:

- `#[FromRoute]` — for parameters extracted from the URL path by a router
- `#[FromQuery]` — for query string parameters
- `#[FromBody]` — for request body values

This gives controllers a clean, type-safe signature without coupling to a
framework's request object, while benefiting from the library's validation and
error handling.

Normal mapping rules apply there: parameters are required unless they have a
default value.

Route and query parameter values coming from an HTTP request are typically
strings. The mapper automatically handles scalar value casting for these
parameters: a string `"42"` will be properly mapped to an `int` parameter.

!!! hint

    The [Valinor Symfony Bundle] provides a native integration with Symfony's
    HTTP Foundation component.

    [Valinor Symfony Bundle]: https://github.com/CuyZ/Valinor-Bundle

## GET request example

Consider an API that lists articles for a given author. The author identifier
comes from the URL path, while filtering and pagination come from the query
string.

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
     * @param non-empty-string $status
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

## POST request example

For a request that carries a body payload, the `#[FromBody]` attribute is used.
Below is an example of a controller that handles posting a comment on an
article.

```php
use CuyZ\Valinor\Mapper\Http\FromBody;
use CuyZ\Valinor\Mapper\Http\FromRoute;
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
        // Comes from the route
        #[FromRoute] int $postId,

        // Both come from body payload
        #[FromBody] string $author,
        #[FromBody] string $content,
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

## Mapping all parameters at once

Instead of mapping individual query parameters or body values to separate
parameters, the `mapAll` option can be used to map all of them at once to a
single parameter. This is useful when working with complex data structures or
when the number of parameters is large.

```php
use CuyZ\Valinor\Mapper\Http\FromQuery;
use CuyZ\Valinor\Mapper\Http\FromRoute;

final readonly class ArticleFilters
{
    public function __construct(
        /** @var non-empsey-string */
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
     * GET /api/authors/{authorId}/articles?status=X&&page=X&limit=X
     */
    public function __invoke(
        #[FromRoute] string $authorId,
        #[FromQuery(mapAll: true)] ArticleFilters $filters,
    ): ResponseInterface { … }
}
```

The same approach works with `#[FromBody(mapAll: true)]` for body values.

## Mapping to an object

Instead of mapping to a callable's arguments, an `HttpRequest` can be mapped
directly to an object. The attributes work the same way on constructor
parameters or promoted properties.

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

## Using PSR-7 requests

An `HttpRequest` instance can be built directly from a [PSR-7]
`ServerRequestInterface`. This is the recommended approach when integrating with
frameworks that use PSR-7.

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

The factory method extracts query parameters from `getQueryParams()` and body
values from `getParsedBody()`. It also passes the original PSR-7 request object
through, so it can be injected into controller parameters if needed (see below).

[PSR-7]: https://www.php-fig.org/psr/psr-7/

## Accessing the original request object

When building an `HttpRequest`, an original request object can be provided. If a
controller parameter's type matches this object, it will be injected
automatically; no attribute is needed.

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

## Enforcing and converting key case

APIs often use a different naming convention than the PHP codebase — for
instance, a JSON payload with `snake_case` keys mapped to `camelCase`
properties. Two families of configurators help with this:

- [RestrictKeysTo*Case] configurators reject keys that do not match the
  expected format (e.g. `snake_case`, `camelCase`), raising a mapping error.
- [ConvertKeysTo*Case] configurators convert input keys to the target format
  before mapping (e.g. `first_name` → `firstName`).

They can be combined so that the restriction validates the original keys and the
conversion remaps them. Both work with HTTP request mapping.

```php
use CuyZ\Valinor\Mapper\Configurator\ConvertKeysToCamelCase;
use CuyZ\Valinor\Mapper\Configurator\RestrictKeysToSnakeCase;
use CuyZ\Valinor\MapperBuilder;

$controllerArguments = (new MapperBuilder())
    ->configureWith(
        new RestrictKeysToSnakeCase(),
        new ConvertKeysToCamelCase(),
    )
    ->argumentsMapper()
    ->mapArguments($controller, $httpRequest);
```

Read the [common mapper configurators chapter] for the full list of available
configurators and detailed usage.

[RestrictKeysTo*Case]: use-provided-mapper-configurators.md#restricting-key-case
[ConvertKeysTo*Case]: use-provided-mapper-configurators.md#converting-key-case
[common mapper configurators chapter]: use-provided-mapper-configurators.md

## Error handling

When the mapping fails — for instance because a required query parameter is
missing or a body value has the wrong type — a `MappingError` is thrown, just
like with regular mapping.

Read [the validation and error handling chapter] for more information.

[the validation and error handling chapter]: ../usage/validation-and-error-handling.md
