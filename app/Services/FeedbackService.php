<?php

namespace App\Services;

use App\Models\Akun;
use App\Models\Bumdes;
use App\Models\Feedback;
use App\Models\UnitUsaha;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class FeedbackService
{
    private const MONITORING_ROLES = [
        'super_admin',
        'pengawas',
        'penasihat',
        'direktur',
    ];

    private const STATUSES = [
        'belum',
        'sedang',
        'selesai',
    ];

    public function create(Akun $creator, array $data, ?Carbon $tanggal = null): Feedback
    {
        $this->authorizeCreator($creator);

        $keIdBumdes = $data['ke_id_bumdes'] ?? null;
        if (! is_string($keIdBumdes) || ! Bumdes::where('id_bumdes', $keIdBumdes)->exists()) {
            throw new InvalidArgumentException('BUMDes target wajib diisi dan harus valid.');
        }

        $keIdUnit = $data['ke_id_unit'] ?? null;
        if ($keIdUnit !== null) {
            $unit = UnitUsaha::where('id_unit', $keIdUnit)->first();
            if ($unit === null || $unit->id_bumdes !== $keIdBumdes) {
                throw new InvalidArgumentException('Unit target harus berada di BUMDes target.');
            }
        }

        $isiCatatan = trim((string) ($data['isi_catatan'] ?? ''));
        if ($isiCatatan === '') {
            throw new InvalidArgumentException('Isi catatan feedback wajib diisi.');
        }

        $status = $data['status_tindak_lanjut'] ?? 'belum';
        if (! in_array($status, self::STATUSES, true)) {
            throw new InvalidArgumentException('Status tindak lanjut tidak valid.');
        }

        return DB::transaction(function () use ($creator, $keIdBumdes, $keIdUnit, $isiCatatan, $status, $tanggal, $data): Feedback {
            return Feedback::create([
                'id_feedback' => $data['id_feedback'] ?? $this->nextId(),
                'dari_id_akun' => $creator->id_akun,
                'ke_id_bumdes' => $keIdBumdes,
                'ke_id_unit' => $keIdUnit,
                'isi_catatan' => $isiCatatan,
                'status_tindak_lanjut' => $status,
                'tanggal' => ($tanggal ?? Carbon::now())->toDateTimeString(),
            ]);
        });
    }

    public function updateStatus(Feedback $feedback, Akun $actor, string $statusBaru): Feedback
    {
        $this->authorizeStatusUpdater($actor, $feedback);

        if (! in_array($statusBaru, self::STATUSES, true)) {
            throw new InvalidArgumentException('Status tindak lanjut tidak valid. Pilih belum, sedang, atau selesai.');
        }

        $feedback->update(['status_tindak_lanjut' => $statusBaru]);

        return $feedback->refresh();
    }

    private function authorizeCreator(Akun $creator): void
    {
        if (! $creator->hasAnyRole(self::MONITORING_ROLES)) {
            throw new AuthorizationException('Hanya Pengawas, Penasihat, Direktur, atau Super Admin yang dapat mengirim feedback.');
        }
    }

    private function authorizeStatusUpdater(Akun $actor, Feedback $feedback): void
    {
        if ($actor->hasRole('super_admin')) {
            return;
        }

        if ($actor->hasRole('admin_bumdes') && $actor->id_bumdes === $feedback->ke_id_bumdes) {
            return;
        }

        if ($actor->hasRole('admin_unit') && $feedback->ke_id_unit !== null && $actor->id_unit === $feedback->ke_id_unit) {
            return;
        }

        throw new AuthorizationException('Akun tidak berwenang memperbarui status tindak lanjut feedback ini.');
    }

    private function nextId(): string
    {
        $lastId = Feedback::query()
            ->where('id_feedback', 'like', 'FB-%')
            ->orderByDesc('id_feedback')
            ->value('id_feedback');

        $next = ((int) str_replace('FB-', '', $lastId ?? 'FB-000000')) + 1;

        return sprintf('FB-%06d', $next);
    }
}
