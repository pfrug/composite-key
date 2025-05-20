<?php

namespace pfrug\CompositeKey\Helpers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CompositeKeyQuery
{
    public static function forModel(Model $model): Builder
    {
        $query = $model->newQuery();

        foreach (static::getKeyValues($model) as $key => $value) {
            $query->where($key, $value);
        }

        return $query;
    }

    public static function find(string $modelClass, array $values): ?Model
    {
        $model = new $modelClass;
        $keyNames = $model->getCompositeKey();

        if (count($values) !== count($keyNames)) {
            throw new \InvalidArgumentException('Invalid key values count.');
        }

        $query = $modelClass::query();
        foreach ($keyNames as $i => $key) {
            $query->where($key, $values[$i]);
        }

        return $query->first();
    }

    public static function getKeyValues(Model $model): array
    {
        $keys = [];

        foreach ($model->getCompositeKey() as $key) {
            $value = $model->getAttribute($key);
            if (is_null($value)) {
                throw new \RuntimeException("Missing composite key value for: {$key}");
            }
            $keys[$key] = $value;
        }

        return $keys;
    }
}
