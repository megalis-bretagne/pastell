<?php

namespace Pastell\System\Check;

use Pastell\System\CheckInterface;
use Pastell\System\HealthCheckItem;
use VerifEnvironnement;

class PhpExtensionsCheck implements CheckInterface
{
    /**
     * @var VerifEnvironnement
     */
    private $verifEnvironnement;

    public function __construct(VerifEnvironnement $verifEnvironnement)
    {
        $this->verifEnvironnement = $verifEnvironnement;
    }

    public function check(): array
    {
        $phpExtensions = [];
        foreach ($this->verifEnvironnement->checkExtension() as $extension => $value) {
            $phpExtensions[] = (new HealthCheckItem($extension, $extension))->setSuccess($value);
        }
        return $phpExtensions;
    }
}
