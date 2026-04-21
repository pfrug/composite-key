<?php
namespace Pfrug\CompositeKey\Traits;

use Pfrug\CompositeKey\Helpers\CompositeKeyQuery;
use Pfrug\CompositeKey\Helpers\CompositeRelationBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Pfrug\CompositeKey\Helpers\CompositeKeyBuilder;

/**
 * Adds composite primary key support to an Eloquent model.
 *
 * Eloquent assumes a single primary key column. This trait overrides the
 * persistence, lookup and relation hooks so the model can be identified by
 * the ordered list of columns declared in the `$compositeKey` property of
 * the using class.
 *
 * @property array<int, string> $compositeKey Ordered list of columns that form the composite primary key.
 */
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

    /**
     * Override so updates use every composite key column in the WHERE clause
     * instead of the single PK Eloquent assumes.
     */
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
        if ($this->fireModelEvent('deleting') === false) {
            return false;
        }
        $result = CompositeKeyQuery::forModel($this)->delete();

        if ($result) {
            $this->fireModelEvent('deleted', false);
        }

        return $result;
    }

    /**
     * @param  string|array<int, mixed>  $values  Ordered values matching `$compositeKey`.
     */
    public static function find(string|array $values): ?self
    {
        return CompositeKeyQuery::find(static::class, $values);
    }

    /**
     * @param  array<int, mixed>  $values  Ordered values matching `$compositeKey`.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public static function findOrFail(array $values): self
    {
        $model = static::find($values);
        if (!$model) throw new ModelNotFoundException();
        return $model;
    }

    /**
     * One-to-many relation joined by matching ordered pairs of columns.
     *
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $related
     * @param  array<int, string>  $foreignKeys  Columns on the related table.
     * @param  array<int, string>  $localKeys    Columns on this model, in matching order.
     */
    public function hasManyComposite(string $related, array $foreignKeys, array $localKeys): Relation
    {
        return CompositeRelationBuilder::hasMany($this, $related, $foreignKeys, $localKeys);
    }

    /**
     * Inverse relation joined by matching ordered pairs of columns.
     *
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $related
     * @param  array<int, string>  $foreignKeys  Columns on this model.
     * @param  array<int, string>  $ownerKeys    Columns on the related (owner) table, in matching order.
     */
    public function belongsToComposite(string $related, array $foreignKeys, array $ownerKeys): Relation
    {
        return CompositeRelationBuilder::belongsTo($this, $related, $foreignKeys, $ownerKeys);
    }

    /**
     * @return array<int, string>
     */
    public function getCompositeKey(): array
    {
        return $this->compositeKey ?? [];
    }

}
