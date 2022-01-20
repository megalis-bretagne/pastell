#! /usr/bin/php
<?php

/**
 * @var ObjectInstancier $objectInstancier
 */


//FIXME : il faut remplacer PASTELL_PATH par le bon chemin.
require_once PASTELL_PATH . '/init.php';

require_once __DIR__ . '/../module/actes-generique/lib/ChoixClassificationControler.class.php';

$sqlQuery = $objectInstancier->getInstance(SQLQuery::class);


$entiteListe = new EntiteListe($sqlQuery);

$liste_collectivite = $entiteListe->getAll('collectivite');

$zenMail = $objectInstancier->getInstance(ZenMail::class);
$notification = new Notification($sqlQuery);
$notificationMail = $objectInstancier->getInstance(NotificationMail::class);

$choixClassificationControler = new ChoixClassificationControler($sqlQuery);

foreach ($liste_collectivite as $col) {
    try {
        /** @var TdtConnecteur $tdT */
        $tdT = $objectInstancier
            ->getInstance(ConnecteurFactory::class)
            ->getConnecteurByType($col['id_e'], 'actes-generique', 'TdT');
        if (!$tdT) {
            echo "{$col['denomination']} : aucun connecteur TdT pour actes\n";
            continue;
        }

        if ($tdT->verifClassif()) {
            echo "{$col['denomination']} : la classification est à jour\n";
            continue;
        }
        $result = $tdT->getClassification();

        /** @var DonneesFormulaire $donneesFormulaire */
        $donneesFormulaire = $objectInstancier
            ->getInstance(ConnecteurFactory::class)
            ->getConnecteurConfigByType($col['id_e'], 'actes-generique', 'TdT');
        $donneesFormulaire->addFileFromData("classification_file", "classification.xml", $result);

        $choixClassificationControler->disabledClassificationCDG($col['id_e']);

        $message = "{$col['denomination']} : classification  mise à jour\n";
        $notificationMail->notify($col['id_e'], $col['id_d'], 'recup-classification', 'collectivite-properties', $message);

        echo $message;
    } catch (Exception $e) {
        echo  "{$col['denomination']} : " . $e->getMessage() . "\n";
    }
}
