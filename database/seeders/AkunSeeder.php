<?php

namespace Database\Seeders;

use App\Models\Akun;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AkunSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['id_akun' => 'AKN-000001', 'nama' => 'Super Admin', 'username' => 'superadmin', 'role' => 'super_admin', 'id_bumdes' => null, 'id_unit' => null],
            ['id_akun' => 'AKN-000002', 'nama' => 'Pengawas 1', 'username' => 'pengawas1', 'role' => 'pengawas', 'id_bumdes' => null, 'id_unit' => null],
            ['id_akun' => 'AKN-000003', 'nama' => 'Penasihat 1', 'username' => 'penasihat1', 'role' => 'penasihat', 'id_bumdes' => null, 'id_unit' => null],
            ['id_akun' => 'AKN-000004', 'nama' => 'Direktur 1', 'username' => 'direktur1', 'role' => 'direktur', 'id_bumdes' => null, 'id_unit' => null],
            ['id_akun' => 'AKN-000005', 'nama' => 'Admin BUMDes Sendangsari', 'username' => 'admin.sendangsari', 'role' => 'admin_bumdes', 'id_bumdes' => 'BMD-SDS-001', 'id_unit' => null],
            ['id_akun' => 'AKN-000006', 'nama' => 'Admin BUMDes Sendangrejo', 'username' => 'admin.sendangrejo', 'role' => 'admin_bumdes', 'id_bumdes' => 'BMD-SDR-001', 'id_unit' => null],
            ['id_akun' => 'AKN-000007', 'nama' => 'Sekretaris Sendangsari', 'username' => 'sekretaris.sds', 'role' => 'sekretaris', 'id_bumdes' => 'BMD-SDS-001', 'id_unit' => null],
            ['id_akun' => 'AKN-000008', 'nama' => 'Bendahara Sendangsari', 'username' => 'bendahara.sds', 'role' => 'bendahara', 'id_bumdes' => 'BMD-SDS-001', 'id_unit' => null],
            ['id_akun' => 'AKN-000009', 'nama' => 'Admin PAMDes Sendangsari', 'username' => 'admin.pamdes.sds', 'role' => 'admin_unit', 'id_bumdes' => 'BMD-SDS-001', 'id_unit' => 'UNT-BMD-SDS-001-PAM-01'],
            ['id_akun' => 'AKN-000010', 'nama' => 'Pengguna Sendangsari', 'username' => 'pengguna.sds', 'role' => 'pengguna', 'id_bumdes' => 'BMD-SDS-001', 'id_unit' => 'UNT-BMD-SDS-001-PAM-01'],
        ];

        foreach ($accounts as $data) {
            $akun = Akun::updateOrCreate(
                ['id_akun' => $data['id_akun']],
                [...$data, 'password_hash' => Hash::make('password'), 'status_aktif' => true],
            );

            $akun->syncRoles([$data['role']]);
        }
    }
}
