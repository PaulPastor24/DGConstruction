<?php

namespace App\Services;

use RuntimeException;

class LocalFaceMatcher
{
    public function validateSamples(array $samples): void
    {
        if (count($samples) !== 3 || ! array_is_list($samples)) {
            throw new RuntimeException('Capture three face samples.');
        }
        foreach ($samples as $sample) {
            if (! is_array($sample) || ! array_is_list($sample) || count($sample) !== 1024) {
                throw new RuntimeException('Invalid face template. Start the scan again.');
            }
            $energy = 0;
            foreach ($sample as $value) {
                if ((! is_int($value) && ! is_float($value)) || ! is_finite((float) $value) || abs($value) > 100) {
                    throw new RuntimeException('Invalid face template values.');
                }
                $energy += $value * $value;
            }
            if ($energy < 0.000001) {
                throw new RuntimeException('Empty face template. Please rescan.');
            }
        }
        if ($this->similarity($samples[0], $samples[1]) < 65 || $this->similarity($samples[0], $samples[2]) < 65) {
            throw new RuntimeException('Face changed during capture. Keep one worker in view and retry.');
        }
    }

    // Human FaceRes default distance normalization. Scores are not probabilities.
    public function similarity(array $left, array $right): float
    {
        if (count($left) !== 1024 || count($right) !== 1024) {
            return 0;
        }
        $squared = 0;
        foreach ($left as $i => $value) {
            $squared += ($value - $right[$i]) ** 2;
        }
        if ($squared == 0) {
            return 100;
        }
        $distance = round(2500 * $squared) / 100;

        return round(100 * max(0, min(1, (0.8 - sqrt($distance) / 100) / 0.6)));
    }

    public function score(array $probe, array $enrolled): float
    {
        return array_sum(array_map(fn ($sample) => max(array_map(
            fn ($reference) => $this->similarity($sample, $reference), $enrolled
        )), $probe)) / count($probe);
    }
}
