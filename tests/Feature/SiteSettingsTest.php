<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Filament\Pages\SiteSettings;
use App\Models\User;
use App\Providers\AppServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SiteSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function validState(array $override = []): array
    {
        return $override + [
            'name' => 'Brand Baru', 'tagline' => 'Tagline Baru', 'description' => 'Deskripsi baru.',
            'email' => 'info@brandbaru.test', 'whatsapp' => '0812-3456-7890', 'phone_display' => '0812',
            'hours' => 'Senin–Jumat', 'service_area' => 'Kaltim',
            'stats' => [['value' => '120', 'label' => 'jam terbang']],
        ];
    }

    /** Simulasi request baru: default config + overlay DB (refreshApplication akan membuang DB in-memory). */
    private function reloadSettings(): void
    {
        config(['site' => require config_path('site.php')]);
        app()->getProvider(AppServiceProvider::class)->boot();
    }

    public function test_only_owner_can_open_settings(): void
    {
        $this->actingAs(User::factory()->role(Role::Editor)->create())->get('/admin/pengaturan')->assertForbidden();
        $this->actingAs(User::factory()->role(Role::Sales)->create())->get('/admin/pengaturan')->assertForbidden();
        $this->actingAs(User::factory()->role(Role::Owner)->create())->get('/admin/pengaturan')->assertOk();
    }

    public function test_saved_settings_override_defaults_on_public_site(): void
    {
        $this->actingAs(User::factory()->role(Role::Owner)->create());

        Livewire::test(SiteSettings::class)->fillForm($this->validState())->call('save')->assertHasNoFormErrors();

        $this->reloadSettings();
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('Brand Baru', $html);
        $this->assertStringContainsString('120', $html);
        $this->assertStringContainsString('https://wa.me/6281234567890', $html);

        $this->get('/kontak')->assertSee('info@brandbaru.test')->assertSee('Senin–Jumat');
    }

    public function test_clearing_whatsapp_hides_button_and_required_fields_are_enforced(): void
    {
        $this->actingAs(User::factory()->role(Role::Owner)->create());

        Livewire::test(SiteSettings::class)
            ->fillForm($this->validState(['whatsapp' => '', 'name' => '', 'email' => 'bukan-email']))
            ->call('save')
            ->assertHasFormErrors(['name' => 'required', 'email' => 'email']);

        Livewire::test(SiteSettings::class)->fillForm($this->validState(['whatsapp' => '']))->call('save')->assertHasNoFormErrors();
        $this->reloadSettings();
        $this->get('/')->assertDontSee('wa.me');
    }
}
