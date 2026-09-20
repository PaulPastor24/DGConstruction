<?php

namespace Spatie\LaravelPasskeys\Actions;

use Webauthn\CeremonyStep\CeremonyStepManagerFactory;

class ConfigureCeremonyStepManagerFactoryAction
{
    public function execute(): CeremonyStepManagerFactory
    {
        $csmFactory = new CeremonyStepManagerFactory;

        return $csmFactory;
    }
}
