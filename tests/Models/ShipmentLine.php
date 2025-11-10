<?php

namespace Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Pfrug\CompositeKey\Traits\HasCompositeKey;
use Tests\Models\ShipmentHeader;
use Tests\Models\ShipmentDetail;

class ShipmentLine extends Model
{
    use HasCompositeKey;

    protected $table = 'shipment_lines';
    protected $fillable = ['company_code', 'shipment_number', 'line_number', 'product_name', 'quantity'];
    protected $compositeKey = ['company_code', 'shipment_number', 'line_number'];

    public $timestamps = false;

    public function header()
    {
        return $this->belongsToComposite(
            ShipmentHeader::class,
            ['company_code', 'shipment_number'],
            ['company_code', 'shipment_number']
        );
    }

    public function details()
    {
        return $this->hasManyComposite(
            ShipmentDetail::class,
            ['company_code', 'shipment_number', 'line_number'],
            ['company_code', 'shipment_number', 'line_number']
        );
    }
}
