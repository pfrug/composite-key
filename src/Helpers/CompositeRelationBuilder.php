<?php
namespace Pfrug\CompositeKey\Helpers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Collection;

class CompositeRelationBuilder
{
    /**
     * Creates a has-many relation using multiple key pairs.
     *
     * @param Model $model The parent model instance.
     * @param class-string<Model> $related The related model class.
     * @param array $foreignKeys Foreign keys in the related model.
     * @param array $localKeys Local keys in the parent model.
     * @return Relation
     */
    public static function hasMany(Model $model, string $related, array $foreignKeys, array $localKeys): Relation
    {
        $builder = (new $related)->newQuery();

        foreach ($foreignKeys as $i => $foreignKey) {
            $localValue = $model->getAttribute($localKeys[$i]);
            if ($localValue === null) {
                return self::emptyRelation($builder, collect(), true);
            }
            $builder->where($foreignKey, $localValue);
        }

        return self::relation($builder, $model, collect(), true);
    }

    /**
     * Creates a belongs-to relation using multiple key pairs.
     *
     * @param Model $model The child model instance.
     * @param class-string<Model> $related The parent model class.
     * @param array $foreignKeys Foreign keys in the child.
     * @param array $ownerKeys Primary keys in the related (parent) model.
     * @return Relation
     */
    public static function belongsTo(Model $model, string $related, array $foreignKeys, array $ownerKeys): Relation
    {
        $builder = (new $related)->newQuery();

        foreach ($foreignKeys as $i => $foreignKey) {
            $value = $model->getAttribute($foreignKey);
            if ($value === null) {
                return self::emptyRelation($builder, null, false);
            }
            $builder->where($ownerKeys[$i], $value);
        }

        return self::relation($builder, $model, null, false);
    }

    /**
     * Builds an anonymous Relation instance to simulate hasMany/belongsTo behavior
     * for composite key relationships.
     *
     * @param Builder $query The base query builder for the related model.
     * @param Model $parent The parent or child model, depending on context.
     * @param mixed $default Default value to assign if no match is found.
     * @param bool $many Indicates if the relation is one-to-many.
     * @return Relation
     */
    protected static function relation(Builder $query, Model $parent, $default, bool $many): Relation
    {
        return new class($query, $parent, $default, $many) extends Relation {
            public function __construct($query, $parent, protected $default, protected $many)
            {
                parent::__construct($query, $parent);
            }

            public function addConstraints() {}
            public function addEagerConstraints(array $models) {}

            public function initRelation(array $models, $relation)
            {
                foreach ($models as $model) {
                    $model->setRelation($relation, $this->many ? collect() : null);
                }
                return $models;
            }

            public function match(array $models, Collection $results, $relation)
            {
                return $models;
            }

            public function getResults()
            {
                return $this->many ? $this->query->get() : $this->query->first();
            }
        };
    }

    /**
     * Returns an always-empty relation. Used when key values are null or missing.
     */
    protected static function emptyRelation(Builder $builder, $default, bool $many): Relation
    {
        return self::relation($builder->whereRaw('1 = 0'), new class extends Model {}, $default, $many);
    }
}
