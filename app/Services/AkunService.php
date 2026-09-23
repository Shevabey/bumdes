<?php

namespace App\Services;

use App\Models\Akun;
use App\Models\Pelanggan;
use App\Models\UnitUsaha;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;

class AkunService
{
    private const ROLES = [
        'super_admin',
        'pengawas',
        'penasihat',
        'direktur',
        'admin_bumdes',
        'sekretaris',
        'bendahara',
        'admin_unit',
        'pengguna',
    ];

    private const GLOBAL_ROLES = [
        'super_admin',
        'pengawas',
        'penasihat',
        'direktur',
    ];

    private const BUMDES_ROLES = [
        'admin_bumdes',
        'sekretaris',
        'bendahara',
    ];

    public function create(Akun $creator, array $data): Akun
    {
        $role = $data['role'] ?? null;

        if (! is_string($role) || ! in_array($role, self::ROLES, true)) {
            throw new InvalidArgumentException('Role akun target tidak valid.');
        }

        $this->authorizeCreator($creator, $role, $data);

        return DB::transaction(function () use ($data, $role): Akun {
            $pelanggan = $this->pelangganForRole($role, $data);
            $payload = $this->payloadForRole($role, $data, $pelanggan);

            $akun = Akun::create([
                'id_akun' => $data['id_akun'] ?? $this->nextId(),
                'nama' => $data['nama'] ?? throw new InvalidArgumentException('Nama akun wajib diisi.'),
                'username' => $data['username'] ?? throw new InvalidArgumentException('Username akun wajib diisi.'),
                'password_hash' => Hash::make($data['password'] ?? throw new InvalidArgumentException('Password akun wajib diisi.')),
                'role' => $role,
                'id_bumdes' => $payload['id_bumdes'],
                'id_unit' => $payload['id_unit'],
                'status_aktif' => $data['status_aktif'] ?? true,
            ]);

            $akun->syncRoles([$role]);

            if ($pelanggan !== null) {
                if ($pelanggan->id_akun !== null) {
                    throw new InvalidArgumentException('Pelanggan sudah memiliki akun login.');
                }

                $pelanggan->update(['id_akun' => $akun->id_akun]);
            }

            return $akun;
        });
    }

    private function authorizeCreator(Akun $creator, string $targetRole, array $data): void
    {
        if ($creator->hasRole('super_admin')) {
            return;
        }

        if ($creator->hasRole('admin_bumdes')) {
            if (! in_array($targetRole, ['sekretaris', 'bendahara', 'admin_unit'], true)) {
                throw new AuthorizationException('Admin BUMDes hanya dapat membuat akun tim internal.');
            }

            $targetBumdes = $data['id_bumdes'] ?? null;

            if ($targetBumdes !== $creator->id_bumdes) {
                throw new AuthorizationException('Admin BUMDes hanya dapat membuat akun di BUMDes sendiri.');
            }

            return;
        }

        if ($creator->hasRole('sekretaris') || $creator->hasRole('admin_unit')) {
            if ($targetRole !== 'pengguna') {
                throw new AuthorizationException('Sekretaris dan Admin Unit hanya dapat membuat akun pelanggan.');
            }

            $this->authorizePelangganScope($creator, $data);

            return;
        }

        throw new AuthorizationException('Role pembuat tidak berwenang membuat akun.');
    }

    private function authorizePelangganScope(Akun $creator, array $data): void
    {
        $pelanggan = Pelanggan::query()
            ->with('unit')
            ->where('id_pelanggan', $data['id_pelanggan'] ?? '')
            ->firstOrFail();

        if ($creator->hasRole('admin_unit') && $pelanggan->id_unit !== $creator->id_unit) {
            throw new AuthorizationException('Admin Unit hanya dapat memberi akses pelanggan di unit sendiri.');
        }

        if ($creator->hasRole('sekretaris') && $pelanggan->unit->id_bumdes !== $creator->id_bumdes) {
            throw new AuthorizationException('Sekretaris hanya dapat memberi akses pelanggan di BUMDes sendiri.');
        }
    }

    private function payloadForRole(string $role, array $data, ?Pelanggan $pelanggan): array
    {
        if (in_array($role, self::GLOBAL_ROLES, true)) {
            return ['id_bumdes' => null, 'id_unit' => null];
        }

        if ($role === 'admin_unit') {
            $unit = UnitUsaha::query()
                ->where('id_unit', $data['id_unit'] ?? '')
                ->firstOrFail();

            if (($data['id_bumdes'] ?? null) !== $unit->id_bumdes) {
                throw new InvalidArgumentException('Unit target harus berada di BUMDes target.');
            }

            return ['id_bumdes' => $unit->id_bumdes, 'id_unit' => $unit->id_unit];
        }

        if ($role === 'pengguna') {
            if ($pelanggan === null) {
                throw new InvalidArgumentException('Pelanggan target wajib diisi untuk akun pengguna.');
            }

            return ['id_bumdes' => $pelanggan->unit->id_bumdes, 'id_unit' => $pelanggan->id_unit];
        }

        if (in_array($role, self::BUMDES_ROLES, true)) {
            return [
                'id_bumdes' => $data['id_bumdes'] ?? throw new InvalidArgumentException('BUMDes target wajib diisi.'),
                'id_unit' => null,
            ];
        }

        throw new InvalidArgumentException('Role akun target tidak valid.');
    }

    private function pelangganForRole(string $role, array $data): ?Pelanggan
    {
        if ($role !== 'pengguna') {
            return null;
        }

        return Pelanggan::query()
            ->with('unit')
            ->where('id_pelanggan', $data['id_pelanggan'] ?? '')
            ->firstOrFail();
    }

    private function nextId(): string
    {
        $lastId = Akun::query()
            ->where('id_akun', 'like', 'AKN-%')
            ->orderByDesc('id_akun')
            ->value('id_akun');

        $next = ((int) str_replace('AKN-', '', $lastId ?? 'AKN-000000')) + 1;

        return sprintf('AKN-%06d', $next);
    }
}
