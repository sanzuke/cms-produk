<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRolesTest extends TestCase
{
    use RefreshDatabase;

    private const CONTENT = ['services', 'packages', 'projects', 'posts', 'testimonials'];

    public function test_access_matrix_per_role(): void
    {
        $matrix = [
            [Role::Owner, ['inquiries', 'users', ...self::CONTENT], []],
            [Role::Editor, self::CONTENT, ['inquiries', 'users']],
            [Role::Sales, ['inquiries'], ['users', ...self::CONTENT]],
        ];

        foreach ($matrix as [$role, $allowed, $denied]) {
            $this->actingAs(User::factory()->role($role)->create());
            foreach ($allowed as $path) {
                $this->get("/admin/$path")->assertOk();
            }
            foreach ($denied as $path) {
                $this->get("/admin/$path")->assertForbidden();
            }
        }
    }

    public function test_first_user_without_explicit_role_becomes_owner_then_default_is_editor(): void
    {
        $first = User::create(['name' => 'A', 'email' => 'a@x.test', 'password' => 'secret-pass']);
        $second = User::create(['name' => 'B', 'email' => 'b@x.test', 'password' => 'secret-pass']);

        $this->assertSame(Role::Owner, $first->fresh()->role);
        $this->assertSame(Role::Editor, $second->fresh()->role);
    }

    public function test_user_without_role_cannot_enter_panel(): void
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => null]);

        $this->assertFalse($user->canAccessPanel(\Filament\Facades\Filament::getPanel('admin')));
    }

    public function test_owner_manages_users_but_cannot_change_own_role(): void
    {
        $owner = User::factory()->role(Role::Owner)->create();
        $this->actingAs($owner);

        \Livewire\Livewire::test(\App\Filament\Resources\Users\Pages\CreateUser::class)
            ->fillForm(['name' => 'Sari', 'email' => 'sari@x.test', 'role' => 'sales', 'password' => 'rahasia-123'])
            ->call('create')->assertHasNoFormErrors();
        $sari = User::where('email', 'sari@x.test')->firstOrFail();
        $this->assertSame(Role::Sales, $sari->role);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('rahasia-123', $sari->password));

        \Livewire\Livewire::test(\App\Filament\Resources\Users\Pages\EditUser::class, ['record' => $owner->id])
            ->fillForm(['name' => 'Owner Baru'])
            ->call('save')->assertHasNoFormErrors();
        $this->assertSame(Role::Owner, $owner->fresh()->role);
        $this->assertSame('Owner Baru', $owner->fresh()->name);
    }

    public function test_last_owner_cannot_be_demoted_or_deleted_but_one_of_two_can(): void
    {
        $a = User::factory()->role(Role::Owner)->create();

        try {
            $a->update(['role' => Role::Editor]);
            $this->fail('demote seharusnya ditolak');
        } catch (\DomainException) {
        }
        try {
            $a->delete();
            $this->fail('hapus seharusnya ditolak');
        } catch (\DomainException) {
        }
        $this->assertTrue($a->fresh()->isLastOwner());

        $b = User::factory()->role(Role::Owner)->create();
        $this->assertFalse($a->fresh()->isLastOwner());
        $a->update(['role' => Role::Editor]);
        $this->assertSame(Role::Editor, $a->fresh()->role);
        $this->assertTrue($b->fresh()->isLastOwner());
    }
}
