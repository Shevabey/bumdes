<?php

namespace Tests\Feature;

use App\Models\KasBumdes;
use App\Models\KasMutasi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KasTest extends TestCase
{
    use RefreshDatabase;

    public function test_kas_seeder_creates_one_balance_and_mutations_per_bumdes(): void
    {
        $this->seed();

        $kas = KasBumdes::with(['bumdes', 'mutasi'])->get();
        $sendangsari = $kas->firstWhere('id_bumdes', 'BMD-SDS-001');

        $this->assertCount(2, $kas);
        $this->assertSame(3, KasMutasi::count());
        $this->assertSame('250000.00', (string) $sendangsari->saldo);
        $this->assertSame(300000.0, (float) $sendangsari->mutasi->where('tipe', 'masuk')->sum('jumlah'));
        $this->assertSame(50000.0, (float) $sendangsari->mutasi->where('tipe', 'keluar')->sum('jumlah'));
        $this->assertSame('iuran', KasMutasi::where('sumber', 'iuran')->value('sumber'));
        $this->assertTrue($kas->every(fn (KasBumdes $cash): bool => $cash->bumdes !== null));
        $this->assertTrue($kas->every(fn (KasBumdes $cash): bool => $cash->mutasi->every(fn (KasMutasi $mutation): bool => $mutation->kas !== null)));
    }
}
