# Changelog

## v3.0.1 - 2026-04-24

- **FIX**: `has` and `whereHas` on composite relations now generate a subquery that compares every `(localKeys[i], foreignKeys[i])` pair. The previous behavior relied on Eloquent's default `getRelationExistenceQuery`, which built the subquery using the parent's primary key and only the first foreign key, producing invalid SQL on models with a composite primary key.
- **TESTS**: Added coverage for `has` and `whereHas` on composite relations (`hasManyComposite` and `belongsToComposite`).
- **FIX**: Corrected expected counts in preexisting eager loading tests (`assertCount(3, ...)` replaced with `assertCount(2, ...)` to match the records actually created).

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
