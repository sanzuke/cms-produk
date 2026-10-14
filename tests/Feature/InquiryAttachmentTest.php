<?php

namespace Tests\Feature;

use App\Models\Inquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InquiryAttachmentTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $files): array
    {
        return [
            'name' => 'Budi', 'company' => 'PT Contoh', 'email' => 'budi@contoh.test', 'phone' => '081234567890',
            'site' => 'Site A', 'needed_date' => now()->addWeek()->toDateString(), 'consent' => '1',
            'attachments' => $files,
        ];
    }

    public function test_attachments_are_stored_on_private_disk_only(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $this->post('/pesan', $this->payload([UploadedFile::fake()->create('brief.pdf', 100, 'application/pdf')]))
            ->assertRedirect(route('order.thanks'));

        $path = Inquiry::firstOrFail()->attachments[0];
        Storage::disk('local')->assertExists($path);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_disallowed_type_oversize_and_too_many_files_are_rejected(): void
    {
        Storage::fake('local');

        $this->post('/pesan', $this->payload([UploadedFile::fake()->create('virus.exe', 10, 'application/x-msdownload')]))
            ->assertSessionHasErrors('attachments.0');
        $this->post('/pesan', $this->payload([UploadedFile::fake()->create('besar.pdf', 11 * 1024, 'application/pdf')]))
            ->assertSessionHasErrors('attachments.0');
        $this->post('/pesan', $this->payload(array_map(
            fn ($i) => UploadedFile::fake()->create("f$i.pdf", 10, 'application/pdf'), range(1, 4)
        )))->assertSessionHasErrors('attachments');

        $this->assertSame(0, Inquiry::count());
        $this->assertSame([], Storage::disk('local')->allFiles());
    }
}
