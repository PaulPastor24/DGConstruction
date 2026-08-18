<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DailyAttendanceReport extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $reportDate,
        public array $summary,
        public $records,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Daily Attendance Report - {$this->reportDate}");
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.attendance.daily');
    }
}
