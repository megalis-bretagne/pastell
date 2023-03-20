<?php

namespace Pastell\Service\FeatureToggle;

use Pastell\Service\FeatureToggleDefault;

class CertificateAuthentication extends FeatureToggleDefault
{
    public function getDescription(): string
    {
        return "Permet d'authentifier les utilisateurs à partir de certificat";
    }
}
