<?php
namespace Pfrug\CompositeKey\Helpers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Collection;
use Pfrug\CompositeKey\Relations\CompositeKeyRelation;

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

        return self::relation($builder, $model, collect(), true, $foreignKeys, $localKeys);
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

        return self::relation($builder, $model, null, false, $foreignKeys, $ownerKeys);
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
    protected static function relation(Builder $query, Model $parent, $default, bool $many, array $foreignKeys = [], array $localKeys = []): Relation
    {
        return new CompositeKeyRelation($query, $parent, $default, $many, $foreignKeys, $localKeys);
    }
}
