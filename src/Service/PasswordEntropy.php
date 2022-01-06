<?php

namespace Pastell\Service;

use Libriciel\Password\Service\PasswordStrengthMeterAnssi;
use Libriciel\Password\Service\PasswordStrengthMeterInterface;

class PasswordEntropy
{
    // Recommendation de l'ANSI
    // https://www.ssi.gouv.fr/administration/precautions-elementaires/calculer-la-force-dun-mot-de-passe/
    private const DEFAULT_DISPLAY_MIN_ENTROPY = 80;

    private $password_min_entropy;
    private $passwordStrengthMeter;

    public function __construct(int $password_min_entropy)
    {
        $this->password_min_entropy = $password_min_entropy;
        $this->passwordStrengthMeter = new PasswordStrengthMeterAnssi();
    }

    public function setPasswordStrengthMeterInterface(PasswordStrengthMeterInterface $passwordStrengthMeter)
    {
        $this->passwordStrengthMeter = $passwordStrengthMeter;
    }

    public function getEntropyForDisplay(): int
    {
        if ($this->password_min_entropy !== 0) {
            return $this->password_min_entropy;
        }
        return self::DEFAULT_DISPLAY_MIN_ENTROPY;
    }

    public function isPasswordStrongEnough(string $password): bool
    {
        return $this->passwordStrengthMeter->entropy($password) >= $this->password_min_entropy;
    }
}
