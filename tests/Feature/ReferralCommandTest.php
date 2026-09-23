<?php

namespace Tests\Feature;

use App\Models\KasBumdes;
use App\Models\KasMutasi;
use App\Models\Referral;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferralCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_expire_command_marks_old_code_and_generates_replacement(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 9, 26));
        $this->seed();

        $this->artisan('referral:expire-check')
            ->expectsOutput('1 referral kedaluwarsa, 1 kode baru dibuat.')
            ->assertExitCode(0);

        $this->assertSame(1, Referral::where('status', 'kedaluwarsa')->count());
        $this->assertSame(1, Referral::where('status', 'aktif')->count());
        Carbon::setTestNow();
    }

    public function test_verify_command_cashes_referral_and_records_cash_mutation(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 10, 4));
        $this->seed();
        $initialBalance = (float) KasBumdes::where('id_bumdes', 'BMD-SDR-001')->value('saldo');
        $initialMutations = KasMutasi::count();

        $this->artisan('referral:verify-check')
            ->expectsOutput('1 referral cair, 0 referral gagal.')
            ->assertExitCode(0);

        $this->assertSame('cair', Referral::where('kode_unik', 'PND001')->value('status'));
        $this->assertSame($initialBalance + 10000, (float) KasBumdes::where('id_bumdes', 'BMD-SDR-001')->value('saldo'));
        $this->assertSame($initialMutations + 1, KasMutasi::count());
        $this->assertSame('referral', KasMutasi::latest('id_mutasi')->value('sumber'));
        Carbon::setTestNow();
    }
}
