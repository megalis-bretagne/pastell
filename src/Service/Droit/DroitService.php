<?php

namespace Pastell\Service\Droit;

use DocumentTypeFactory;
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
    private $documentTypeFactory;
    private $connecteur_droit;

    public function __construct(RoleUtilisateur $roleUtilisateur, DocumentTypeFactory $documentTypeFactory, bool $connecteur_droit = false)
    {
        $this->roleUtilisateur = $roleUtilisateur;
        $this->documentTypeFactory = $documentTypeFactory;
        $this->connecteur_droit = $connecteur_droit;
    }

    public function hasDroit(int $id_u, string $droit, int $id_e): bool
    {
        if ($id_u == 0) {
            return true;
        }
        if (! $this->isEnabledDroitTypeDossier($droit)) {
            return false;
        }
        return $this->roleUtilisateur->hasDroit($id_u, $droit, $id_e);
    }

    public function hasOneDroit(int $id_u, string $droit): bool
    {
        if (! $this->isEnabledDroitTypeDossier($droit)) {
            return false;
        }
        return $this->roleUtilisateur->hasOneDroit($id_u, $droit);
    }

    public function getAllDocumentLecture(int $id_u, int $id_e): array
    {
        $liste_type = $this->roleUtilisateur->getAllDocumentLecture($id_u, $id_e);
        foreach ($liste_type as $type) {
            if (! $this->documentTypeFactory->isEnabledFlux($type)) {
                unset($liste_type[$type]);
            }
        }
        return $liste_type;
    }

    public function getAllDroitEntite(int $id_u, int $id_e): array
    {
        $data = $this->roleUtilisateur->getAllDroitEntite($id_u, $id_e);
        foreach ($data as $key => $droit) {
            if (! $this->isEnabledDroitTypeDossier($droit)) {
                unset($data[$key]);
            }
        }
        return array_values($data);
    }

    public function getAllDroit(int $id_u): array
    {
        $data = $this->roleUtilisateur->getAllDroit($id_u);
        foreach ($data as $key => $droit) {
            if (! $this->isEnabledDroitTypeDossier($droit)) {
                unset($data[$key]);
            }
        }
        return array_values($data);
    }

    public function hasDroitConnecteurLecture(int $id_e, int $id_u): bool
    {
        $part = $this->getPartForConnecteurDroit();
        return $this->hasDroit($id_u, self::getDroitLecture($part), $id_e);
    }

    public function hasDroitConnecteurEdition(int $id_e, int $id_u): bool
    {
        $part = $this->getPartForConnecteurDroit();
        return $this->hasDroit($id_u, self::getDroitEdition($part), $id_e);
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

    /**
     * @param array $all_droit
     * @return array
     */
    public function cleanDisabledDroit(array $all_droit): array
    {
        foreach ($all_droit as $sql_droit => $checked) {
            if (! $this->isEnabledDroitTypeDossier($sql_droit)) {
                unset($all_droit[$sql_droit]);
            }
        }
        return $all_droit;
    }

    /**
     * @param string $droit
     * @return bool
     */
    public function isEnabledDroitTypeDossier(string $droit): bool
    {
        list($part) = explode(":", $droit);
        if (! $this->documentTypeFactory->isEnabledFlux($part)) {
            return false;
        }
        return true;
    }
}
