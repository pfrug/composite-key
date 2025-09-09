<?php

namespace Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Pfrug\CompositeKey\Traits\HasCompositeKey;
use Tests\Models\ShipmentLine;

class ShipmentDetail extends Model
{
    use HasCompositeKey;

    protected $table = 'shipment_details';
    protected $fillable = ['company_code', 'shipment_number', 'line_number', 'detail_sequence', 'detail_description', 'weight'];
    protected array $compositeKey = ['company_code', 'shipment_number', 'line_number', 'detail_sequence'];

    public $timestamps = false;

    public function line()
    {
        return $this->belongsToComposite(
            ShipmentLine::class,
            ['company_code', 'shipment_number', 'line_number'],
            ['company_code', 'shipment_number', 'line_number']
        );
    }
}