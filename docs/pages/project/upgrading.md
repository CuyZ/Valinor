# Upgrading Valinor

## Upgrade from 1.x to 2.x

### Changes to messages/errors handling

Some changes have been made to the way messages and errors are handled.

It is now easier to fetch messages when error(s) occur during mapping:

```php
try {
    (new \CuyZ\Valinor\MapperBuilder())->mapper()->map(/* … */);
} catch (\CuyZ\Valinor\Mapper\MappingError $error) {
    // Before (1.x):
    $messages = \CuyZ\Valinor\Mapper\Tree\Message\Messages::flattenFromNode(
        $error->node()
    );
    
    // After (2.x):
    $messages = $error->messages();
}
```

Some methods to access information from the message have been moved:

```php
// Before (1.x):
$name = $message->node()->name();
$path = $message->node()->path();
$type = $message->node()->type();
$type = $message->node()->sourceValue();

// After (2.x):
$name = $message->name();
$path = $message->path();
$type = $message->type();
$type = $message->sourceValue();
```

### Full list of breaking changes

- Removed `\CuyZ\Valinor\Mapper\MappingError::node()`
    * Use `\CuyZ\Valinor\Mapper\MappingError::messages()` instead
- Removed `\CuyZ\Valinor\Mapper\Tree\Node`
- Removed `\CuyZ\Valinor\Mapper\Tree\NodeTraverser`
- Removed `\CuyZ\Valinor\Mapper\Tree\Message\Messages::flattenFromNode()`
- Removed `\CuyZ\Valinor\Mapper\Tree\Message\NodeMessage::node()`
    * Use the following methods instead:
        - `\CuyZ\Valinor\Mapper\Tree\Message\NodeMessage::name()`
        - `\CuyZ\Valinor\Mapper\Tree\Message\NodeMessage::path()`
        - `\CuyZ\Valinor\Mapper\Tree\Message\NodeMessage::type()`
        - `\CuyZ\Valinor\Mapper\Tree\Message\NodeMessage::sourceValue()`
- Changed `\CuyZ\Valinor\Mapper\Tree\Message\NodeMessage::__construct()` signature
