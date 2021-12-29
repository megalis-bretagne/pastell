<?php

namespace Pastell\Service;

use Libriciel\Password\Service\PasswordStrengthMeterAnssi;

class PasswordEntropy
{
    // Recommendation de l'ANSI
    // https://www.ssi.gouv.fr/administration/precautions-elementaires/calculer-la-force-dun-mot-de-passe/
    private const DEFAULT_DISPLAY_MIN_ENTROPY = 80;

    private $password_min_entropy;

    public function __construct(int $password_min_entropy)
    {
        $this->password_min_entropy = $password_min_entropy;
    }

    public function getEntropyForDisplay(): int
    {
        if ($this->password_min_entropy) {
            return $this->password_min_entropy;
        }
        return self::DEFAULT_DISPLAY_MIN_ENTROPY;
    }

    public function isPasswordStrongEnough(string $password): bool
    {
        if (! $this->password_min_entropy) {
            return true;
        }
        $pwdMeter = new PasswordStrengthMeterAnssi();
        return $pwdMeter->entropy($password) >= $this->password_min_entropy;
    }
}
