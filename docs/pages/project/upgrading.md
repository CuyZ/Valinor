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

### Changes to messages codes

Codes have been changed for all messages that can be thrown during mapping. They
now are way more expressive. Here is the exhaustive list of changes and their
replacements:

- `1642183169` → `cannot_find_object_builder`
- `1632903281` → `invalid_source`
- `1607027306` → `cannot_resolve_type_from_union`
- `1654273010` → `invalid_list_key`
- `1630678334` → `invalid_value`
- `1630946163` → `invalid_array_key`
- `1655449641` → `missing_value`
- `1736015505` → `value_is_empty_array`
- `1736015714` → `value_is_empty_list`
- `1710263908` → `value_is_not_null`
- `1618739163` → `value_is_not_iterable`
- `1710262975` → `too_many_resolved_types_from_union`
- `1655117782` → `unexpected_keys`
- `1630686564` → `cannot_parse_datetime_format`

Note that a lot of error messages related to invalid scalar values mapping now
have a specific code, where it used to be `unknown`.

### Removed classes and interfaces

The following classes/interfaces were part of the initial release but were
unused by the library. Although no one should have used them, they were part of
the public API so removing them is a breaking change.

- `\CuyZ\Valinor\Mapper\Source\IdentifiableSource`

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
- Removed `\CuyZ\Valinor\Mapper\Source\IdentifiableSource`
