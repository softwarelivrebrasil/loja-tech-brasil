<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * apps/admin never writes identity data — apps/backend's own
     * DatabaseSeeder does, against the same shared users/admins tables.
     * See .claude/rules/database.md.
     */
    public function run(): void
    {
        //
    }
}
