<?php
namespace Pfrug\CompositeKey\Helpers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Collection;

class CompositeRelationBuilder
{
    public static function hasMany(Model $model, string $related, array $foreignKeys, array $localKeys): Relation
    {
        $builder = (new $related)->newQuery();

        foreach ($foreignKeys as $i => $foreignKey) {
            $localValue = $model->getAttribute($localKeys[$i]);
            if (is_null($localValue)) {
                return self::emptyRelation($builder, collect(), true);
            }
            $builder->where($foreignKey, $localValue);
        }

        return self::relation($builder, $model, collect(), true);
    }

    public static function belongsTo(Model $model, string $related, array $foreignKeys, array $ownerKeys): Relation
    {
        $builder = (new $related)->newQuery();

        foreach ($foreignKeys as $i => $foreignKey) {
            $value = $model->getAttribute($foreignKey);
            if (is_null($value)) {
                return self::emptyRelation($builder, null, false);
            }
            $builder->where($ownerKeys[$i], $value);
        }

        return self::relation($builder, $model, null, false);
    }

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

    protected static function emptyRelation(Builder $builder, $default, bool $many): Relation
    {
        return self::relation($builder->whereRaw('1 = 0'), new class extends Model {}, $default, $many);
    }
}
