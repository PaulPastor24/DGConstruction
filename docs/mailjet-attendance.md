# Attendance report email with Mailjet

Attendance report emails use the dedicated `mailjet` SMTP mailer. Other application emails retain their existing default mailer.

When an administrator clicks **Send report to my email**, the controller reloads that administrator's account and sends to its current `users.email`. A submitted `email`, `recipient`, or sender setting cannot choose a different recipient. The selected report date and attendance content remain unchanged. Scheduled daily reports use the same mailer and send separately to each enabled administrator's database email.

## Hostinger setup

1. Create a Mailjet account and verify your sending address or domain in that account.
2. Open Mailjet's SMTP and SEND API settings. Copy the API key and secret key. These are the SMTP credentials; the Mailjet account login email/password are not SMTP credentials.
3. Deploy the changed source files, including `config/attendance_reports.php` and `app/Services/AttendanceReportMailer.php`.
4. Copy the settings in `docs/.env.mailjet.example` into Hostinger's existing `.env`. Replace the placeholder keys and sender address with actual values **only on the server**. Do not commit `.env` or keys to GitHub.
5. Run `php artisan optimize:clear`. If your deployment normally caches configuration, run `php artisan config:cache` afterward. Restart long-running queue workers if used.
6. Sign in as an administrator, check their saved account email, select a report date, and click **Send report to my email**. Check Mailjet's delivery logs and the recipient's inbox/spam folder.

`MAILJET_FROM_ADDRESS` is the verified sender (for example a verified reports address at your domain). It is not the recipient. Do not replace it with the administrator's address unless that address is itself your verified sender. The actual recipient is always chosen from the signed-in account.

Default connection: `in-v3.mailjet.com`, port `587`, scheme `smtp` with STARTTLS required. For implicit TLS on port 465, use `MAILJET_PORT=465` and `MAILJET_SCHEME=smtps`. A stale `MAIL_URL` for Brevo does not override this dedicated mailer.

Official setup references:
- https://documentation.mailjet.com/hc/en-us/articles/360043229473-How-can-I-configure-my-SMTP-parameters
- https://dev.mailjet.com/docs/smtp-relay/configuration

No additional Composer package, Node build, or database migration is required. Live delivery requires actual Mailjet credentials, sender verification, and outbound SMTP connectivity from Hostinger. Automated tests use fake mail and do not send real messages.

Verify locally with `php vendor/phpunit/phpunit/phpunit --do-not-cache-result --filter AttendanceReportTest`.
