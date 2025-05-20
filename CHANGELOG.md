# Changelog

## v1.0.0 - 2025-05-20

- Initial public release
- Support for composite primary keys in Eloquent models
- Adds `HasCompositeKey` trait with:
  - Custom `find()` and `findOrFail()` methods
  - Composite-aware `save()` and `delete()`
  - `hasManyComposite()` and `belongsToComposite()` relationships
- Compatible with Laravel 10 and PHP 8.2+
- Includes full PHPUnit test suite with in-memory SQLite database
