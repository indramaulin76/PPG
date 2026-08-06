<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\Jamaah;
use App\Models\Kelompok;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JamaahUpdateScopeTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(string $role, ?Kelompok $kelompok = null): User
    {
        $user = User::factory()->create([
            'desa_id' => $kelompok?->desa_id,
            'kelompok_id' => $kelompok?->id,
        ]);
        $user->setRole($role);
        $user->setIsActive(true);
        $user->save();

        return $user;
    }

    public function test_admin_kelompok_cannot_move_jamaah_to_another_kelompok(): void
    {
        $desa = Desa::create(['nama_desa' => 'Desa A', 'kode_desa' => 'A']);
        $ownKelompok = Kelompok::create(['desa_id' => $desa->id, 'nama_kelompok' => 'Kelompok Sendiri']);
        $otherKelompok = Kelompok::create(['desa_id' => $desa->id, 'nama_kelompok' => 'Kelompok Lain']);

        $admin = $this->makeAdmin(User::ROLE_ADMIN_KELOMPOK, $ownKelompok);

        $jamaah = Jamaah::create([
            'kelompok_id' => $ownKelompok->id,
            'nama_lengkap' => 'Budi Santoso',
            'jenis_kelamin' => 'L',
        ]);

        $response = $this->actingAs($admin)->put(route('jamaah.update', $jamaah), [
            'kelompok_id' => $otherKelompok->id,
            'nama_lengkap' => 'Budi Santoso',
            'jenis_kelamin' => 'L',
        ]);

        $response->assertForbidden();
        $this->assertEquals($ownKelompok->id, $jamaah->fresh()->kelompok_id);
    }

    public function test_admin_desa_cannot_move_jamaah_to_kelompok_in_another_desa(): void
    {
        $desaA = Desa::create(['nama_desa' => 'Desa A', 'kode_desa' => 'A']);
        $desaB = Desa::create(['nama_desa' => 'Desa B', 'kode_desa' => 'B']);
        $kelompokA = Kelompok::create(['desa_id' => $desaA->id, 'nama_kelompok' => 'Kelompok A']);
        $kelompokB = Kelompok::create(['desa_id' => $desaB->id, 'nama_kelompok' => 'Kelompok B']);

        $admin = User::factory()->create(['desa_id' => $desaA->id, 'kelompok_id' => null]);
        $admin->setRole(User::ROLE_ADMIN_DESA);
        $admin->setIsActive(true);
        $admin->save();

        $jamaah = Jamaah::create([
            'kelompok_id' => $kelompokA->id,
            'nama_lengkap' => 'Siti Aminah',
            'jenis_kelamin' => 'P',
        ]);

        $response = $this->actingAs($admin)->put(route('jamaah.update', $jamaah), [
            'kelompok_id' => $kelompokB->id,
            'nama_lengkap' => 'Siti Aminah',
            'jenis_kelamin' => 'P',
        ]);

        $response->assertForbidden();
        $this->assertEquals($kelompokA->id, $jamaah->fresh()->kelompok_id);
    }

    public function test_admin_kelompok_can_still_update_jamaah_within_own_scope(): void
    {
        $desa = Desa::create(['nama_desa' => 'Desa A', 'kode_desa' => 'A']);
        $kelompok = Kelompok::create(['desa_id' => $desa->id, 'nama_kelompok' => 'Kelompok Sendiri']);

        $admin = $this->makeAdmin(User::ROLE_ADMIN_KELOMPOK, $kelompok);

        $jamaah = Jamaah::create([
            'kelompok_id' => $kelompok->id,
            'nama_lengkap' => 'Budi Santoso',
            'jenis_kelamin' => 'L',
        ]);

        $response = $this->actingAs($admin)->put(route('jamaah.update', $jamaah), [
            'kelompok_id' => $kelompok->id,
            'nama_lengkap' => 'Budi Santoso Updated',
            'jenis_kelamin' => 'L',
        ]);

        $response->assertRedirect(route('jamaah.index'));
        $this->assertEquals('Budi Santoso Updated', $jamaah->fresh()->nama_lengkap);
    }
}
