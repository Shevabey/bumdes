<?php

namespace Tests\Feature;

use App\Models\Referral;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferralTest extends TestCase
{
    use RefreshDatabase;

    public function test_referral_seeder_covers_expiry_redeem_and_verification_states(): void
    {
        $this->seed();

        $referrals = Referral::with(['pengaju', 'penerima'])->get();
        $active = $referrals->firstWhere('status', 'aktif');
        $pending = $referrals->firstWhere('status', 'pending');
        $paid = $referrals->firstWhere('status', 'cair');
        $failed = $referrals->firstWhere('status', 'gagal');

        $this->assertCount(4, $referrals);
        $this->assertSame('2026-09-25 00:00:00', $active->tanggal_expired->format('Y-m-d H:i:s'));
        $this->assertNull($active->tanggal_redeem);
        $this->assertSame('BMD-SDR-001', $pending->id_bumdes_penerima);
        $this->assertSame('2026-10-03 00:00:00', $pending->batas_verifikasi->format('Y-m-d H:i:s'));
        $this->assertNotNull($paid->tanggal_cair);
        $this->assertNull($failed->tanggal_cair);
        $this->assertTrue($referrals->every(fn (Referral $referral): bool => $referral->pengaju !== null));
        $this->assertTrue($referrals->whereNotNull('id_bumdes_penerima')->every(fn (Referral $referral): bool => $referral->penerima !== null));
    }
}
