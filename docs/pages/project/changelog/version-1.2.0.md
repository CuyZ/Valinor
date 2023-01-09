# Changelog 1.2.0 — 9th of January 2023

!!! info inline end "[See release on GitHub]"
    [See release on GitHub]: https://github.com/CuyZ/Valinor/releases/tag/1.2.0

## Notable changes

**Handle single property/constructor argument with array input**

It is now possible, again, to use an array for a single node (single class
property or single constructor argument), if this array has one value with a key
matching the argument/property name.

This is a revert of a change that was introduced in a previous commit: see hash
72cba320f582c7cda63865880a1cbf7ea292d2b1

## Features

* Allow usage of array input for single node during mapping ([686186](https://github.com/CuyZ/Valinor/commit/6861862d80c74bc26f54db9f55fd2e8adb29f3df))

## Bug Fixes

* Do not re-validate single node with existing error ([daaaac](https://github.com/CuyZ/Valinor/commit/daaaac96f28a03dadfcbc6311b49dfb0c76141c1))

## Other

* Remove unneeded internal check ([86cca5](https://github.com/CuyZ/Valinor/commit/86cca5280aabd07c96b7c98dd2d4e2d310420650))
* Remove unneeded internal checks and exceptions ([157723](https://github.com/CuyZ/Valinor/commit/1577233ac9dce444af61bb482f5a744e38eaf739))
