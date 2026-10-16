# Free camera attendance (capstone)

AWS is no longer used by facial attendance. The phone extracts Human 3.3.6 FaceRes descriptors; Laravel compares them to encrypted local enrollments and the authenticated supervisor confirms the person before saving Time In. One phone can scan workers sequentially.

## Setup / deployment

1. Run `npm ci` and `npm run build`.
2. Deploy **both** `public/build` and `public/models/human` alongside the PHP changes. The build script copies the three required model pairs from the pinned npm package; no external model CDN or API key is required at runtime.
3. Run these additive migrations (or your normal migration deployment):
   ```sh
   php artisan migrate --path=database/migrations/2026_10_14_000001_create_worker_face_enrollments.php
   php artisan migrate --path=database/migrations/2026_10_14_000002_create_worker_face_attempts.php
   php artisan migrate --path=database/migrations/2026_10_16_000001_add_local_face_templates.php
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   ```
4. Set `FACE_ATTENDANCE_ENABLED=true`. Remove previous AWS-specific face settings. Replace any old AWS similarity threshold (98) with `FACE_SIMILARITY_THRESHOLD=65` and `FACE_AMBIGUITY_MARGIN=8`.
5. Keep the existing `APP_KEY` stable and backed up: changing it makes encrypted enrollments unreadable. Do not generate a new key during deployment.
6. Open Supervisor > Attendance and tap the main face icon under **Ready to Identify**. The camera opens directly inside the attendance panel, without a modal, iframe, or operation menu. Grant camera permission, capture the worker, and confirm Time In; the attendance table refreshes after saving. Close camera or finish saving to return to the face button for the next worker.
7. For an existing worker, use **Enrolled Workers > Edit > Enroll / update face**. The enrollment camera opens directly without an identity checkbox. Capturing replaces the face on that same worker record; switching phones does not require another enrollment. Previous passkeys/AWS templates cannot be converted to local camera templates.
8. For a genuinely new worker, use **Register Worker** and enter their details and assigned project. The camera opens and scans the face automatically; there is no Capture worker face button during enrollment. Save Worker Record stays disabled until capture succeeds. Laravel validates all three samples, checks for an already-enrolled face, and saves the worker, deployment, and encrypted face enrollment together. Updating an existing worker's face also scans and saves automatically. No device passkey or identity checkbox is required. Registration does not record Time In.
9. Privacy acknowledgement is handled in the workers' hiring contracts; no separate privacy checkbox is shown. Run `php artisan migrate --path=database/migrations/2026_10_16_000002_allow_contract_based_face_enrollment.php --force` when deploying this update. New records do not claim an in-app privacy acknowledgement; existing acknowledgement timestamps are preserved.

The local migration adds columns and preserves historical AWS records. Deleting/replacing a local enrollment does not delete old AWS cloud data. If cloud enrollments were previously created, remove those separately when AWS account access is restored.

## Scope and limits

- This is **supervisor-verified camera assistance**, not certified liveness or independently trusted biometric authentication. A modified browser can fabricate descriptors. Photos/video can fool matching; a supervisor must check the physical person. Records use `biometric_matched=0` and explicit camera-assistance remarks.
- Face descriptors are sensitive biometric templates, encrypted in the database. They are never sent back as a worker directory. Camera frames remain in browser memory; the confirmation preview is not uploaded or persisted.
- Three samples must agree. Unknown and near-tied worker matches fail. Thresholds are initial prototype settings, not validated production accuracy: evaluate actual workers on target devices before using results for payroll.
- Enrollment requires an active project assignment and three consistent face samples. Attendance still requires the supervisor to confirm the matched person before saving Time In. Attendance reuses active assigned workers' saved database templates across supervisor sessions and phones. Attendance proofs expire after two minutes and are consumed once; scans expire after five minutes.
- Supports Time In. Use the existing attendance screen for breaks, Time Out, and manual corrections.
- Safari on iPhone and Chrome on Android require physical-device testing. A laptop served at an HTTP LAN IP is not a secure camera context; use HTTPS when accessing it from a phone.
- Test low light, two faces, unknown workers, similar-looking workers, camera denial, scan cancellation, duplicate Time In, midnight date changes, and revoked project assignments.
- No paid API integration is required. Hosting, mobile data, and maintenance remain your responsibility.

## Dependencies and credits

Human: https://github.com/vladmandic/human (MIT; license copied with model assets).
Models and upstream credits: https://github.com/vladmandic/human/wiki/Models
FaceRes normalization reference: https://github.com/vladmandic/human/blob/main/src/face/match.ts
Review upstream model terms before any commercial rollout beyond the capstone.

## Verification

`php vendor/phpunit/phpunit/phpunit --do-not-cache-result --filter 'LocalFaceAttendanceTest|AttendanceReportTest'`

`npm run build`
