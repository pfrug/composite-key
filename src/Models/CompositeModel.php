<?php
namespace Pfrug\CompositeKey\Models;

use Illuminate\Database\Eloquent\Model;
use Pfrug\CompositeKey\Traits\HasCompositeKey;
use Pfrug\CompositeKey\Traits\HasCompositeRouteKey;

/**
 * Base Eloquent model with composite primary key support out of the box.
 *
 * Convenience base class for models that only need the default composite-key
 * behavior. Auto-increment and timestamps are disabled because composite keys
 * are assigned explicitly and the trait's update path assumes they are.
 *
 * Subclasses must declare the `$compositeKey` property with the ordered list
 * of columns that form the primary key.
 */
class CompositeModel extends Model
{
    use HasCompositeKey;
    use HasCompositeRouteKey;

    public $incrementing = false;
    public $timestamps = false;
}
