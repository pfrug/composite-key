# Changelog

## v3.0.0 - 2026-04-21

- **BREAKING**: Removed `HasCompositeRouteKey::COMPOSITE_KEY_SEPARATOR` constant
- **BREAKING**: Default route key separator changed from `:` to `~`
- **NEW**: Configurable route key separator via `config/composite-key.php`
- **NEW**: `CompositeKeyServiceProvider` with `vendor:publish --tag=composite-key-config`
- **NEW**: `COMPOSITE_KEY_SEPARATOR` environment variable support
- **NEW**: Per-model override by redeclaring `getCompositeKeySeparator()`
- **DOCS**: PHPDoc added across traits, helpers, relations, model and service provider
- **FIX**: Missing `use` of `HasCompositeRouteKey` in `CompositeModel`
- **FIX**: Unused `Collection` import in `CompositeRelationBuilder`

### Upgrade notes

- Publish the config or set `COMPOSITE_KEY_SEPARATOR=:` in `.env` to keep the previous `:` separator.
- Replace any reference to `HasCompositeRouteKey::COMPOSITE_KEY_SEPARATOR` with `$this->getCompositeKeySeparator()`.

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
