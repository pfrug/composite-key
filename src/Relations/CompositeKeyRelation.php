<?php
namespace Pfrug\CompositeKey\Relations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;

class CompositeKeyRelation extends Relation
{
    protected mixed $default;
    protected bool $many;
    protected array $foreignKeys;
    protected array $localKeys;

    public function __construct(Builder $query, Model $parent, mixed $default, bool $many, array $foreignKeys, array $localKeys)
    {
        $this->default = $default;
        $this->many = $many;
        $this->foreignKeys = $foreignKeys;
        $this->localKeys = $localKeys;

        parent::__construct($query, $parent);
    }

    public function addConstraints(): void
    {
        if (static::$constraints === false) return;

        foreach ($this->localKeys as $i => $localKey) {
            $val = $this->parent->getAttribute($localKey);
            if ($val === null) {
                $this->query->whereRaw('1 = 0');
                return;
            }
            $this->query->where($this->foreignKeys[$i], $val);
        }
    }

    public function addEagerConstraints(array $models): void
    {
        if (count($models) === 0) return;

        $grouped = [];

        foreach ($models as $model) {
            $key = implode('|', array_map(fn($k) => (string) $model->getAttribute($k), $this->localKeys));
            $grouped[$key] = array_map(fn($k) => $model->getAttribute($k), $this->localKeys);
        }

        $this->query->where(function ($query) use ($grouped) {
            foreach ($grouped as $parts) {
                $query->orWhere(function ($q) use ($parts) {
                    foreach ($this->foreignKeys as $i => $foreignKey) {
                        $q->where($foreignKey, '=', $parts[$i]);
                    }
                });
            }
        });
    }

    public function initRelation(array $models, $relation): array
    {
        foreach ($models as $model) {
            $model->setRelation($relation, $this->many ? collect() : null);
        }

        return $models;
    }

    public function match(array $models, Collection $results, $relation): array
    {
        $dictionary = [];

        foreach ($results as $result) {
            $key = implode('|', array_map(fn($k) => (string) $result->getAttribute($k), $this->foreignKeys));
            $dictionary[$key][] = $result;
        }

        foreach ($models as $model) {
            $key = implode('|', array_map(fn($k) => (string) $model->getAttribute($k), $this->localKeys));
            $related = $dictionary[$key] ?? ($this->many ? [] : null);
            $model->setRelation($relation, $this->many ? collect($related) : ($related[0] ?? null));
        }

        return $models;
    }

    public function getResults(): mixed
    {
        return $this->many ? $this->query->get() : $this->query->first();
    }

    /**
     * Get the key used for comparison against the parent key in "has" queries.
     *
     * @return string|null
     */
    public function getExistenceCompareKey()
    {
        return $this->getQualifiedForeignKeyName();
    }

    /**
     * Get the foreign key for the relationship.
     *
     * For composite relationships, this method returns the first defined foreign key
     * to maintain compatibility with Eloquent "has" queries.
     *
     * @return string|null
     */
    public function getQualifiedForeignKeyName()
    {
        return $this->foreignKeys[0] ?? null;
    }
}
