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
   ```
4. Set `FACE_ATTENDANCE_ENABLED=true`. Remove previous AWS-specific face settings. Replace any old AWS similarity threshold (98) with `FACE_SIMILARITY_THRESHOLD=65` and `FACE_AMBIGUITY_MARGIN=8`.
5. Keep the existing `APP_KEY` stable and backed up: changing it makes encrypted enrollments unreadable. Do not generate a new key during deployment.
6. Open Supervisor > Attendance > Facial Attendance over HTTPS. Grant camera permission, enroll each assigned worker, then scan and confirm Time In. Existing AWS templates cannot be converted; workers must enroll again.

The local migration adds columns and preserves historical AWS records. Deleting/replacing a local enrollment does not delete old AWS cloud data. If cloud enrollments were previously created, remove those separately when AWS account access is restored.

## Scope and limits

- This is **supervisor-verified camera assistance**, not certified liveness or independently trusted biometric authentication. A modified browser can fabricate descriptors. Photos/video can fool matching; a supervisor must check the physical person. Records use `biometric_matched=0` and explicit camera-assistance remarks.
- Face descriptors are sensitive biometric templates, encrypted in the database. They are never sent back as a worker directory. Camera frames remain in browser memory; the confirmation preview is not uploaded or persisted.
- Three samples must agree. Unknown and near-tied worker matches fail. Thresholds are initial prototype settings, not validated production accuracy: evaluate actual workers on target devices before using results for payroll.
- Enrollment requires an active project assignment and in-person identity/privacy acknowledgement. Attendance proofs expire after two minutes and are consumed once; scans expire after five minutes.
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
