<?php

namespace App\Console\Commands;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Console\Command;

class SeedMongoDB extends Command
{
    protected $signature = 'seed:mongodb';
    protected $description = 'Seed MongoDB collections for PodWave';

    public function handle()
    {
        try {
            $this->info("Starting MongoDB seeding...");
            $seeder = new DatabaseSeeder();
            $seeder->run();
            
            $this->info("\n✅ PodWave database seeded successfully!");
            $this->info("\n🔐 Login credentials:");
            $this->info("   Admin   → admin@podwave.fm / password");
            $this->info("   Creator → creator@podwave.fm / password");
            $this->info("   Listener→ listener@podwave.fm / password");
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("❌ Seeding failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
