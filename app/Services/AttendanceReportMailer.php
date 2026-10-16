<?php

namespace App\Services;

use App\Mail\DailyAttendanceReport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

class AttendanceReportMailer
{
    public function configurationError(): ?string
    {
        $mailer = config('attendance_reports.mailer', 'mailjet');
        $settings = config('mail.mailers.'.$mailer, []);
        if (($settings['transport'] ?? null) !== 'smtp') {
            return 'Attendance email delivery is not configured. Ask the administrator to configure Mailjet.';
        }
        if (empty($settings['host']) || empty($settings['port']) || empty($settings['username']) || empty($settings['password'])) {
            return $mailer === 'mailjet'
                ? 'Mailjet is not configured. Set MAILJET_API_KEY and MAILJET_SECRET_KEY on the server before sending reports.'
                : 'Attendance report SMTP settings are incomplete. Ask the administrator to configure email delivery.';
        }
        $from = config('attendance_reports.from.address');
        if (! filter_var($from, FILTER_VALIDATE_EMAIL) || str_ends_with(strtolower($from), '@example.com')) {
            return 'Set MAILJET_FROM_ADDRESS to a sender email address verified in Mailjet before sending reports.';
        }

        return null;
    }

    public function send(string $recipient, Carbon $date, $records): void
    {
        if ($error = $this->configurationError()) {
            throw new RuntimeException($error);
        }
        if (! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('The account email address is invalid. Update the account profile before sending reports.');
        }
        Mail::mailer(config('attendance_reports.mailer', 'mailjet'))
            ->to($recipient)->send(new DailyAttendanceReport($date, $records));
    }
}
