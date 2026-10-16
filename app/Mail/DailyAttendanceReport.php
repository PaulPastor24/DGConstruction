<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DailyAttendanceReport extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Carbon $date, public $records) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('attendance_reports.from.address'), config('attendance_reports.from.name')),
            subject: 'Daily Attendance Report - '.$this->date->format('M d, Y'),
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.daily-attendance-report');
    }
}
