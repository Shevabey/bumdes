<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (
            [
                'super_admin',
                'pengawas',
                'penasihat',
                'direktur',
                'admin_bumdes',
                'sekretaris',
                'bendahara',
                'admin_unit',
                'pengguna',
            ] as $roleName
        ) {
            Role::findOrCreate($roleName, 'web');
        }
    }
}
