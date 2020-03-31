<?php

namespace Pastell\Service\Droit;

use RoleUtilisateur;

class DroitService
{
    public const DROIT_LECTURE = "lecture";
    public const DROIT_ECRITURE = "edition";

    public const DROIT_CONNECTEUR = "connecteur";
    public const DROIT_ENTITE = "entite";

    public static function getDroitLecture(string $part): string
    {
        return sprintf("%s:%s", $part, self::DROIT_LECTURE);
    }

    public static function getDroitEdition(string $part): string
    {
        return sprintf("%s:%s", $part, self::DROIT_ECRITURE);
    }

    private $roleUtilisateur;
    private $connecteur_droit;

    public function __construct(RoleUtilisateur $roleUtilisateur, bool $connecteur_droit = false)
    {
        $this->roleUtilisateur = $roleUtilisateur;
        $this->connecteur_droit = $connecteur_droit;
    }

    public function hasDroit(int $id_e, int $id_u, string $droit): bool
    {
        if ($id_u == 0) {
            return true;
        }
        return $this->roleUtilisateur->hasDroit($id_u, $droit, $id_e);
    }

    public function hasDroitConnecteurLecture(int $id_e, int $id_u): bool
    {
        $part = $this->getPartForConnecteurDroit();
        return $this->hasDroit($id_e, $id_u, self::getDroitLecture($part));
    }

    public function hasDroitConnecteurEdition(int $id_e, int $id_u): bool
    {
        $part = $this->getPartForConnecteurDroit();
        return $this->hasDroit($id_e, $id_u, self::getDroitEdition($part));
    }

    public function getPartForConnecteurDroit(): string
    {
        if ($this->connecteur_droit) {
            $part = self::DROIT_CONNECTEUR;
        } else {
            $part = self::DROIT_ENTITE;
        }
        return $part;
    }
}
