<?php

namespace Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Pfrug\CompositeKey\Traits\HasCompositeKey;
use Tests\Models\ShipmentLine;

class ShipmentHeader extends Model
{
    use HasCompositeKey;

    protected $table = 'shipment_headers';
    protected $fillable = ['company_code', 'shipment_number', 'description'];
    protected $compositeKey = ['company_code', 'shipment_number'];

    public $timestamps = false;

    public function lines()
    {
        return $this->hasManyComposite(
            ShipmentLine::class,
            ['company_code', 'shipment_number'],
            ['company_code', 'shipment_number']
        );
    }
}
