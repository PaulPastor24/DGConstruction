<?php

namespace Spatie\LaravelPasskeys\Exceptions;

use Exception;

class InvalidAuthenticatableModel extends Exception
{
    public static function missingInterface(string $modelClass, string $interfaceFqcn): self
    {
        return new self("The model `{$modelClass}` does not use the `{$interfaceFqcn}` interface.");
    }
}
