<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendTelegramNewVacancy implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public $vacancy_id;
    public function __construct($vacancy_id)
    {
        $this->vacancy_id = $vacancy_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
    }
}
