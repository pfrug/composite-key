<?php
namespace Pfrug\CompositeKey\Traits;

use Pfrug\CompositeKey\Helpers\CompositeKeyQuery;
use Pfrug\CompositeKey\Helpers\CompositeRelationBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Pfrug\CompositeKey\Helpers\CompositeKeyBuilder;

trait HasCompositeKey
{
    public function newEloquentBuilder($query)
    {
        return new CompositeKeyBuilder($query);
    }

    public function getIncrementing(): bool
    {
        return false;
    }

    public function save(array $options = []): bool
    {
        if (!$this->exists) {
            return parent::save($options);
        }

        if ($this->fireModelEvent('saving') === false) {
            return false;
        }

        if ($this->fireModelEvent('updating') === false) {
            return false;
        }

        $result = $this->performCompositeUpdate();

        if ($result) {
            $this->fireModelEvent('updated', false);
            $this->fireModelEvent('saved', false);
        }

        return $result;
    }

    protected function performCompositeUpdate(): bool
    {
        $dirty = $this->getDirty();

        if (empty($dirty)) {
            return false;
        }

        if ($this->timestamps) {
            $this->updateTimestamps();
            $dirty = $this->getDirty();
        }

        $result = CompositeKeyQuery::forModel($this)->update($dirty);

        if ($result > 0) {
            $this->syncChanges();
            return true;
        }

        return false;
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
