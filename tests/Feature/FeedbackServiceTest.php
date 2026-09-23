<?php

namespace Tests\Feature;

use App\Models\Akun;
use App\Models\Feedback;
use App\Models\UnitUsaha;
use App\Services\FeedbackService;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class FeedbackServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_monitoring_roles_can_create_feedback_for_bumdes_and_unit(): void
    {
        $this->seed();
        $service = new FeedbackService;
        $pengawas = Akun::where('username', 'pengawas1')->firstOrFail();
        $direktur = Akun::where('username', 'direktur1')->firstOrFail();
        $pamdes = UnitUsaha::where('id_unit', 'UNT-BMD-SDS-001-PAM-01')->firstOrFail();
        $tanggal = Carbon::create(2026, 9, 24, 9, 0);

        $fbBumdes = $service->create($pengawas, [
            'ke_id_bumdes' => 'BMD-SDS-001',
            'isi_catatan' => 'Periksa kembali kelengkapan SOP pengelolaan BUMDes.',
        ], $tanggal);

        $this->assertSame('FB-000004', $fbBumdes->id_feedback);
        $this->assertSame($pengawas->id_akun, $fbBumdes->dari_id_akun);
        $this->assertSame('BMD-SDS-001', $fbBumdes->ke_id_bumdes);
        $this->assertNull($fbBumdes->ke_id_unit);
        $this->assertSame('belum', $fbBumdes->status_tindak_lanjut);
        $this->assertSame('2026-09-24 09:00:00', $fbBumdes->tanggal->toDateTimeString());

        $fbUnit = $service->create($direktur, [
            'ke_id_bumdes' => 'BMD-SDS-001',
            'ke_id_unit' => $pamdes->id_unit,
            'isi_catatan' => 'Laporan debit air PAMDes perlu diperbarui mingguan.',
        ], $tanggal);

        $this->assertSame('FB-000005', $fbUnit->id_feedback);
        $this->assertSame($pamdes->id_unit, $fbUnit->ke_id_unit);
    }

    public function test_operational_roles_cannot_create_feedback(): void
    {
        $this->seed();
        $service = new FeedbackService;
        $adminBumdes = Akun::where('username', 'admin.sendangsari')->firstOrFail();
        $bendahara = Akun::where('username', 'bendahara.sds')->firstOrFail();
        $pengguna = Akun::where('username', 'pengguna.sds')->firstOrFail();

        foreach ([$adminBumdes, $bendahara, $pengguna] as $actor) {
            try {
                $service->create($actor, [
                    'ke_id_bumdes' => 'BMD-SDS-001',
                    'isi_catatan' => 'Catatan operasional.',
                ]);
                $this->fail("Aktor {$actor->username} seharusnya tidak diizinkan membuat feedback.");
            } catch (AuthorizationException $e) {
                $this->assertStringContainsString('Hanya Pengawas, Penasihat, Direktur, atau Super Admin', $e->getMessage());
            }
        }
    }

    public function test_create_feedback_validates_unit_ownership_and_content(): void
    {
        $this->seed();
        $service = new FeedbackService;
        $superAdmin = Akun::where('username', 'superadmin')->firstOrFail();

        // 1. Unit milik Sendangsari tetapi target BUMDes Sendangrejo
        try {
            $service->create($superAdmin, [
                'ke_id_bumdes' => 'BMD-SDR-001',
                'ke_id_unit' => 'UNT-BMD-SDS-001-PAM-01',
                'isi_catatan' => 'Catatan salah target unit.',
            ]);
            $this->fail('Harus gagal karena unit bukan milik BUMDes target.');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('Unit target harus berada di BUMDes target', $e->getMessage());
        }

        // 2. Isi catatan kosong
        try {
            $service->create($superAdmin, [
                'ke_id_bumdes' => 'BMD-SDS-001',
                'isi_catatan' => '   ',
            ]);
            $this->fail('Harus gagal karena catatan kosong.');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('Isi catatan feedback wajib diisi', $e->getMessage());
        }

        // 3. BUMDes tidak valid
        try {
            $service->create($superAdmin, [
                'ke_id_bumdes' => 'BMD-NON-EXIST',
                'isi_catatan' => 'Catatan BUMDes tidak ada.',
            ]);
            $this->fail('Harus gagal karena BUMDes tidak valid.');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('BUMDes target wajib diisi dan harus valid', $e->getMessage());
        }
    }

    public function test_admin_bumdes_can_update_status_for_owned_bumdes_feedback(): void
    {
        $this->seed();
        $service = new FeedbackService;
        $adminSds = Akun::where('username', 'admin.sendangsari')->firstOrFail();
        $feedbackSds = Feedback::where('id_feedback', 'FB-000001')->firstOrFail();

        $updated = $service->updateStatus($feedbackSds, $adminSds, 'sedang');

        $this->assertSame('sedang', $updated->status_tindak_lanjut);
    }

    public function test_admin_unit_can_update_status_for_owned_unit_feedback(): void
    {
        $this->seed();
        $service = new FeedbackService;
        $adminPamdes = Akun::where('username', 'admin.pamdes.sds')->firstOrFail();
        $feedbackPamdes = Feedback::where('id_feedback', 'FB-000002')->firstOrFail();

        $updated = $service->updateStatus($feedbackPamdes, $adminPamdes, 'selesai');

        $this->assertSame('selesai', $updated->status_tindak_lanjut);
    }

    public function test_update_status_rejects_cross_scope_actors(): void
    {
        $this->seed();
        $service = new FeedbackService;
        $adminSdr = Akun::where('username', 'admin.sendangrejo')->firstOrFail();
        $adminPamdes = Akun::where('username', 'admin.pamdes.sds')->firstOrFail();
        $feedbackBumdesSds = Feedback::where('id_feedback', 'FB-000001')->firstOrFail();

        // 1. Admin BUMDes beda desa
        try {
            $service->updateStatus($feedbackBumdesSds, $adminSdr, 'selesai');
            $this->fail('Harus gagal karena beda BUMDes.');
        } catch (AuthorizationException $e) {
            $this->assertStringContainsString('Akun tidak berwenang', $e->getMessage());
        }

        // 2. Admin unit mencoba update feedback level BUMDes (ke_id_unit null)
        try {
            $service->updateStatus($feedbackBumdesSds, $adminPamdes, 'selesai');
            $this->fail('Harus gagal karena admin unit tidak berwenang pada feedback level BUMDes.');
        } catch (AuthorizationException $e) {
            $this->assertStringContainsString('Akun tidak berwenang', $e->getMessage());
        }
    }

    public function test_update_status_rejects_invalid_status_enum(): void
    {
        $this->seed();
        $service = new FeedbackService;
        $adminSds = Akun::where('username', 'admin.sendangsari')->firstOrFail();
        $feedbackSds = Feedback::where('id_feedback', 'FB-000001')->firstOrFail();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Status tindak lanjut tidak valid');

        $service->updateStatus($feedbackSds, $adminSds, 'status_palsu');
    }
}
