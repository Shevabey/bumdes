<?php

namespace Tests\Feature;

use App\Http\Livewire\Operasional\TagihanManager;
use App\Models\Akun;
use App\Models\Bumdes;
use App\Models\Pelanggan;
use App\Models\Tagihan;
use App\Models\UnitUsaha;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BumdesTagihanTest extends TestCase
{
    use RefreshDatabase;

    // ─────────────────────────────── Route Authorization ───────────────────────────────

    public function test_tagihan_routes_redirect_guests(): void
    {
        $this->seed();

        $this->get(route('operasional.tagihan'))->assertRedirect(route('login'));
    }

    public function test_tagihan_routes_accessible_by_authorized_roles(): void
    {
        $this->seed();

        // 1. Super Admin
        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);
        $this->get(route('operasional.tagihan'))->assertOk();

        // 2. Admin BUMDes
        $adminBumdes = Akun::where('username', 'admin.sendangsari')->firstOrFail();
        $this->actingAs($adminBumdes);
        $this->get(route('operasional.tagihan'))->assertOk();

        // 3. Sekretaris
        $sekretaris = Akun::where('username', 'sekretaris.sds')->firstOrFail();
        $this->actingAs($sekretaris);
        $this->get(route('operasional.tagihan'))->assertOk();

        // 4. Admin Unit PAMDes
        $adminUnit = Akun::where('username', 'admin.pamdes.sds')->firstOrFail();
        $this->actingAs($adminUnit);
        $this->get(route('operasional.tagihan'))->assertOk();
    }

    // ─────────────────────────────── Livewire TagihanManager ───────────────────────────────

    public function test_tagihan_manager_renders_and_shows_records(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);

        Livewire::test(TagihanManager::class)
            ->assertStatus(200)
            ->assertSee('Manajemen Tagihan Pelanggan')
            ->assertSee('Belum Bayar')
            ->assertSee('Lunas');
    }

    public function test_tagihan_manager_search(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);

        $tagihan = Tagihan::firstOrFail();

        Livewire::test(TagihanManager::class)
            ->set('search', $tagihan->id_tagihan)
            ->assertSee($tagihan->id_tagihan);
    }

    public function test_tagihan_manager_filter_by_status(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);

        Livewire::test(TagihanManager::class)
            ->set('filters.status', 'lunas')
            ->assertStatus(200);

        Livewire::test(TagihanManager::class)
            ->set('filters.status', 'belum_bayar')
            ->assertStatus(200);
    }

    public function test_tagihan_manager_filter_by_unit(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);

        $unit = UnitUsaha::firstOrFail();

        Livewire::test(TagihanManager::class)
            ->set('filters.id_unit', $unit->id_unit)
            ->assertStatus(200);
    }

    public function test_tagihan_manager_filter_by_due_date_range(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);

        Livewire::test(TagihanManager::class)
            ->set('filters.jatuh_tempo_mulai', '2026-09-01')
            ->set('filters.jatuh_tempo_akhir', '2026-09-30')
            ->assertStatus(200);
    }

    public function test_tagihan_manager_stats_calculation(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);

        $component = Livewire::test(TagihanManager::class);

        $expectedCountBelumBayar = Tagihan::where('status', 'belum_bayar')->count();
        $expectedCountMenunggu = Tagihan::where('status', 'menunggu_verifikasi')->count();
        $expectedCountLunas = Tagihan::where('status', 'lunas')->count();
        $expectedTotalNominal = (float) Tagihan::sum('jumlah');

        $stats = $component->get('stats');
        $this->assertEquals($expectedCountBelumBayar, $stats['countBelumBayar']);
        $this->assertEquals($expectedCountMenunggu, $stats['countMenunggu']);
        $this->assertEquals($expectedCountLunas, $stats['countLunas']);
        $this->assertEquals($expectedTotalNominal, $stats['totalNominal']);
    }

    public function test_tagihan_manager_create_by_admin_unit(): void
    {
        $this->seed();

        $adminUnit = Akun::where('username', 'admin.pamdes.sds')->firstOrFail();
        $this->actingAs($adminUnit);

        $pelanggan = Pelanggan::where('id_unit', $adminUnit->id_unit)->firstOrFail();

        Livewire::test(TagihanManager::class)
            ->call('openCreateModal')
            ->assertSet('showModal', true)
            ->assertSet('isEdit', false)
            ->assertSet('id_unit', $adminUnit->id_unit)
            ->set('id_pelanggan', $pelanggan->id_pelanggan)
            ->set('jumlah', 35000)
            ->set('jatuh_tempo', '2026-10-15')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('tagihan', [
            'id_pelanggan' => $pelanggan->id_pelanggan,
            'id_unit' => $adminUnit->id_unit,
            'jumlah' => 35000,
            'status' => 'belum_bayar',
        ]);
    }

    public function test_tagihan_manager_create_validation_errors(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);

        Livewire::test(TagihanManager::class)
            ->call('openCreateModal')
            ->set('id_unit', '')
            ->set('id_pelanggan', '')
            ->set('jumlah', '')
            ->call('save')
            ->assertHasErrors(['id_unit', 'id_pelanggan', 'jumlah']);
    }

    public function test_tagihan_manager_edit_tagihan(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);

        $tagihan = Tagihan::where('status', 'belum_bayar')->firstOrFail();

        Livewire::test(TagihanManager::class)
            ->call('openEditModal', $tagihan->id_tagihan)
            ->assertSet('showModal', true)
            ->assertSet('isEdit', true)
            ->set('jumlah', 45000)
            ->set('jatuh_tempo', '2026-10-20')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('tagihan', [
            'id_tagihan' => $tagihan->id_tagihan,
            'jumlah' => 45000,
            'jatuh_tempo' => '2026-10-20',
        ]);
    }

    public function test_tagihan_manager_bayar_tunai(): void
    {
        $this->seed();

        $adminUnit = Akun::where('username', 'admin.pamdes.sds')->firstOrFail();
        $this->actingAs($adminUnit);

        $tagihan = Tagihan::where('id_unit', $adminUnit->id_unit)
            ->where('status', 'belum_bayar')
            ->firstOrFail();

        Livewire::test(TagihanManager::class)
            ->call('bayarTunai', $tagihan->id_tagihan)
            ->assertStatus(200);

        $this->assertDatabaseHas('tagihan', [
            'id_tagihan' => $tagihan->id_tagihan,
            'status' => 'lunas',
            'metode' => 'tunai',
            'diverifikasi_oleh' => $adminUnit->id_akun,
        ]);
    }

    public function test_tagihan_manager_verifikasi_transfer_approve(): void
    {
        $this->seed();

        $adminUnit = Akun::where('username', 'admin.pamdes.sds')->firstOrFail();
        $this->actingAs($adminUnit);

        $tagihan = Tagihan::where('id_unit', $adminUnit->id_unit)->firstOrFail();
        $tagihan->update([
            'status' => 'menunggu_verifikasi',
            'metode' => 'transfer',
            'bukti_transfer_url' => 'payments/test-proof-approve.jpg',
        ]);

        Livewire::test(TagihanManager::class)
            ->call('openVerifikasiModal', $tagihan->id_tagihan)
            ->assertSet('showVerifikasiModal', true)
            ->assertSet('verifikasiTagihanId', $tagihan->id_tagihan)
            ->call('prosesVerifikasiTransfer', true)
            ->assertSet('showVerifikasiModal', false)
            ->assertSet('verifikasiTagihanId', null);

        $this->assertDatabaseHas('tagihan', [
            'id_tagihan' => $tagihan->id_tagihan,
            'status' => 'lunas',
            'diverifikasi_oleh' => $adminUnit->id_akun,
        ]);
    }

    public function test_tagihan_manager_verifikasi_transfer_reject(): void
    {
        $this->seed();

        $adminUnit = Akun::where('username', 'admin.pamdes.sds')->firstOrFail();
        $this->actingAs($adminUnit);

        $tagihan = Tagihan::where('id_unit', $adminUnit->id_unit)->firstOrFail();
        $tagihan->update([
            'status' => 'menunggu_verifikasi',
            'metode' => 'transfer',
            'bukti_transfer_url' => 'payments/test-proof-reject.jpg',
        ]);

        Livewire::test(TagihanManager::class)
            ->call('openVerifikasiModal', $tagihan->id_tagihan)
            ->assertSet('showVerifikasiModal', true)
            ->call('prosesVerifikasiTransfer', false)
            ->assertSet('showVerifikasiModal', false);

        $this->assertDatabaseHas('tagihan', [
            'id_tagihan' => $tagihan->id_tagihan,
            'status' => 'ditolak',
            'diverifikasi_oleh' => $adminUnit->id_akun,
        ]);
    }

    public function test_tagihan_manager_view_detail_modal(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);

        $tagihan = Tagihan::firstOrFail();

        Livewire::test(TagihanManager::class)
            ->call('openDetailModal', $tagihan->id_tagihan)
            ->assertSet('showDetailModal', true)
            ->assertSet('detailId', $tagihan->id_tagihan)
            ->assertSee($tagihan->id_tagihan)
            ->call('closeDetailModal')
            ->assertSet('showDetailModal', false)
            ->assertSet('detailId', null);
    }

    public function test_tagihan_manager_sorting(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);

        Livewire::test(TagihanManager::class)
            ->call('sortBy', 'jumlah')
            ->assertSet('sortField', 'jumlah')
            ->assertSet('sortDirection', 'asc')
            ->call('sortBy', 'jumlah')
            ->assertSet('sortDirection', 'desc');
    }

    public function test_tagihan_manager_reset_filters(): void
    {
        $this->seed();

        $superadmin = Akun::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($superadmin);

        Livewire::test(TagihanManager::class)
            ->set('search', 'TAG-')
            ->set('filters.status', 'lunas')
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('filters.status', '');
    }

    public function test_tagihan_manager_admin_unit_scope_isolation(): void
    {
        $this->seed();

        $adminUnit = Akun::where('username', 'admin.pamdes.sds')->firstOrFail();
        $this->actingAs($adminUnit);

        // Own unit tagihan
        $ownTagihan = Tagihan::where('id_unit', $adminUnit->id_unit)->first();

        // Other unit tagihan
        $otherTagihan = Tagihan::where('id_unit', '!=', $adminUnit->id_unit)->first();

        $component = Livewire::test(TagihanManager::class)->assertStatus(200);

        if ($ownTagihan) {
            $component->assertSee($ownTagihan->id_tagihan);
        }

        if ($otherTagihan) {
            $component->assertDontSee($otherTagihan->id_tagihan);
        }
    }
}
