<?php

class FakeTdT extends TdtAdapter
{
    /** @var int */
    private $checkStatus;

    public function setConnecteurConfig(DonneesFormulaire $donneesFormulaire)
    {
        $this->checkStatus = $donneesFormulaire->get('tdt_check_status', TdtConnecteur::STATUS_ACQUITTEMENT_RECU);
    }

    public function getLogicielName()
    {
        return "FakeTdT";
    }

    public function sendActes(TdtActes $tdtActes)
    {
        return mt_rand(1, mt_getrandmax());
    }

    public function getStatus($id_transaction)
    {
        return $this->checkStatus;
    }

    public function getARActes()
    {
        return file_get_contents(__DIR__ . "/fixtures/ar-actes.xml");
    }

    public function getDateAR($id_transaction)
    {
        return date("Y-m-d");
    }

    public function getBordereau($id_transaction)
    {
        return file_get_contents(__DIR__ . "/fixtures/vide.pdf");
    }

    public function getActeTamponne($id_transaction, string $date_affichage = null): ?string
    {
        return file_get_contents(__DIR__ . '/fixtures/vide.pdf');
    }

    public function getListReponsePrefecture($transaction_id)
    {
        return [];
    }

    public function sendHelios(Fichier $fichierHelios)
    {
        return  mt_rand(1, mt_getrandmax());
    }

    public function getStatusHelios($id_transaction)
    {
        return TdtConnecteur::STATUS_HELIOS_INFO;
    }

    public function getStatusInfo($status)
    {
        return $status;
    }

    public function getFichierRetour($tedetis_transaction_id)
    {
        return file_get_contents(__DIR__ . "/fixtures/pes_acquit.xml");
    }

    public function getAnnexesTamponnees(string $transaction_id, ?string $date_publication = null): array
    {
        return [];
    }

    public function getPESRetourListe()
    {
        return [];
    }
}
