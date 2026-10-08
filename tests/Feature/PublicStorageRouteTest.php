<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicStorageRouteTest extends TestCase
{
    public function test_public_storage_route_serves_uploaded_files(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('reports/example.txt', 'uploaded file');

        $response = $this->get('/storage/reports/example.txt');

        $response->assertOk();
        $response->assertHeader('Content-Disposition', 'inline; filename=example.txt');
    }

    public function test_public_storage_route_returns_not_found_for_missing_files(): void
    {
        Storage::fake('public');

        $this->get('/storage/reports/missing.jpg')->assertNotFound();
    }
}
