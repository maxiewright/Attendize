<?php

namespace App\Jobs;

use App\Mail\SendOrderAttendeeTicketMail;
use App\Models\Attendee;
use Config;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mail;

class SendOrderAttendeeTicketJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $attendee;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Attendee $attendee)
    {
        $this->attendee = $attendee;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        GenerateTicketJob::dispatchSync($this->attendee);
        $mail = new SendOrderAttendeeTicketMail($this->attendee);
        Mail::to($this->attendee->email)
            ->locale(Config::get('app.locale'))
            ->send($mail);
    }
}
