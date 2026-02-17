<?php

namespace Tests\Unit\Models;

use App\Models\Branch;
use App\Models\Circular;
use App\Models\CircularAcknowledgment;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CircularTest extends TestCase
{
    use RefreshDatabase;

    public function test_belongs_to_creator(): void
    {
        $circular = Circular::factory()->create();
        $this->assertInstanceOf(User::class, $circular->creator);
    }

    public function test_belongs_to_target_branch(): void
    {
        $branch = Branch::factory()->create();
        $circular = Circular::factory()->create(['target_branch_id' => $branch->id]);
        $this->assertEquals($branch->id, $circular->targetBranch->id);
    }

    public function test_has_many_acknowledgments(): void
    {
        $circular = Circular::factory()->create();
        $user = User::factory()->create();
        CircularAcknowledgment::create([
            'circular_id'     => $circular->id,
            'user_id'         => $user->id,
            'acknowledged_at' => now(),
        ]);
        $this->assertEquals(1, $circular->acknowledgments()->count());
    }

    public function test_title_accessor_arabic(): void
    {
        app()->setLocale('ar');
        $circular = Circular::factory()->create(['title_ar' => 'تعميم', 'title_en' => 'Circular']);
        $this->assertEquals('تعميم', $circular->title);
    }

    public function test_title_accessor_english(): void
    {
        app()->setLocale('en');
        $circular = Circular::factory()->create(['title_ar' => 'تعميم', 'title_en' => 'Circular']);
        $this->assertEquals('Circular', $circular->title);
    }

    public function test_scope_published(): void
    {
        Circular::factory()->create(['published_at' => now()->subHour()]);
        Circular::factory()->create(['published_at' => null]);
        $this->assertEquals(1, Circular::published()->count());
    }

    public function test_scope_active_excludes_expired(): void
    {
        Circular::factory()->create(['published_at' => now()->subHour(), 'expires_at' => now()->addDay()]);
        Circular::factory()->create(['published_at' => now()->subHour(), 'expires_at' => now()->subHour()]);
        $this->assertEquals(1, Circular::active()->count());
    }

    public function test_scope_active_includes_no_expiry(): void
    {
        Circular::factory()->create(['published_at' => now()->subHour(), 'expires_at' => null]);
        $this->assertEquals(1, Circular::active()->count());
    }

    public function test_soft_deletes(): void
    {
        $circular = Circular::factory()->create();
        $circular->delete();
        $this->assertSoftDeleted('circulars', ['id' => $circular->id]);
    }
}
