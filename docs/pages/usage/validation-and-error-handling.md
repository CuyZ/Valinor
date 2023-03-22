# Validation and error handling

The source given to a mapper can never be trusted, this is actually the very
goal of this library: transforming an unstructured input to a well-defined
object structure. If a value has an invalid type, or if the mapper cannot cast
it properly (in flexible mode), it means that it is not able to guarantee the
validity of the desired object thus it will fail.

Any issue encountered during the mapping will add an error to an upstream
exception of type `\CuyZ\Valinor\Mapper\MappingError`. It is therefore always
recommended wrapping the mapping function call with a try/catch statement and
handle the error properly.

When the mapping fails, the exception gives access to the root node. This
recursive object allows retrieving all needed information through the whole
mapping tree: path, values, types and messages, including the issues that caused
the exception.

Node messages can be customized and iterated through with the usage of the class 
`\CuyZ\Valinor\Mapper\Tree\Message\Messages`.

```php
try {
    (new \CuyZ\Valinor\MapperBuilder())
        ->mapper()
        ->map(SomeClass::class, [/* … */ ]);
} catch (\CuyZ\Valinor\Mapper\MappingError $error) {
    // Get flatten list of all messages through the whole nodes tree
    $messages = \CuyZ\Valinor\Mapper\Tree\Message\Messages::flattenFromNode(
        $error->node()
    );

    // Formatters can be added and will be applied on all messages
    $messages = $messages->formatWith(
        new \CuyZ\Valinor\Mapper\Tree\Message\Formatter\MessageMapFormatter([
            // …
        ]),
        (new \CuyZ\Valinor\Mapper\Tree\Message\Formatter\TranslationMessageFormatter())
            ->withTranslations([
                // …
            ])
    );

    // If only errors are wanted, they can be filtered
    $errorMessages = $messages->errors();

    foreach ($errorMessages as $message) {
        echo $message;
    }
}
```

## Custom exception messages

More specific validation should be done in the constructor of the object, by
throwing an exception if something is wrong with the given data.

For security reasons, exceptions thrown in a constructor will not be caught by
the mapper, unless one of the three options below is used.

### 1. Custom exception classes

An exception that implements `\CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage`
can be thrown. The body can contain placeholders, see [message customization 
chapter] for more information.

If more parameters can be provided, the exception can also implement the 
interface `\CuyZ\Valinor\Mapper\Tree\Message\HasParameters` that returns a list
of string values, using keys as parameters names.

To help identifying an error, a unique code can be provided by implementing the 
interface `CuyZ\Valinor\Mapper\Tree\Message\HasCode`.

```php
final class SomeClass
{
    public function __construct(private string $value)
    {
        if ($this->value === 'foo') {
            throw new SomeException('some custom parameter');
        }
    }
}

use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\HasCode;
use CuyZ\Valinor\Mapper\Tree\Message\HasParameters;

final class SomeException extends DomainException implements ErrorMessage, HasParameters, HasCode
{
    private string $someParameter;

    public function __construct(string $someParameter)
    {
        parent::__construct();

        $this->someParameter = $someParameter;
    }

    public function body() : string
    {
        return 'Some custom message / {some_parameter} / {source_value}';
    }

    public function parameters(): array
    {
        return [
            'some_parameter' => $this->someParameter,
        ];
    }

    public function code() : string
    {
        // A unique code that can help to identify the error
        return 'some_unique_code';
    }
}

try {
    (new \CuyZ\Valinor\MapperBuilder())->mapper()->map(SomeClass::class, 'foo');
} catch (\CuyZ\Valinor\Mapper\MappingError $exception) {
    // Should print:
    // Some custom message / some custom parameter / 'foo'
    echo $exception->node()->messages()[0];
}
```

### 2. Use provided message builder

The utility class `\CuyZ\Valinor\Mapper\Tree\Message\MessageBuilder` can be used
to build a message.

```php
final class SomeClass
{
    public function __construct(private string $value)
    {
        if (str_starts_with($this->value, 'foo_')) {
            throw \CuyZ\Valinor\Mapper\Tree\Message\MessageBuilder::newError(
                'Some custom error message: {value}.'
            )
            ->withCode('some_code')
            ->withParameter('value', $this->value)
            ->build();
        }
    }
}

try {
    (new \CuyZ\Valinor\MapperBuilder())->mapper()->map(
        SomeClass::class, 'foo_bar'
    );
} catch (\CuyZ\Valinor\Mapper\MappingError $exception) {
    // Should print:
    // > Some custom error message: foo_bar.
    echo $exception->node()->messages()[0];
}
```

### 3. Allow third party exceptions

It is possible to set up a list of exceptions that can be caught by the mapper,
for instance when using lightweight validation tools like [Webmozart Assert].

It is advised to use this feature with caution: userland exceptions may contain
sensible information — for instance an SQL exception showing a part of a query
should never be allowed. Therefore, only an exhaustive list of carefully chosen
exceptions should be filtered.

```php
final class SomeClass
{
    public function __construct(private string $value)
    {
        \Webmozart\Assert\Assert::startsWith($value, 'foo_');
    }
}

try {
    (new \CuyZ\Valinor\MapperBuilder())
        ->filterExceptions(function (Throwable $exception) {
            if ($exception instanceof \Webmozart\Assert\InvalidArgumentException) {
                return \CuyZ\Valinor\Mapper\Tree\Message\MessageBuilder::from($exception);
            } 
            
            // If the exception should not be caught by this library, it
            // must be thrown again.
            throw $exception;
        })
        ->mapper()
        ->map(SomeClass::class, 'bar_baz');
} catch (\CuyZ\Valinor\Mapper\MappingError $exception) {
    // Should print something similar to:
    // > Expected a value to start with "foo_". Got: "bar_baz"
    echo $exception->node()->messages()[0];
}
```

[message customization chapter]: ../how-to/customize-error-messages.md
