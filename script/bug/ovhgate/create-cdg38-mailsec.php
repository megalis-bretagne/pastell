<?php


require_once __DIR__ . "/../../../init.php";
require_once __DIR__."/CDG38EntiteMS.php";

$objectInstancier = ObjectInstancierFactory::getObjetInstancier();

$pastellLogger = $objectInstancier->getInstance(PastellLogger::class);
$pastellLogger->setName("ovhgate");
$pastellLogger->enableStdOut(true);


$csv = __DIR__ . "/../../../temp/2021_03_17_Base_MAIL_SEC__PASTELL.csv";

$fhandle = fopen($csv, "r");

for($i=0;$i<2;$i++) {
    $line = fgetcsv($fhandle, "1000", ";");
}


$entiteCreator = $objectInstancier->getInstance(EntiteCreator::class);
$entiteSQL = $objectInstancier->getInstance(EntiteSQL::class);
$utilisateurSQL = $objectInstancier->getInstance(UtilisateurSQL::class);
$roleUtilisateur = $objectInstancier->getInstance(RoleUtilisateur::class);
$roleSQL = $objectInstancier->getInstance(RoleSQL::class);
$connecteurEntiteSQL = $objectInstancier->getInstance(ConnecteurEntiteSQL::class);
$donneesFormulaireFactory = $objectInstancier->getInstance(DonneesFormulaireFactory::class);
$actionExecutorFactory = $objectInstancier->getInstance(ActionExecutorFactory::class);
$fluxEntiteSQL = $objectInstancier->getInstance(FluxEntiteSQL::class);


$roleSQL->edit('utilisateur',"Utilisateur");
$roleSQL->updateDroit('utilisateur',['journal:lecture']);
$roleSQL->edit('admin_entite',"Admin entité");
$roleSQL->updateDroit('admin_entite',['journal:lecture','entite:lecture','entite:edition','utilisateur:lecture','utilisateur:edition']);
$roleSQL->edit('helios-generique',"Helios générique");
$roleSQL->updateDroit('helios-generique',['helios-generique:lecture','helios-generique:edition']);
$roleSQL->edit('helios-automatique',"Helios automatique");
$roleSQL->updateDroit('helios-automatique',['helios-automatique:lecture','helios-automatique:edition']);
$roleSQL->edit('actes-automatique',"Acte automatique");
$roleSQL->updateDroit('actes-automatique',['actes-automatique:lecture','actes-automatique:edition']);
$roleSQL->edit('pdf-generique',"PDF générique");
$roleSQL->updateDroit('pdf-generique',['pdf-generique:lecture','pdf-generique:edition']);
$roleSQL->edit('facture-cpp',"Facture Chorus");
$roleSQL->updateDroit('facture-cpp',['facture-cpp:lecture','facture-cpp:edition']);
$roleSQL->edit('facture-cpp',"Facture CPP");
$roleSQL->updateDroit('facture-cpp',['facture-cpp:lecture','facture-cpp:edition']);
$roleSQL->edit('doc-a-signer',"Document à signer");
$roleSQL->updateDroit('doc-a-signer',['document-a-signer:lecture','document-a-signer:edition']);
$roleSQL->edit('pack-marche',"Marché");
$roleSQL->updateDroit('pack-marche',['piece-marche:lecture','piece-marche:edition','pes-marche:lecture','pes-marche:edition','dossier-marche:lecture','dossier-marche:edition','piece-marche-par-etape:lecture','piece-marche-par-etape:edition']);
$roleSQL->edit('helios-retour','Helios retour');
$roleSQL->updateDroit('helios-retour',['helios-pes-retour:lecture','helios-pes-retour:edition']);


$id_e_mere = 1;
$siren = "283812014";

$roleSQL->edit('mailsec',"MAILSEC");
$roleSQL->updateDroit('utilisateur',['journal:lecture','mailsec:lecture','mailsec:edition']);


while ($line = fgetcsv($fhandle, "1000", ";")) {
    $CDG38EntiteMS = new CDG38EntiteMS();
    $CDG38EntiteMS->hydrate($line);
    print_r($CDG38EntiteMS);
    //exit;
    if ($CDG38EntiteMS->entite_fille){
        $info = $entiteSQL->getInfoByDenomination($CDG38EntiteMS->entite_fille);
        if (! $info){
            $id_e = $entiteCreator->edit(0, $siren, $CDG38EntiteMS->entite_fille,"collectivite",$id_e_mere);
        } else {
            $id_e = $info['id_e'];
        }
    }
    $id_u = $utilisateurSQL->getIdFromLogin($CDG38EntiteMS->login_user);
    if (! $id_u) {
        $pastellLogger->info("Création de l'utilisateur " . $CDG38EntiteMS->login_user);
        $id_u = $utilisateurSQL->create($CDG38EntiteMS->login_user, $CDG38EntiteMS->password_user?:"fkdjfklqsjdflkq", $CDG38EntiteMS->mail_user, "");
        $utilisateurSQL->validMailAuto($id_u);
        $utilisateurSQL->setColBase($id_u, $id_e);
    }
    $utilisateurSQL->setNomPrenom($id_u, $CDG38EntiteMS->nom_user, $CDG38EntiteMS->prenom_user);
    $roleUtilisateur->removeAllRole($id_u);
    $roleUtilisateur->addRole($id_u, 'mailsec', $id_e);


    $connecteur_info = $connecteurEntiteSQL->getByType($id_e,'mailsec');
    if ($connecteur_info){
        $id_ce = $connecteur_info[0]['id_ce'];
    } else {
        $id_ce = $connecteurEntiteSQL->addConnecteur($id_e, "mailsec", "mailsec", "mailsec");
        $pastellLogger->info("Ajout connecteur mailsec $id_e");
    }
    $donneesFormulaire = $donneesFormulaireFactory->getConnecteurEntiteFormulaire($id_ce);

    $donneesFormulaire->setTabData([
        'mailsec_from' => $CDG38EntiteMS->adresse_expe,
        'mailsec_content'=> utf8_encode($CDG38EntiteMS->reception_mail)
    ]);
    $fluxEntiteSQL->addConnecteur($id_e, 'mailsec', 'mailsec', $id_ce);

}
