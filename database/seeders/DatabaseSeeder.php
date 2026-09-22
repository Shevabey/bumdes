<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RegionSeeder::class);
        $this->call(BumdesSeeder::class);
        $this->call(UnitUsahaSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(AkunSeeder::class);
        $this->call(PelangganSeeder::class);
        $this->call(TransaksiSeeder::class);
        $this->call(TagihanSeeder::class);
        $this->call(IuranBumdesSeeder::class);
    }
}
