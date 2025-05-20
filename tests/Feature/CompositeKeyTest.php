<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\Models\ShipmentHeader;
use Tests\Models\ShipmentLine;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CompositeKeyTest extends TestCase
{
    public function test_find_by_composite_key()
    {
        ShipmentHeader::create([
            'company_code' => 'ACME',
            'shipment_number' => 'SHP001',
            'description' => 'Test shipment'
        ]);

        $header = ShipmentHeader::find(['ACME', 'SHP001']);

        $this->assertNotNull($header);
        $this->assertEquals('Test shipment', $header->description);
    }

    public function test_find_or_fail_by_composite_key()
    {
        $this->expectException(ModelNotFoundException::class);

        ShipmentHeader::findOrFail(['NOPE', '404']);
    }

    public function test_save_updates_existing_model()
    {
        $header = ShipmentHeader::create([
            'company_code' => 'XYZ',
            'shipment_number' => 'SHP002',
            'description' => 'Before'
        ]);

        $header->description = 'After';
        $header->save();

        $fresh = ShipmentHeader::find(['XYZ', 'SHP002']);
        $this->assertEquals('After', $fresh->description);
    }

    public function test_delete_by_composite_key()
    {
        $header = ShipmentHeader::create([
            'company_code' => 'DEL',
            'shipment_number' => 'SHP003',
            'description' => 'To delete'
        ]);

        $header->delete();

        $this->assertNull(ShipmentHeader::find(['DEL', 'SHP003']));
    }

    public function test_has_many_composite_relationship()
    {
        $header = ShipmentHeader::create([
            'company_code' => 'REL',
            'shipment_number' => 'SHP004',
            'description' => 'Header with lines'
        ]);

        ShipmentLine::create([
            'company_code' => 'REL',
            'shipment_number' => 'SHP004',
            'line_number' => 1,
            'product_name' => 'Widget',
            'quantity' => 10
        ]);

        $lines = $header->lines;

        $this->assertCount(1, $lines);
        $this->assertEquals('Widget', $lines[0]->product_name);
    }

    public function test_belongs_to_composite_relationship()
    {
        ShipmentHeader::create([
            'company_code' => 'REL',
            'shipment_number' => 'SHP005',
            'description' => 'Parent'
        ]);

        $line = ShipmentLine::create([
            'company_code' => 'REL',
            'shipment_number' => 'SHP005',
            'line_number' => 1,
            'product_name' => 'Gadget',
            'quantity' => 5
        ]);

        $header = $line->header;

        $this->assertNotNull($header);
        $this->assertEquals('REL', $header->company_code);
        $this->assertEquals('SHP005', $header->shipment_number);
    }
}
