<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FacilitiesFromVisitDataSeeder extends Seeder
{
    public function run(): void
    {
        // Determine source table: VisitData or stage_VisitData
        $source = null;
        if (Schema::hasTable('VisitData')) {
            $source = 'VisitData';
        } elseif (Schema::hasTable('stage_VisitData')) {
            $source = 'stage_VisitData';
        }

        if (!$source) {
            $this->command?->warn('No VisitData or stage_VisitData table found. Skipping FacilitiesFromVisitDataSeeder.');
            return;
        }

        // Fetch distinct HFID/HFName rows
        $rows = DB::table($source)
            ->select('HFID', 'HFName')
            ->whereNotNull('HFID')
            ->whereNotNull('HFName')
            ->distinct()
            ->orderBy('HFName')
            ->get();

        $count = 0;
        foreach ($rows as $r) {
            // Use HFID as the facility id to keep mapping consistent where possible
            DB::table('hfacilities')->updateOrInsert(
                ['id' => (int)$r->HFID],
                ['name' => (string)$r->HFName]
            );
            $count++;
        }

        $this->command?->info("Seeded/updated {$count} facilities from {$source} into hfacilities.");
    }
}

