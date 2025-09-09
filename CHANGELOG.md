# Changelog

## v2.0.0 - 2025-09-05

- **NEW**: Full eager loading support for composite key relationships
- Composite relationships (`hasManyComposite` and `belongsToComposite`) now support `with()` and `load()` methods
- Enhanced relationship loading performance with proper query optimization
- Updated documentation with eager loading examples

## v1.0.0 - 2025-05-20

- Initial public release
- Support for composite primary keys in Eloquent models
- Adds `HasCompositeKey` trait with:
  - Custom `find()` and `findOrFail()` methods
  - Composite-aware `save()` and `delete()`
  - `hasManyComposite()` and `belongsToComposite()` relationships
- Compatible with Laravel 10 and PHP 8.2+
- Includes full PHPUnit test suite with in-memory SQLite database
