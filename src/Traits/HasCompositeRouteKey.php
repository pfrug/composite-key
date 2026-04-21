<?php

namespace Pfrug\CompositeKey\Traits;

use Illuminate\Database\Eloquent\ModelNotFoundException;

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
