<?php

namespace Pfrug\CompositeKey\Traits;

use Illuminate\Database\Eloquent\ModelNotFoundException;

trait HasCompositeRouteKey
{
    protected const COMPOSITE_KEY_SEPARATOR = ':';

    public function getRouteKey()
    {
        return implode(self::COMPOSITE_KEY_SEPARATOR, array_map(
            fn($key) => $this->getAttribute(strtolower($key)),
            $this->getCompositeKey()
        ));
    }

    public function resolveRouteBinding($value, $field = null)
    {
        $keyValues = $this->decodeCompositeKey($value);

        return static::find(array_values($keyValues));
    }

    protected function decodeCompositeKey(string $value): array
    {
        $keys = $this->getCompositeKey();
        $parts = explode(self::COMPOSITE_KEY_SEPARATOR, $value);

        if (count($parts) !== count($keys)) {
            throw new ModelNotFoundException('Invalid composite key.');
        }

        return array_combine($keys, $parts);
    }
}
