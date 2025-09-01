<?php

namespace Pfrug\CompositeKey\Helpers;

use Illuminate\Database\Eloquent\Builder;

class CompositeKeyBuilder extends Builder
{
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
