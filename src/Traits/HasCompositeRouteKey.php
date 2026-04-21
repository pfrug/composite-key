<?php

namespace Pfrug\CompositeKey\Traits;

use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Enables implicit route-model binding for models with a composite primary key.
 *
 * The composite key values are joined into a single URL segment using the
 * separator from `config('composite-key.separator')`, and decoded back on
 * resolution. Must be used together with {@see HasCompositeKey}.
 */
trait HasCompositeRouteKey
{
    public function getRouteKey()
    {
        return implode($this->getCompositeKeySeparator(), array_map(
            fn($key) => $this->getAttribute($this->usesLowercaseKeys() ? strtolower($key) : $key),
            $this->getCompositeKey()
        ));
    }

    protected function getCompositeKeySeparator(): string
    {
        return config('composite-key.separator', '~');
    }

    /**
     * Some drivers (e.g. Oracle) return column names lowercased, which forces
     * attribute access to use lowercase keys.
     */
    protected function usesLowercaseKeys(): bool
    {
        $connection = $this->getConnectionName() ?? config('database.default');

        return (bool) config("database.connections.{$connection}.lowercase_keys");
    }

    public function resolveRouteBinding($value, $field = null)
    {
        $keyValues = $this->decodeCompositeKey($value);

        return static::find(array_values($keyValues));
    }

    /**
     * @return array<string, string>
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException When the
     *         decoded parts do not match the composite key length.
     */
    protected function decodeCompositeKey(string $value): array
    {
        $keys = $this->getCompositeKey();
        $parts = explode($this->getCompositeKeySeparator(), $value);

        if (count($parts) !== count($keys)) {
            throw new ModelNotFoundException('Invalid composite key.');
        }

        return array_combine($keys, $parts);
    }
}
