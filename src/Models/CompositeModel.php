<?php
namespace Pfrug\CompositeKey\Models;

use Illuminate\Database\Eloquent\Model;
use Pfrug\CompositeKey\Traits\HasCompositeKey;

class CompositeModel extends Model
{
    use HasCompositeKey;

    public $incrementing = false;
}
