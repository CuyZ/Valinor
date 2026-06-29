# Common converters examples

Instead of providing converters out-of-the-box, this library focuses on easing
the creation of custom ones. This way, the mapper is not tied up to a
third-party library release-cycle and can be adapted to fit the needs of the
application's business logics.

Below is a list of common features that can inspire or be implemented by
third-party libraries or applications.

!!! info inline end
    These examples are not available out-of-the-box, they can be implemented
    using the library's API and should be adapted to fit the needs of the
    application.

- [Renaming keys](#renaming-keys)
- [Casting scalar values](#casting-scalar-values)
- [Custom datetime format](#custom-datetime-format)
- [Explode string to list](#explode-string-to-list)
- [Array to list](#array-to-list)
- [Json decode](#json-decode)

## Renaming keys

For a global renaming, see [converting source keys chapter]. To rename the key
of a single property, see [mapping a property from a specific key configurator
chapter].

[converting source keys chapter]: convert-input.md#converting-source-keys
[mapping a property from a specific key configurator chapter]: use-provided-mapper-configurators.md#mapping-a-property-from-a-specific-key

## Casting scalar values

Sometimes the input data is not in the expected format for a scalar value.
Converters can be used to convert the input data to the expected type, allowing
the mapper to handle the data correctly.

!!! note
    Scalar value casting can also be globally enabled, [see documentation about
    `MapperBuilder::allowScalarValueCasting()`](../usage/type-strictness-and-flexibility.md#allowing-scalar-value-casting).

=== "Casting to boolean"

    See the [`MapAsBool`](use-provided-mapper-configurators.md#mapasbool) configurator.

=== "Casting to string"

    See the [`MapAsString`](use-provided-mapper-configurators.md#mapasstring) configurator.

=== "Casting to integer"

    See the [`MapAsInt`](use-provided-mapper-configurators.md#mapasint) configurator.

=== "Casting to float"

    See the [`MapAsFloat`](use-provided-mapper-configurators.md#mapasfloat) configurator.

## Custom datetime format

Global datetime format customization can be enabled with the mapper builder, see
[`MapperBuilder::supportDateFormats()`](deal-with-dates.md).

For a more granular control, see [mapping a date from a format configurator
chapter].

[mapping a date from a format configurator chapter]: use-provided-mapper-configurators.md#mapping-a-date-from-a-format

## Explode string to list

See [exploding a string to a list configurator chapter].

[exploding a string to a list configurator chapter]: use-provided-mapper-configurators.md#exploding-a-string-to-a-list

## Array to list

Global array to list conversion can be enabled with the mapper builder, see
[`MapperBuilder::allowNonSequentialList()`](../usage/type-strictness-and-flexibility.md#allowing-non-sequential-lists).

For a more granular control, see [mapping an array to a list configurator
chapter].

[mapping an array to a list configurator chapter]: use-provided-mapper-configurators.md#mapping-an-array-to-a-list

## Json decode

See [decoding a JSON string configurator chapter].

[decoding a JSON string configurator chapter]: use-provided-mapper-configurators.md#decoding-a-json-string
