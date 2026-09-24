<?php

namespace Tests\Feature;

use App\Models\IuranBumdes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IuranBumdesTest extends TestCase
{
    use RefreshDatabase;

    public function test_iuran_seeder_creates_monthly_contributions_for_each_bumdes(): void
    {
        $this->seed();

        $iuran = IuranBumdes::with(['bumdes', 'verifikator'])->get();
        $lunas = $iuran->firstWhere('status', 'lunas');
        $pending = $iuran->firstWhere('status', 'menunggu_verifikasi');

        $this->assertCount(2, $iuran);
        $this->assertSame('2026-09', $iuran->first()->bulan_tahun);
        $this->assertSame('50000.00', (string) $iuran->first()->jumlah);
        $this->assertSame('kas', $lunas->sumber_dana);
        $this->assertSame('tunai', $lunas->metode_bayar);
        $this->assertNotNull($lunas->verifikator);
        $this->assertSame('luar_kas', $pending->sumber_dana);
        $this->assertSame('transfer', $pending->metode_bayar);
        $this->assertNotNull($pending->bukti_pembayaran_url);
        $this->assertTrue($iuran->every(fn (IuranBumdes $contribution): bool => $contribution->bumdes !== null));
    }
}
