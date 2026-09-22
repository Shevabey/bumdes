<?php

namespace Tests\Feature;

use App\Models\Transaksi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransaksiTest extends TestCase
{
    use RefreshDatabase;

    public function test_transaksi_seeder_creates_input_and_output_for_each_unit(): void
    {
        $this->seed();

        $transactions = Transaksi::with(['unit', 'pencatat'])->get();

        $this->assertCount(20, $transactions);
        $this->assertSame(10, $transactions->where('tipe', 'input')->count());
        $this->assertSame(10, $transactions->where('tipe', 'output')->count());
        $this->assertSame('array', gettype($transactions->first()->detail));
        $this->assertSame('data seed development', $transactions->first()->detail['sumber']);
        $this->assertSame(10, $transactions->pluck('unit.id_unit')->unique()->count());
        $this->assertTrue($transactions->every(fn (Transaksi $transaction): bool => $transaction->pencatat !== null));
    }
}
