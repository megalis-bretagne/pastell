<?php

require_once __DIR__."/TdtException.class.php";
require_once __DIR__."/TdT/lib/ActesTypePJ.class.php";

abstract class TdtConnecteur extends Connecteur{

    const FAMILLE_CONNECTEUR = 'TdT';

    const STATUS_ERREUR = -1;
    const STATUS_ANNULE = 0;
    const STATUS_POSTE = 1;
    const STATUS_EN_ATTENTE_DE_TRANSMISSION = 2;
    const STATUS_TRANSMIS = 3;
    const STATUS_ACQUITTEMENT_RECU = 4;
    const STATUS_VALIDE = 5;
    const STATUS_REFUSE = 6;

    const STATUS_HELIOS_TRAITEMENT = 7;
    const STATUS_HELIOS_INFO = 8;
    const STATUS_HELIOS_ATTENTE = 9;

    const STATUS_ACTES_MESSAGE_PREF_RECU = 7;
    const STATUS_ACTES_MESSAGE_PREF_RECU_AR = 8;
    const STATUS_ACTES_MESSAGE_PREF_RECU_PAS_D_AR = 21;

    const STATUS_ACTES_MESSAGE_PREF_ACQUITTEMENT_RECU = 11;

    const STATUS_ACTES_EN_ATTENTE_DE_POSTER = 17;


    const COURRIER_SIMPLE = 2;
    const DEMANDE_PIECE_COMPLEMENTAIRE = 3;
    const LETTRE_OBSERVATION = 4;
    const DEFERE_TRIBUNAL_ADMINISTRATIF = 5;

    protected $arActes;

    public static function getStatusString($status) {
        $statusString = [
            -1 => 'Erreur',
            0 => 'Annulé',
            1 => 'Posté',
            2 => 'En attente de transmission',
            3 => 'Transmis',
            4 => 'Acquittement reçu',
            5 => 'Validé',
            6 => 'Refusé',
            7 => 'AR non disponible pour le moment',
            17 => "En attente d'être postée"
        ];
        if (empty($statusString[$status])) {
            return "Statut inconnu ($status)";
        }
        return $statusString[$status];
    }

    abstract public function getLogicielName();

    abstract public function testConnexion();

    abstract public function getClassification();

    abstract public function demandeClassification();

    abstract public function annulationActes($id_transaction);

    abstract public function verifClassif();

    abstract public function postHelios(DonneesFormulaire $donneesFormulaire);

	abstract public function sendHelios(Fichier $fichierHelios);

	abstract public function postActes(DonneesFormulaire $donneesFormulaire);

	abstract public function sendActes(TdtActes $tdtActes);

	abstract public function getStatusHelios($id_transaction);

    abstract public function getStatus($id_transaction);

    abstract public function getLastReponseFile();

    abstract public function getDateAR($id_transaction);

    abstract public function getBordereau($id_transaction);

    abstract public function getActeTamponne($id_transaction);

    abstract public function getFichierRetour($transaction_id);

    abstract public function getListReponsePrefecture($transaction_id);

    abstract public function getReponsePrefecture($transaction_id);

    abstract public function sendResponse(DonneesFormulaire $donneesFormulaire);

    abstract public function getAnnexesTamponnees($transaction_id);

    /* URL pour rediriger l'utilisateur et ainsi permettre qu'il puisse s'authentifier avec un certificat RGS** */
    public function getRedirectURLForTeletransimission(){}

    /* Idem en version "par lot" */
    public function getRedirectURLForTeletransimissionMulti(){}

    /* Permet de récupérer un nonce de S2low pour l'authentification par nonce */
    public function getNounce(){}

    /**
     * @param string $nature
     * @param string $classification_file_path The path to the classification file
     * @return mixed
     * @throws UnrecoverableException
     * @throws Exception
     */
    public function getDefaultTypology($nature, $classification_file_path)
    {
        $actesTypePJ = new ActesTypePJ();

        $actesTypePJData = new ActesTypePJData();

        $actesTypePJData->acte_nature = $nature;
        $actesTypePJData->classification_file_path = $classification_file_path;

        $piece_list = $actesTypePJ->getTypePJListe($actesTypePJData);

        if (!$piece_list) {
            throw new UnrecoverableException(
                "Impossible de trouver un typage par défaut pour la nature $nature"
            );
        }

        return array_keys($piece_list)[0];
    }

    /**
     * @param string $natureActe
     * @return string
     * @throws Exception
     */
    public function getShortenedNatureActe(string $natureActe) : string
    {
        $shortenedNatureActe = [
            '1' => 'DE',
            '2' => 'AR',
            '3' => 'AI',
            '4' => 'CC',
            '5' => 'BF',
            '6' => 'AU'
        ];
        if (!array_key_exists($natureActe, $shortenedNatureActe)) {
            throw new Exception("La nature $natureActe est inconnue.");
        }
        return $shortenedNatureActe[$natureActe];
    }

    public function getARActes()
    {
        return $this->arActes;
    }

    public function getStatusInfo($status_id) {
        //Note : les status helios et actes sont commun sur le TdT pour la plupart.
        $all_status = [
            -1 => "Erreur",
            0 => "Annulé",
            1 => "Posté",
            2 => "En attente de transmission. Fichier valide.",
            3 => "Transmis",
            4 => "Acquittement reçu",
            5 => "status 5 invalide",
            6 => "Refusé",
            7 => "En traitement",
            8 => "Information disponible"
        ];
        if (empty($all_status[$status_id])) {
            return "Status $status_id inconnu sur Pastell";
        }
        return $all_status[$status_id];
    }

}
