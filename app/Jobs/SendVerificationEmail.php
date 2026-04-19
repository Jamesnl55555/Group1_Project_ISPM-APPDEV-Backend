<?php

namespace App\Jobs;

use App\Services\BrevoMailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendVerificationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $subject;
    public $html;
    public $email;
    public $name;
    public $code;

    public function __construct($email, $name, $subject, $html)
    {
        $this->email = $email;
        $this->name = $name;
        $this->subject = $subject;
        $this->html = $html;
    }

    public function handle(BrevoMailService $mail)
    {
        $mail->sendEmail(
        $this->email,
        $this->name,
        $this->subject,
        $this->html
        );

    }
}