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

More specific validation should be done in the constructor of the value object,
by throwing an exception if something is wrong with the given data. A good
practice would be to use lightweight validation tools like [Webmozart Assert].

When the mapping fails, the exception gives access to the root node. This
recursive object allows retrieving all needed information through the whole
mapping tree: path, values, types and messages, including the issues that caused
the exception.

```php
final class SomeClass
{
    public function __construct(private string $someValue)
    {
        Assert::startsWith($someValue, 'foo_');
    }
}

try {
   (new \CuyZ\Valinor\MapperBuilder())
        ->mapper()
        ->map(
            SomeClass::class,
            ['someValue' => 'bar_baz']
        );
} catch (\CuyZ\Valinor\Mapper\MappingError $error) {
    // Get flatten list of all messages through the whole nodes tree
    $node = $error->node();
    $messages = new \CuyZ\Valinor\Mapper\Tree\Message\MessagesFlattener($node);
    
    // If only errors are wanted, they can be filtered
    $errorMessages = $messages->errors();

    // Should print something similar to:
    // > Expected a value to start with "foo_". Got: "bar_baz"
    foreach ($errorMessages as $message) {
        echo $message;
    }
}
```
