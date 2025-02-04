<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TicketStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ticket_statuses = [
            [
                'id' => 1,
                'name' => 'Sold Out',
            ],
            [
                'id' => 2,
                'name' => 'Sales Have Ended',
            ],
            [
                'id' => 3,
                'name' => 'Not On Sale Yet',
            ],
            [
                'id' => 4,
                'name' => 'On Sale',
            ],
            [
                'id' => 5,
                'name' => 'On Sale',
            ],
        ];

        DB::table('ticket_statuses')->insert($ticket_statuses);
    }
}
