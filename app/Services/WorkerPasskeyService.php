<?php

namespace App\Services;

use RuntimeException;
use Spatie\LaravelPasskeys\Actions\ConfigureCeremonyStepManagerFactoryAction;
use Spatie\LaravelPasskeys\Support\CredentialRecordConverter;
use Spatie\LaravelPasskeys\Support\Serializer;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialDescriptor;
use Webauthn\PublicKeyCredentialParameters;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\PublicKeyCredentialUserEntity;

class WorkerPasskeyService
{
    public function createRegistrationOptions(string $firstName, string $lastName): array
    {
        $displayName = trim($firstName.' '.$lastName);
        $relyingParty = new PublicKeyCredentialRpEntity(name: '', id: $this->relyingPartyId());
        $relyingParty->name = $this->relyingPartyName();

        $options = new PublicKeyCredentialCreationOptions(
            rp: $relyingParty,
            user: new PublicKeyCredentialUserEntity(
                name: strtolower(str_replace(' ', '.', $displayName)).'@workers.local',
                id: random_bytes(16),
                displayName: $displayName,
            ),
            challenge: random_bytes(32),
            pubKeyCredParams: [
                PublicKeyCredentialParameters::createPk(-7),
                PublicKeyCredentialParameters::createPk(-257),
            ],
            authenticatorSelection: new AuthenticatorSelectionCriteria(
                authenticatorAttachment: AuthenticatorSelectionCriteria::AUTHENTICATOR_ATTACHMENT_PLATFORM,
                userVerification: AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_PREFERRED,
                residentKey: AuthenticatorSelectionCriteria::RESIDENT_KEY_REQUIREMENT_PREFERRED,
            ),
            attestation: PublicKeyCredentialCreationOptions::ATTESTATION_CONVEYANCE_PREFERENCE_NONE,
            timeout: 60000,
        );

        $optionsJson = Serializer::make()->toJson($options);
        session(['worker_webauthn_registration_options' => $optionsJson]);

        return json_decode($optionsJson, true, flags: JSON_THROW_ON_ERROR);
    }

    public function verifyRegistration(array $payload): PublicKeyCredentialSource
    {
        $optionsJson = session()->pull('worker_webauthn_registration_options');

        if (! is_string($optionsJson) || $optionsJson === '') {
            throw new RuntimeException('Fingerprint registration expired. Start fingerprint capture again.');
        }

        $serializer = Serializer::make();
        $credential = $serializer->fromJson(
            json_encode($payload, JSON_THROW_ON_ERROR),
            PublicKeyCredential::class,
        );

        if (! $credential instanceof PublicKeyCredential) {
            throw new RuntimeException('Registration response could not be decoded as a WebAuthn credential.');
        }

        if (! $credential->response instanceof AuthenticatorAttestationResponse) {
            throw new RuntimeException('The authenticator did not return a registration response.');
        }

        $options = $serializer->fromJson($optionsJson, PublicKeyCredentialCreationOptions::class);
        $validator = AuthenticatorAttestationResponseValidator::create(
            (new ConfigureCeremonyStepManagerFactoryAction)->execute()->creationCeremony(),
        );
        $credentialRecord = $validator->check(
            $credential->response,
            $options,
            $this->clientHost(),
        );

        return CredentialRecordConverter::toPublicKeyCredentialSource($credentialRecord);
    }

    public function createAuthenticationOptions(iterable $workers): array
    {
        $allowedCredentials = [];

        foreach ($workers as $worker) {
            try {
                $allowedCredentials[] = new PublicKeyCredentialDescriptor(
                    type: PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY,
                    id: $this->base64UrlDecode((string) $worker->credential_id),
                );
            } catch (RuntimeException) {
                continue;
            }
        }

        $options = new PublicKeyCredentialRequestOptions(
            challenge: random_bytes(32),
            rpId: $this->relyingPartyId(),
            allowCredentials: $allowedCredentials,
            userVerification: PublicKeyCredentialRequestOptions::USER_VERIFICATION_REQUIREMENT_PREFERRED,
            timeout: 60000,
        );

        $optionsJson = Serializer::make()->toJson($options);
        session(['worker_webauthn_authentication_options' => $optionsJson]);

        return json_decode($optionsJson, true, flags: JSON_THROW_ON_ERROR);
    }

    public function verifyAssertion(array $payload, object $worker): PublicKeyCredentialSource
    {
        $optionsJson = session()->pull('worker_webauthn_authentication_options');

        if (! is_string($optionsJson) || $optionsJson === '') {
            throw new RuntimeException('Biometric verification expired. Start the scan again.');
        }

        $serializer = Serializer::make();
        $credential = $serializer->fromJson(
            json_encode($payload, JSON_THROW_ON_ERROR),
            PublicKeyCredential::class,
        );

        if (! $credential instanceof PublicKeyCredential) {
            throw new RuntimeException('Authentication response could not be decoded as a WebAuthn credential.');
        }

        if (! $credential->response instanceof AuthenticatorAssertionResponse) {
            throw new RuntimeException('The authenticator did not return an assertion response.');
        }

        $receivedCredentialId = $this->base64UrlEncode($credential->rawId);

        if (! hash_equals((string) $worker->credential_id, $receivedCredentialId)) {
            throw new RuntimeException('The biometric credential does not match this worker.');
        }

        $credentialSource = $this->credentialSourceForWorker($worker, $serializer);
        $options = $serializer->fromJson($optionsJson, PublicKeyCredentialRequestOptions::class);
        $validator = AuthenticatorAssertionResponseValidator::create(
            (new ConfigureCeremonyStepManagerFactoryAction)->execute()->requestCeremony(),
        );
        $verifiedRecord = $validator->check(
            CredentialRecordConverter::toCredentialRecord($credentialSource),
            $credential->response,
            $options,
            $this->clientHost(),
            $credentialSource->userHandle,
        );

        return CredentialRecordConverter::toPublicKeyCredentialSource($verifiedRecord);
    }

    public function credentialId(PublicKeyCredentialSource $credentialSource): string
    {
        return $this->base64UrlEncode($credentialSource->publicKeyCredentialId);
    }

    public function serializeCredentialSource(PublicKeyCredentialSource $credentialSource): string
    {
        return Serializer::make()->toJson($credentialSource);
    }

    /**
     * A successful WebAuthn assertion can only be checked against a previously
     * verified credential source containing the authenticator's public key.
     *
     * Older installations stored the raw browser registration response in
     * credential_json. That is not a verified server-side credential source,
     * so never reinterpret it as one or treat a Face ID prompt as proof.
     */
    private function credentialSourceForWorker(object $worker, Serializer $serializer): PublicKeyCredentialSource
    {
        try {
            $source = $serializer->fromJson(
                (string) $worker->credential_json,
                PublicKeyCredentialSource::class,
            );
        } catch (\Throwable $error) {
            throw new RuntimeException(
                'Stored worker passkey record is invalid. Re-enroll this worker before scanning.',
                0,
                $error,
            );
        }

        if (! $source instanceof PublicKeyCredentialSource) {
            throw new RuntimeException(
                'Stored worker passkey record uses an unsupported format. Re-enroll this worker before scanning.',
            );
        }

        $expectedId = $this->base64UrlEncode($source->publicKeyCredentialId);
        if (! hash_equals((string) $worker->credential_id, $expectedId)) {
            throw new RuntimeException(
                'Stored worker passkey does not match this worker. Re-enroll the correct worker.',
            );
        }

        return $source;
    }

    private function relyingPartyId(): string
    {
        $configuredId = strtolower(trim((string) config('passkeys.relying_party.id', '')));
        $currentHost = strtolower(request()->getHost());

        if (str_ends_with($configuredId, 'ngrok-free.dev') || str_ends_with($configuredId, 'asse.devtunnels.ms')) {
            return $configuredId;
        }

        if ($configuredId !== '' && ($configuredId === $currentHost || str_ends_with($currentHost, '.'.$configuredId))) {
            return $configuredId;
        }

        return $currentHost;
    }

    private function clientHost(): string
    {
        return request()->getHost();
    }

    private function relyingPartyName(): string
    {
        return (string) config('passkeys.relying_party.name', 'D&G Construction Inc.');
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $value): string
    {
        $decoded = base64_decode(
            strtr($value, '-_', '+/').str_repeat('=', (4 - strlen($value) % 4) % 4),
            true,
        );

        if ($decoded === false || $decoded === '') {
            throw new RuntimeException('Invalid biometric credential identifier.');
        }

        return $decoded;
    }
}