<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleAndPermissionSeeder::class,
            UserSeeder::class,
            AdminStaffSeeder::class,
            CategorySeeder::class,
            SubCategorySeeder::class,
            PendingSellerSeeder::class,
            BannerSeeder::class,
            ProductSeeder::class,
            DummyProductSeeder::class,
            MarketingSeeder::class,
        ]);

    }
}
