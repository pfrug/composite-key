<?php

namespace Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Pfrug\CompositeKey\Traits\HasCompositeKey;
use Tests\Models\ShipmentHeader;

class ShipmentLine extends Model
{
    use HasCompositeKey;

    protected $table = 'shipment_lines';
    protected $fillable = ['company_code', 'shipment_number', 'line_number', 'product_name', 'quantity'];
    protected array $compositeKey = ['company_code', 'shipment_number', 'line_number'];

    public $timestamps = false;

    public function header()
    {
        return $this->belongsToComposite(
            ShipmentHeader::class,
            ['company_code', 'shipment_number'],
            ['company_code', 'shipment_number']
        );
    }
}
