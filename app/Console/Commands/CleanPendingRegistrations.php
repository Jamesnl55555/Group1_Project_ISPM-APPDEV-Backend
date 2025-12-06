<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Scheduling\Schedule;

class CleanPendingRegistrations extends Command
{
    protected $signature = 'pending:clean';
    protected $description = 'Delete expired pending registrations';

    public function handle()
    {
        DB::table('pending_registrations')
            ->where('expires_at', '<', now())
            ->delete();

        $this->info('Expired pending registrations cleaned.');
    }

    public function schedule(Schedule $schedule): void
    {
        $schedule->daily();
    }
}
