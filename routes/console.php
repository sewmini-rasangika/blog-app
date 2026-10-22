<?php
 
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;


Schedule::command('sanctum:prune-expired --hours=24')->daily();
 
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('posts:remind-pending --days=2')
    ->dailyAt('09:00')
    ->timezone('Asia/Colombo')
    ->withoutOverlapping();