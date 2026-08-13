<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_image_file(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);
        $file = UploadedFile::fake()->image('banner_hero.jpg', 1200, 400);

        $response = $this->actingAs($admin)->postJson('/admin/upload', [
            'file' => $file,
            'folder' => 'banners',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure(['status', 'message', 'url', 'path']);

        $path = $response->json('path');
        Storage::disk('public')->assertExists($path);
    }

    public function test_non_admin_cannot_upload_image_file(): void
    {
        Storage::fake('public');

        $customer = User::factory()->create(['role' => 'customer']);
        $file = UploadedFile::fake()->image('test.jpg');

        $response = $this->actingAs($customer)->postJson('/admin/upload', [
            'file' => $file,
        ]);

        $response->assertStatus(403);
    }

    public function test_upload_fails_if_file_is_not_an_image(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);
        $file = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');

        $response = $this->actingAs($admin)->postJson('/admin/upload', [
            'file' => $file,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    public function test_upload_fails_if_no_file_provided(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->postJson('/admin/upload', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }
}
