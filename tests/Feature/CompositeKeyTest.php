<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\Models\ShipmentHeader;
use Tests\Models\ShipmentLine;
use Tests\Models\ShipmentDetail;
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

    public function test_eager_loading_has_many_composite_relationship()
    {
        $header1 = ShipmentHeader::create([
            'company_code' => 'EAGER1',
            'shipment_number' => 'SHP001',
            'description' => 'Header 1'
        ]);

        $header2 = ShipmentHeader::create([
            'company_code' => 'EAGER2',
            'shipment_number' => 'SHP002',
            'description' => 'Header 2'
        ]);

        ShipmentLine::create([
            'company_code' => 'EAGER1',
            'shipment_number' => 'SHP001',
            'line_number' => 1,
            'product_name' => 'Product A',
            'quantity' => 10
        ]);

        ShipmentLine::create([
            'company_code' => 'EAGER1',
            'shipment_number' => 'SHP001',
            'line_number' => 2,
            'product_name' => 'Product B',
            'quantity' => 20
        ]);

        ShipmentLine::create([
            'company_code' => 'EAGER2',
            'shipment_number' => 'SHP002',
            'line_number' => 1,
            'product_name' => 'Product C',
            'quantity' => 30
        ]);

        $headers = ShipmentHeader::with('lines')->get();

        $this->assertCount(3, $headers);

        $header1Loaded = $headers->where('company_code', 'EAGER1')->first();
        $header2Loaded = $headers->where('company_code', 'EAGER2')->first();

        $this->assertCount(2, $header1Loaded->lines);
        $this->assertCount(1, $header2Loaded->lines);

        $this->assertEquals('Product A', $header1Loaded->lines[0]->product_name);
        $this->assertEquals('Product B', $header1Loaded->lines[1]->product_name);
        $this->assertEquals('Product C', $header2Loaded->lines[0]->product_name);
    }

    public function test_eager_loading_belongs_to_composite_relationship()
    {
        ShipmentHeader::create([
            'company_code' => 'PARENT1',
            'shipment_number' => 'SHP001',
            'description' => 'Parent Header 1'
        ]);

        ShipmentHeader::create([
            'company_code' => 'PARENT2',
            'shipment_number' => 'SHP002',
            'description' => 'Parent Header 2'
        ]);

        ShipmentLine::create([
            'company_code' => 'PARENT1',
            'shipment_number' => 'SHP001',
            'line_number' => 1,
            'product_name' => 'Line 1',
            'quantity' => 5
        ]);

        ShipmentLine::create([
            'company_code' => 'PARENT2',
            'shipment_number' => 'SHP002',
            'line_number' => 1,
            'product_name' => 'Line 2',
            'quantity' => 10
        ]);

        $lines = ShipmentLine::with('header')->get();

        $this->assertCount(3, $lines);

        $line1 = $lines->where('product_name', 'Line 1')->first();
        $line2 = $lines->where('product_name', 'Line 2')->first();

        $this->assertNotNull($line1->header);
        $this->assertNotNull($line2->header);

        $this->assertEquals('Parent Header 1', $line1->header->description);
        $this->assertEquals('Parent Header 2', $line2->header->description);
    }

    public function test_eager_loading_multiple_relations_on_single_query()
    {
        $header = ShipmentHeader::create([
            'company_code' => 'MULTI',
            'shipment_number' => 'SHP999',
            'description' => 'Multi Test'
        ]);

        ShipmentLine::create([
            'company_code' => 'MULTI',
            'shipment_number' => 'SHP999',
            'line_number' => 1,
            'product_name' => 'Item 1',
            'quantity' => 100
        ]);

        ShipmentLine::create([
            'company_code' => 'MULTI',
            'shipment_number' => 'SHP999',
            'line_number' => 2,
            'product_name' => 'Item 2',
            'quantity' => 200
        ]);

        $headerWithLines = ShipmentHeader::with('lines')->where('company_code', 'MULTI')->first();

        $this->assertNotNull($headerWithLines);
        $this->assertCount(2, $headerWithLines->lines);
        $this->assertTrue($headerWithLines->relationLoaded('lines'));
    }

    public function test_eager_loading_empty_relationships()
    {
        ShipmentHeader::create([
            'company_code' => 'EMPTY',
            'shipment_number' => 'SHP000',
            'description' => 'Header without lines'
        ]);

        $header = ShipmentHeader::with('lines')->where('company_code', 'EMPTY')->first();

        $this->assertNotNull($header);
        $this->assertCount(0, $header->lines);
        $this->assertTrue($header->relationLoaded('lines'));
    }

    public function test_nested_eager_loading_relationships()
    {
        $header = ShipmentHeader::create([
            'company_code' => 'NESTED',
            'shipment_number' => 'SHP100',
            'description' => 'Nested Test Header'
        ]);

        $line1 = ShipmentLine::create([
            'company_code' => 'NESTED',
            'shipment_number' => 'SHP100',
            'line_number' => 1,
            'product_name' => 'Product with details',
            'quantity' => 50
        ]);

        $line2 = ShipmentLine::create([
            'company_code' => 'NESTED',
            'shipment_number' => 'SHP100',
            'line_number' => 2,
            'product_name' => 'Another product',
            'quantity' => 25
        ]);

        ShipmentDetail::create([
            'company_code' => 'NESTED',
            'shipment_number' => 'SHP100',
            'line_number' => 1,
            'detail_sequence' => 1,
            'detail_description' => 'Detail 1A',
            'weight' => 10.5
        ]);

        ShipmentDetail::create([
            'company_code' => 'NESTED',
            'shipment_number' => 'SHP100',
            'line_number' => 1,
            'detail_sequence' => 2,
            'detail_description' => 'Detail 1B',
            'weight' => 15.2
        ]);

        ShipmentDetail::create([
            'company_code' => 'NESTED',
            'shipment_number' => 'SHP100',
            'line_number' => 2,
            'detail_sequence' => 1,
            'detail_description' => 'Detail 2A',
            'weight' => 8.0
        ]);

        $headerWithNested = ShipmentHeader::with('lines.details')->where('company_code', 'NESTED')->first();

        $this->assertNotNull($headerWithNested);
        $this->assertCount(2, $headerWithNested->lines);
        $this->assertTrue($headerWithNested->relationLoaded('lines'));

        $firstLine = $headerWithNested->lines->where('line_number', 1)->first();
        $secondLine = $headerWithNested->lines->where('line_number', 2)->first();

        $this->assertNotNull($firstLine);
        $this->assertNotNull($secondLine);
        $this->assertTrue($firstLine->relationLoaded('details'));
        $this->assertTrue($secondLine->relationLoaded('details'));

        $this->assertCount(2, $firstLine->details);
        $this->assertCount(1, $secondLine->details);

        $this->assertEquals('Detail 1A', $firstLine->details[0]->detail_description);
        $this->assertEquals('Detail 1B', $firstLine->details[1]->detail_description);
        $this->assertEquals('Detail 2A', $secondLine->details[0]->detail_description);
    }

    public function test_lazy_loading_after_eager_loading()
    {
        $header = ShipmentHeader::create([
            'company_code' => 'LAZY',
            'shipment_number' => 'SHP200',
            'description' => 'Lazy Load Test'
        ]);

        $line = ShipmentLine::create([
            'company_code' => 'LAZY',
            'shipment_number' => 'SHP200',
            'line_number' => 1,
            'product_name' => 'Lazy Product',
            'quantity' => 10
        ]);

        ShipmentDetail::create([
            'company_code' => 'LAZY',
            'shipment_number' => 'SHP200',
            'line_number' => 1,
            'detail_sequence' => 1,
            'detail_description' => 'Lazy Detail',
            'weight' => 5.0
        ]);

        $headerEager = ShipmentHeader::with('lines')->where('company_code', 'LAZY')->first();
        
        $this->assertTrue($headerEager->relationLoaded('lines'));
        $this->assertFalse($headerEager->lines[0]->relationLoaded('details'));

        $details = $headerEager->lines[0]->details;
        
        $this->assertTrue($headerEager->lines[0]->relationLoaded('details'));
        $this->assertCount(1, $details);
        $this->assertEquals('Lazy Detail', $details[0]->detail_description);
    }
}
