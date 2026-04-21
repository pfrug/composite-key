<?php

namespace Pfrug\CompositeKey\Helpers;

use Illuminate\Database\Eloquent\Builder;

/**
 * Eloquent builder that resolves `find()` lookups using every column of the
 * model's composite key instead of the single PK Eloquent assumes.
 *
 * Installed on the model through {@see \Pfrug\CompositeKey\Traits\HasCompositeKey::newEloquentBuilder()}.
 */
class CompositeKeyBuilder extends Builder
{
    /**
     * @param  array<int, mixed>  $values   Ordered values matching the model's composite key.
     * @param  array<int, string>  $columns
     * @return \Illuminate\Database\Eloquent\Model|null
     *
     * @throws \InvalidArgumentException When `$values` is not an array or its
     *         length does not match the composite key definition.
     */
    public function find($values, $columns = ['*'])
    {
        $model = $this->getModel();
        $keyNames = $model->getCompositeKey();

        if (!is_array($values)) {
            throw new \InvalidArgumentException('Composite key values must be an array.');
        }

        if (count($keyNames) !== count($values)) {
            throw new \InvalidArgumentException('Mismatched composite key parts.');
        }

        foreach ($keyNames as $i => $key) {
            $this->where($key, $values[$i]);
        }

        return $this->first($columns);
    }

}
