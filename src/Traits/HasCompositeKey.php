<?php
namespace Pfrug\CompositeKey\Traits;

use Pfrug\CompositeKey\Helpers\CompositeKeyQuery;
use Pfrug\CompositeKey\Helpers\CompositeRelationBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\ModelNotFoundException;

trait HasCompositeKey
{
    public $incrementing = false;

    public function save(array $options = []): bool
    {
        if (!$this->exists) return parent::save($options);

        $query = CompositeKeyQuery::forModel($this);

        $dirty = $this->getDirty();
        if (count($dirty) > 0) {
            $query->update($dirty);
        }

        return true;
    }

    public function delete(): bool|int|null
    {
        return CompositeKeyQuery::forModel($this)->delete();
    }

    public static function find(string|array $values): ?self
    {
        return CompositeKeyQuery::find(static::class, $values);
    }

    public static function findOrFail(array $values): self
    {
        $model = static::find($values);
        if (!$model) throw new ModelNotFoundException();
        return $model;
    }

    public function hasManyComposite(string $related, array $foreignKeys, array $localKeys): Relation
    {
        return CompositeRelationBuilder::hasMany($this, $related, $foreignKeys, $localKeys);
    }

    public function belongsToComposite(string $related, array $foreignKeys, array $ownerKeys): Relation
    {
        return CompositeRelationBuilder::belongsTo($this, $related, $foreignKeys, $ownerKeys);
    }

    public function getCompositeKey(): array
    {
        return $this->compositeKey ?? [];
    }

}
