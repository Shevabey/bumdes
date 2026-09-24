<?php

namespace Tests\Feature;

use App\Models\Feedback;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeedbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_feedback_seeder_creates_statuses_and_bumdes_unit_relations(): void
    {
        $this->seed();

        $feedback = Feedback::with(['pengirim', 'bumdes', 'unit'])->get();

        $this->assertCount(3, $feedback);
        $this->assertSame(1, $feedback->where('status_tindak_lanjut', 'belum')->count());
        $this->assertSame(1, $feedback->where('status_tindak_lanjut', 'sedang')->count());
        $this->assertSame(1, $feedback->where('status_tindak_lanjut', 'selesai')->count());
        $this->assertSame('pengawas1', $feedback->firstWhere('id_feedback', 'FB-000001')->pengirim->username);
        $this->assertNull($feedback->firstWhere('id_feedback', 'FB-000001')->unit);
        $this->assertSame('PAMDes SDS', $feedback->firstWhere('id_feedback', 'FB-000002')->unit->nama_unit);
        $this->assertTrue($feedback->every(fn (Feedback $note): bool => $note->bumdes !== null && $note->pengirim !== null));
    }
}
