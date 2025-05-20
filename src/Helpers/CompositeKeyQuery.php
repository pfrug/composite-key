<?php

namespace Pfrug\CompositeKey\Helpers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CompositeKeyQuery
{
    /**
     * Builds a query for the given model using its composite key values.
     *
     * @param Model $model
     * @return Builder
     */
    public static function forModel(Model $model): Builder
    {
        $query = $model->newQuery();

        foreach (static::getKeyValues($model) as $key => $value) {
            $query->where($key, $value);
        }

        return $query;
    }

    /**
     * Finds a model instance using the given composite key values.
     *
     * @param class-string<Model> $modelClass
     * @param array $values
     * @return Model|null
     *
     * @throws \InvalidArgumentException If the number of values doesn't match the composite key.
     */
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

    /**
     * Returns the current composite key values from the given model.
     *
     * @param Model $model
     * @return array<string, mixed>
     *
     * @throws \RuntimeException If any composite key value is null.
     */
    public static function getKeyValues(Model $model): array
    {
        $keys = [];

        foreach ($model->getCompositeKey() as $key) {
            $value = $model->getAttribute($key);
            if ($value === null) {
                throw new \RuntimeException("Missing composite key value for: {$key}");
            }
            $keys[$key] = $value;
        }

        return $keys;
    }
}
