<?php

namespace Pastell\Connector\Ensap\builders;

use Pastell\Connector\Ensap\parts\Assure;
use Pastell\Connector\Ensap\parts\Gestionnaire;

class AssureBuilder
{
    private Assure $assure;

    public function __construct()
    {
        $this->assure = new Assure();
    }

    public function setNumeroDossier(string $numeroDossier): self
    {
        $this->assure->numeroDossier = $numeroDossier;
        return $this;
    }

    public function setNumeroOrdre(string $numeroOrdre): self
    {
        $this->assure->numeroOrdre = $numeroOrdre;
        return $this;
    }

    public function setNir(string $nir): self
    {
        $this->assure->nir = $nir;
        return $this;
    }

    public function setNirModifie(?string $nirModifie): self
    {
        $this->assure->nirModifie = $nirModifie;
        return $this;
    }

    public function setNomNaissance(string $nomNaissance): self
    {
        $this->assure->nomNaissance = $nomNaissance;
        return $this;
    }

    public function setSexe(?string $sexe): self
    {
        $this->assure->sexe = $sexe;
        return $this;
    }

    public function setDateNaissance(string $dateNaissance): self
    {
        $this->assure->dateNaissance = $dateNaissance;
        return $this;
    }

    public function setIban(string $iban): self
    {
        $this->assure->iban = $iban;
        return $this;
    }

    public function setStatut(string $statut): self
    {
        $this->assure->statut = $statut;
        return $this;
    }

    public function setReferenceEmetteur(?string $referenceEmetteur): self
    {
        $this->assure->referenceEmetteur = $referenceEmetteur;
        return $this;
    }

    public function setGestionnaires(array $gestionnaires): self
    {
        $this->assure->gestionnaires = $gestionnaires;
        return $this;
    }

    public function addGestionnaire(Gestionnaire $gestionnaire): self
    {
        $this->assure->gestionnaires[] = $gestionnaire;
        return $this;
    }

    public function build(): Assure
    {
        return $this->assure;
    }

    public function getAssure(array $data): Assure
    {
        return $this->setNumeroDossier($data['numeroDossier'])
            ->setNumeroOrdre($data['numeroOrdre'])
            ->setNir($data['nir'])
            ->setNirModifie($data['nirModifie'])
            ->setNomNaissance($data['nomNaissance'])
            ->setSexe($data['sexe'])
            ->setDateNaissance($data['dateNaissance'])
            ->setIban($data['iban'])
            ->setStatut($data['statut'])
            ->setReferenceEmetteur($data['referenceEmetteur'])
            ->setGestionnaires($data['gestionnaires'])
            ->build();
    }
}
