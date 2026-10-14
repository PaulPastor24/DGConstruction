<?php

namespace App\Services;

use Aws\Rekognition\RekognitionClient;
use Aws\Sts\StsClient;
use RuntimeException;

class FaceAttendanceAwsService
{
    private function sdkConfig(): array
    {
        $options = ['version' => 'latest', 'region' => config('face_attendance.region')];
        if (config('face_attendance.aws_key') && config('face_attendance.aws_secret')) {
            $options['credentials'] = [
                'key' => config('face_attendance.aws_key'),
                'secret' => config('face_attendance.aws_secret'),
            ];
        }
        return $options;
    }

    private function rekognition(): RekognitionClient
    {
        return new RekognitionClient($this->sdkConfig());
    }

    public function createLivenessSession(): string
    {
        $response = $this->rekognition()->createFaceLivenessSession([
            'Settings' => ['AuditImagesLimit' => 0], // do not persist audit images
        ]);
        return (string) $response['SessionId'];
    }

    /** Browser credentials: never expose the long-lived Hostinger IAM key. */
    public function shortLivedStreamingCredentials(): array
    {
        $roleArn = (string) config('face_attendance.stream_role_arn');
        if ($roleArn === '') {
            throw new RuntimeException('FACE_AWS_STREAM_ROLE_ARN is not configured.');
        }

        $sts = new StsClient($this->sdkConfig());
        $response = $sts->assumeRole([
            'RoleArn' => $roleArn,
            'RoleSessionName' => 'face-scan-'.bin2hex(random_bytes(8)),
            'DurationSeconds' => 900, // AWS STS minimum: 15 minutes
            // STS session policy is an intersection with the role's own permissions.
            'Policy' => json_encode([
                'Version' => '2012-10-17',
                'Statement' => [[
                    'Effect' => 'Allow',
                    'Action' => ['rekognition:StartFaceLivenessSession'],
                    'Resource' => '*',
                ]],
            ], JSON_THROW_ON_ERROR),
        ]);
        $c = $response['Credentials'];
        return [
            'accessKeyId' => (string) $c['AccessKeyId'],
            'secretAccessKey' => (string) $c['SecretAccessKey'],
            'sessionToken' => (string) $c['SessionToken'],
            'expiration' => $c['Expiration']->format(DATE_ATOM),
        ];
    }

    /** @return array{confidence: float, image: string} */
    public function completedLiveness(string $sessionId): array
    {
        $response = $this->rekognition()->getFaceLivenessSessionResults(['SessionId' => $sessionId]);
        if (($response['Status'] ?? '') !== 'SUCCEEDED') {
            throw new RuntimeException('The face liveness test has not succeeded. Please rescan.');
        }
        $confidence = (float) ($response['Confidence'] ?? 0);
        if ($confidence < (float) config('face_attendance.liveness_threshold')) {
            throw new RuntimeException('Liveness score too low. Please retry in good lighting.');
        }
        $bytes = $response['ReferenceImage']['Bytes'] ?? null;
        if (! is_string($bytes) || $bytes === '') {
            throw new RuntimeException('The liveness result has no reference image. Please rescan.');
        }
        return ['confidence' => $confidence, 'image' => $bytes];
    }

    /** Returns Rekognition candidates; the controller must validate face IDs against DB. */
    public function search(string $image): array
    {
        $response = $this->rekognition()->searchFacesByImage([
            'CollectionId' => config('face_attendance.collection_id'),
            'Image' => ['Bytes' => $image],
            'FaceMatchThreshold' => (float) config('face_attendance.similarity_threshold'),
            'MaxFaces' => 10,
        ]);
        return array_map(static fn ($entry) => [
            'face_id' => (string) $entry['Face']['FaceId'],
            'similarity' => (float) $entry['Similarity'],
        ], $response['FaceMatches'] ?? []);
    }

    public function index(string $image, int $workerId): string
    {
        $response = $this->rekognition()->indexFaces([
            'CollectionId' => config('face_attendance.collection_id'),
            'Image' => ['Bytes' => $image],
            'ExternalImageId' => 'worker_'.$workerId,
            'MaxFaces' => 1,
            'QualityFilter' => 'AUTO',
        ]);
        $records = $response['FaceRecords'] ?? [];
        if (count($records) !== 1 || empty($records[0]['Face']['FaceId'])) {
            throw new RuntimeException('Enrollment image did not produce one usable face. Please retry.');
        }
        return (string) $records[0]['Face']['FaceId'];
    }

    public function deleteFace(string $faceId): void
    {
        $this->rekognition()->deleteFaces([
            'CollectionId' => config('face_attendance.collection_id'),
            'FaceIds' => [$faceId],
        ]);
    }
}
