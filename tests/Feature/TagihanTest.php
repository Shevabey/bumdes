<?php

namespace Tests\Feature;

use App\Models\Tagihan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagihanTest extends TestCase
{
    use RefreshDatabase;

    public function test_tagihan_seeder_creates_payment_statuses_and_relations(): void
    {
        $this->seed();

        $tagihan = Tagihan::with(['pelanggan', 'unit', 'verifikator'])->get();

        $this->assertCount(10, $tagihan);
        $this->assertSame(1, $tagihan->where('status', 'lunas')->count());
        $this->assertSame(1, $tagihan->where('status', 'menunggu_verifikasi')->count());
        $this->assertSame(8, $tagihan->where('status', 'belum_bayar')->count());
        $this->assertSame('tunai', $tagihan->firstWhere('status', 'lunas')->metode);
        $this->assertSame('transfer', $tagihan->firstWhere('status', 'menunggu_verifikasi')->metode);
        $this->assertNotNull($tagihan->firstWhere('status', 'lunas')->verifikator);
        $this->assertTrue($tagihan->every(fn(Tagihan $invoice): bool => $invoice->pelanggan !== null && $invoice->unit !== null));
    }
}
