# Validation

The source given to a mapper can never be trusted, this is actually the very
goal of this library: transforming an unstructured input to a well-defined
object structure. If the mapper cannot guess how to cast a certain value, it
means that it is not able to guarantee the validity of the desired object thus
it will fail.

Any issue encountered during the mapping will add an error to an upstream
exception of type `\CuyZ\Valinor\Mapper\MappingError`. It is therefore always
recommended wrapping the mapping function call with a try/catch statement and
handle the error properly.

When the mapping fails, the exception gives access to the root node. This
recursive object allows retrieving all needed information through the whole
mapping tree: path, values, types and messages, including the issues that caused
the exception.

```php
try {
   (new \CuyZ\Valinor\MapperBuilder())
        ->mapper()
        ->map(SomeClass::class, [/* … */ ]);
} catch (\CuyZ\Valinor\Mapper\MappingError $error) {
    // Get flatten list of all messages through the whole nodes tree
    $node = $error->node();
    $messages = new \CuyZ\Valinor\Mapper\Tree\Message\MessagesFlattener($node);
    
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

An exception that implements the interface
`\CuyZ\Valinor\Mapper\Tree\Message\Message` can be thrown.

```php
final class SomeClass
{
    public function __construct(private string $value)
    {
        if ($this->value === 'foo') {
            throw new SomeException();
        }
    }
}

final class SomeException extends DomainException implements \CuyZ\Valinor\Mapper\Tree\Message\Message
{
    public function __construct()
    {
        parent::__construct(
            'Some custom error message.', 
            1656081053 // A unique code that can help to identify the error
        );
    }
}

try {
   (new \CuyZ\Valinor\MapperBuilder())->mapper()->map(SomeClass::class, 'foo');
} catch (\CuyZ\Valinor\Mapper\MappingError $exception) {
    // Should print:
    // > Some custom error message.
    echo $exception->node()->messages()[0];
}
```

### 2. Use provided message class

The built-in class `\CuyZ\Valinor\Mapper\Tree\Message\ThrowableMessage` can be
thrown.

```php
final class SomeClass
{
    public function __construct(private string $value)
    {
        if ($this->value === 'foo') {
            throw \CuyZ\Valinor\Mapper\Tree\Message\ThrowableMessage::new(
                'Some custom error message.',
                'some_code'
            );
        }
    }
}

try {
   (new \CuyZ\Valinor\MapperBuilder())->mapper()->map(SomeClass::class, 'foo');
} catch (\CuyZ\Valinor\Mapper\MappingError $exception) {
    // Should print:
    // > Some custom error message.
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
                return \CuyZ\Valinor\Mapper\Tree\Message\ThrowableMessage::from($exception);
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
