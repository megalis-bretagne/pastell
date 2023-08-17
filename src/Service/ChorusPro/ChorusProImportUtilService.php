<?php

namespace Pastell\Service\ChorusPro;

use Exception;
use SQLQuery;

class ChorusProImportUtilService
{
    public const TYPE_SYNCHRONISATION_CREATION = 'C';
    public const TYPE_SYNCHRONISATION_SYNCHRO = 'S';

    public const TYPE_INTEGRATION_CPP_CLE = "CPP";
    public const TYPE_INTEGRATION_CPP_VALEUR = "Importation Chorus Pro";

    public const TYPE_INTEGRATION_CPP_TRAVAUX_CLE = "CPP_TRAVAUX";
    public const TYPE_INTEGRATION_CPP_TRAVAUX_VALEUR = "Importation Chorus Pro Facture de Travaux (MOE/MOA)";

    public const NOMMAGE_ID_FACTURE_CSV = '-99-csv';
    public const TYPE_INTEGRATION_CSV_CLE = 'CSV';
    public const TYPE_INTEGRATION_CSV_VALEUR = 'Importation Chorus Pro par CSV';

    /**
     * @var SQLQuery
     */
    private $SQLQuery;

    public function __construct(
        SQLQuery $SQLQuery
    ) {
        $this->SQLQuery = $SQLQuery;
    }

    /**
     * @param string $id_e
     * @param string $date_get_depuis_le
     * @param string $type_integration
     * @return bool|mixed
     */
    public function getMinDateStatutCourant(string $id_e, string $date_get_depuis_le, string $type_integration)
    {
        $sql = "SELECT di.field_value FROM document_index di " .
            " JOIN document_entite de ON de.id_d=di.id_d " .
            " JOIN document_index di_integration ON de.id_d=di_integration.id_d AND di_integration.field_name = 'type_integration' AND di_integration.field_value =? " .
            " WHERE de.id_e=? AND di.field_name='date_statut_courant' " .
            " ORDER BY di.field_value DESC LIMIT 1"; // date_statut_courant la plus récente
        $min_date_statut_courant = $this->SQLQuery->queryOne(
            $sql,
            $type_integration,
            $id_e
        );
        if (! $min_date_statut_courant) {
            return $date_get_depuis_le;
        }
        return min($date_get_depuis_le, $min_date_statut_courant); // Date la plus ancienne
        // Exemples, avec 01/01/2021 et 01/01/2019, date_statut_courant la plus récente = 01/01/2021
        // et (depuis le 01/01/2020 => 01/01/2020), (depuis le 01/01/2022 => 01/01/2021)
    }

    /**
     * @param $id_e
     * @param $type_integration
     * @param string $utilisateur_technique
     * @return array
     * @throws Exception
     */
    public function getListeFacturePastell($id_e, $type_integration, $utilisateur_technique = ""): array
    {
        // Chargement des factures cpp présentes sur le Pastell
        $sql = <<<SQL
SELECT de.id_d, de.id_e, di_id_facture_cpp.field_value AS id_facture_cpp, di_statut_cpp.field_value AS statut_cpp
FROM document_entite de
INNER JOIN document_index di_id_facture_cpp
    ON di_id_facture_cpp.id_d = de.id_d AND di_id_facture_cpp.field_name = 'id_facture_cpp'
INNER JOIN document_index di_statut_cpp
    ON de.id_d = di_statut_cpp.id_d AND di_statut_cpp.field_name = 'statut_cpp'
INNER JOIN document_index di_type_integration
    ON de.id_d = di_type_integration.id_d
           AND di_type_integration.field_name = 'type_integration'
           AND di_type_integration.field_value =? 
SQL;
        if ($utilisateur_technique) {
            $sql .= <<<SQL
INNER JOIN document_index di_utilisateur_technique ON de.id_d = di_utilisateur_technique.id_d
      AND di_utilisateur_technique.field_name = 'utilisateur_technique'
      AND di_utilisateur_technique.field_value =?
SQL;
        }
        $sql .= "WHERE de.id_e=?";
        if ($utilisateur_technique) {
            $liste_facture_pastell = $this->SQLQuery->query($sql, $type_integration, $utilisateur_technique, $id_e);
        } else {
            $liste_facture_pastell = $this->SQLQuery->query($sql, $type_integration, $id_e);
        }
        return $liste_facture_pastell;
    }

    /**
     * @param $id_facture_cpp
     * @param $liste_facture_pastell
     * @return mixed
     */
    public function rechercherDocumentPastell($id_facture_cpp, $liste_facture_pastell)
    {
        foreach ($liste_facture_pastell as $facture_pastell) {
            if (strcmp($facture_pastell['id_facture_cpp'], $id_facture_cpp) == 0) {
                return $facture_pastell;
            }
        }
        // Document non trouvé.
        return false;
    }

    /**
     * @param $result
     * @return string
     */
    public function miseEnFormeResult($result): string
    {
        $message = "";
        $retour = [];

        foreach ($result as $line) {
            if (empty($retour[$line['message']])) {
                $retour[$line['message']]['nb'] = 0;
                $retour[$line['message']]['factures'] = '';
            }
            $retour[$line['message']]['nb'] += 1;
            $retour[$line['message']]['message'] = $line['message'];
            $retour[$line['message']]['factures'] .= $line['id_facture_cpp'] . ', ';
        }

        foreach ($retour as $values) {
            $message .= '"' . $values['message'] . '" pour ' . $values['nb'] . ' facture(s): ' . $values['factures'] . '<br/>';
        }
        return $message;
    }
}
