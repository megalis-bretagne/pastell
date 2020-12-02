<?php

namespace Pastell\Service\SimpleTwigRenderer;

use DonneesFormulaire;
use Twig\TwigFunction;

interface ISimpleTwigFunction
{
    public function getFunctionName(): string;
    public function getFunction(DonneesFormulaire $donneesFormulaire): TwigFunction;
}
