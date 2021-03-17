<?php


require_once __DIR__ . "/../../../init.php";
require_once __DIR__."/AFIEntite.php";

$objectInstancier = ObjectInstancierFactory::getObjetInstancier();

$pastellLogger = $objectInstancier->getInstance(PastellLogger::class);
$pastellLogger->setName("ovhgate");
$pastellLogger->enableStdOut(true);


$csv = __DIR__ . "/../../../temp/afi_v3.csv";

$fhandle = fopen($csv, "r");

for($i=0;$i<4;$i++) {
    $line = fgetcsv($fhandle, "1000", ",");
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



while ($line = fgetcsv($fhandle, "1000", ",")) {
    $AFIEntite = new AFIEntite();
    $AFIEntite->hydrate($line);
    //print_r($line);
    //print_r($AFIEntite); exit;
    $pastellLogger->info(sprintf("Analyse entite %s (id_e=%s)", $AFIEntite->denomination, $AFIEntite->id_e));
    if (! $AFIEntite->id_e) {
        $pastellLogger->alert("id_e vide !!!");
        exit;
    }
    while (! $entiteSQL->getInfo($AFIEntite->id_e)) {

        $id_e = $entiteCreator->edit(0, "000000000", "Entite provisoire");
        if ($id_e>$AFIEntite->id_e){
            $pastellLogger->emergency("oops id_e $id_e > {$AFIEntite->id_e}");
            exit;
        }
    }
    $info = $entiteSQL->getInfo($AFIEntite->id_e);
    if ($info['siren'] != $AFIEntite->siren && $info['denomination'] != $AFIEntite->denomination) {
        $entiteCreator->edit($AFIEntite->id_e, $AFIEntite->siren, $AFIEntite->denomination);
    }
    $id_u = $utilisateurSQL->getIdFromLogin($AFIEntite->login_user);
    if (! $id_u) {
        $pastellLogger->info("Création de l'utilisateur " . $AFIEntite->login_user);
        $id_u = $utilisateurSQL->create($AFIEntite->login_user, $AFIEntite->password_user, $AFIEntite->email_user, "");
        $utilisateurSQL->validMailAuto($id_u);
        $utilisateurSQL->setColBase($id_u, $AFIEntite->id_e);

    }
    $utilisateurSQL->setNomPrenom($id_u, $AFIEntite->login_user, $AFIEntite->login_user);
    $roleUtilisateur->removeAllRole($id_u);
    $roleUtilisateur->addRole($id_u, 'utilisateur', $AFIEntite->id_e);

    $role_assoc = [
        'helios_generique' => 'helios-generique',
        'helios_automatique' => 'helios-automatique',
        'actes_automatique' => 'actes-automatique',
        'pdf_generique' => 'pdf-generique',
        'cpp' => 'facture-cpp',
        'doc_a_signer' => 'doc-a-signer',
        'pack_marche' => 'pack-marche',
        'helios_retour' => 'helios-retour',
    ];

    foreach ($role_assoc as $data_from_csv => $role){
        if ($AFIEntite->$data_from_csv) {
            $roleUtilisateur->addRole($id_u, $role, $AFIEntite->id_e);
        }
    }
    //Role Admin

    $id_u = $utilisateurSQL->getIdFromLogin($AFIEntite->login_admin);
    if (! $id_u) {
        $pastellLogger->info("Création de l'utilisateur " . $AFIEntite->login_admin);
        $id_u = $utilisateurSQL->create($AFIEntite->login_admin, $AFIEntite->password_admin, $AFIEntite->email_admin, "");
        $utilisateurSQL->validMailAuto($id_u);
        $utilisateurSQL->setColBase($id_u, $AFIEntite->id_e);

    }
    $utilisateurSQL->setNomPrenom($id_u, $AFIEntite->login_admin, $AFIEntite->login_admin);
    $roleUtilisateur->removeAllRole($id_u);
    $roleUtilisateur->addRole($id_u, 'admin_entite', $AFIEntite->id_e);

    // TDT
    $connecteur_info = $connecteurEntiteSQL->getByType($AFIEntite->id_e,'TdT');
    if ($connecteur_info){
        $id_ce = $connecteur_info[0]['id_ce'];
    } else {
        $id_ce = $connecteurEntiteSQL->addConnecteur($AFIEntite->id_e, "s2low", "TdT", "S2LOW ADULLACT");
        $pastellLogger->info("Ajout connecteur S2LOW pour " . $AFIEntite->denomination . " id_e=" . $AFIEntite->id_e);
    }
    if ($AFIEntite->s2low_other) {
        $connecteurEntiteSQL->edit($id_ce, "S2LOW - " . $AFIEntite->s2low_other);
    } else {
        $connecteurEntiteSQL->edit($id_ce, "S2LOW - AFI");
    }
    $donneesFormulaire = $donneesFormulaireFactory->getConnecteurEntiteFormulaire($id_ce);

    $donneesFormulaire->setTabData([
        'url' => 'https://s2low.org/',
        'user_certificat_password' => 'afi_2018',
        'user_login' => $AFIEntite->user_s2low,
        'user_password' => $AFIEntite->password_s2low
    ]);

    $donneesFormulaire->addFileFromCopy('user_certificat',"pastell.afi-sa.net.p12",__DIR__ . "/../../../temp/pastell.afi-sa.net.p12");
    $actionExecutorFactory->executeOnConnecteur($id_ce, 0, 'update-certificate', 1);
    foreach (['helios_generique' => 'helios-generique', 'helios_automatique' => 'helios-automatique', 'actes_automatique'=>'actes-automatique', 'helios_retour'=>'helios-pes-retour'] as $type => $flux) {
        if ($AFIEntite->$type) {
            $fluxEntiteSQL->addConnecteur($AFIEntite->id_e, $flux, 'TdT', $id_ce);
        }
    }

    //Chorus
    if ($AFIEntite->cpp) {
        $connecteur_info = $connecteurEntiteSQL->getByType($AFIEntite->id_e, 'PortailFacture');
        if ($connecteur_info) {
            $id_ce =  $connecteur_info[0]['id_ce'];
        } else {
            $id_ce = $connecteurEntiteSQL->addConnecteur($AFIEntite->id_e, "cpp", "PortailFacture", "Chorus Pro");
            $pastellLogger->info("Ajout connecteur Chorus pour " . $AFIEntite->denomination . " id_e=" . $AFIEntite->id_e);
        }
        $donneesFormulaire = $donneesFormulaireFactory->getConnecteurEntiteFormulaire($id_ce);
        $donneesFormulaire->setTabData([
            'user_login' => $AFIEntite->cpp_user,
            'user_password' => $AFIEntite->cpp_pass
        ]);
        $fluxEntiteSQL->addConnecteur($AFIEntite->id_e, 'facture-cpp', 'PortailFacture', $id_ce);

        $connecteur_info = $connecteurEntiteSQL->getByType($AFIEntite->id_e, 'ParametrageFlux');
        if ($connecteur_info) {
            $id_ce = $connecteur_info[0]['id_ce'];
        } else {
            $id_ce = $connecteurEntiteSQL->addConnecteur($AFIEntite->id_e, "parametrage-flux-facture-cpp", "ParametrageFlux", "Circuit Chorus");
            $pastellLogger->info("Ajout connecteur param circuit Chorus pour " . $AFIEntite->denomination . " id_e=" . $AFIEntite->id_e);
        }
        $donneesFormulaire = $donneesFormulaireFactory->getConnecteurEntiteFormulaire($id_ce);
        $donneesFormulaire->setTabData([
            'check_mise_a_dispo_gf' => "on",
            'envoi_auto' => "on"
        ]);
        $fluxEntiteSQL->addConnecteur($AFIEntite->id_e, 'facture-cpp', 'ParametrageFlux', $id_ce);
    }

    //Parapheur PES
    $id_ce = false;
    $connecteur_info = $connecteurEntiteSQL->getByType($AFIEntite->id_e, 'signature');

    foreach($connecteur_info as $i=>$info){
        if ($info['libelle'] == 'i-Parapheur PES') {
            $id_ce = $info['id_ce'];
            $connecteurEntiteSQL->edit($id_ce, 'IParapheur - AFI');
        }
        if ($info['libelle'] == 'IParapheur - AFI') {
            $id_ce = $info['id_ce'];
        }
    }
    if (!$id_ce){
        $id_ce = $connecteurEntiteSQL->addConnecteur($AFIEntite->id_e, "iParapheur", "signature", "i-Parapheur PES");
        $pastellLogger->info("Ajout connecteur IP (PES) pour " . $AFIEntite->denomination . " id_e=" . $AFIEntite->id_e);
    }

    $donneesFormulaire = $donneesFormulaireFactory->getConnecteurEntiteFormulaire($id_ce);
    $donneesFormulaire->setTabData([
        'iparapheur_wsdl' => 'https://secure-i-parapheur.afi-sa.net/ws-iparapheur?wsdl',
        'iparapheur_user_certificat_password' => 'pdanne@afi-sa.fr',
        'iparapheur_login' => $AFIEntite->login_parapheur_tech,
        'iparapheur_password' => $AFIEntite->password_parapheur_tech,
        'iparapheur_type' => $AFIEntite->type_parapheur
    ]);
    $donneesFormulaire->addFileFromCopy('iparapheur_user_certificat', "pdanne@afi-sa.fr.p12", __DIR__ . "/../../../temp/pdanne@afi-sa.fr.p12");
    $actionExecutorFactory->executeOnConnecteur($id_ce, 0, 'update-certificate', 1);
    foreach (['helios_generique' => 'helios-generique', 'helios_automatique' => 'helios-automatique'] as $type => $flux) {
        if ($AFIEntite->$type) {
            $fluxEntiteSQL->addConnecteur($AFIEntite->id_e, $flux, 'signature', $id_ce);
        }
    }

    if ($AFIEntite->pdf_generique) {
        //Parapheur PDF generique
        $id_ce = false;
        $connecteur_info = $connecteurEntiteSQL->getByType($AFIEntite->id_e, 'signature');

        foreach ($connecteur_info as $i => $info) {
            if ($info['libelle'] == 'i-Parapheur PDF générique') {
                $id_ce = $info['id_ce'];
                $connecteurEntiteSQL->edit($id_ce, 'IParapheur - AFI - BDC');
            }
            if ($info['libelle'] == 'IParapheur - AFI - BDC') {
                $id_ce = $info['id_ce'];
            }
        }
        if (!$id_ce) {
            $id_ce = $connecteurEntiteSQL->addConnecteur($AFIEntite->id_e, "iParapheur", "signature", "i-Parapheur PDF générique");
            $pastellLogger->info("Ajout connecteur IP (PDF Générique) pour " . $AFIEntite->denomination . " id_e=" . $AFIEntite->id_e);
        }

        $donneesFormulaire = $donneesFormulaireFactory->getConnecteurEntiteFormulaire($id_ce);
        $donneesFormulaire->setTabData([
            'iparapheur_wsdl' => 'https://secure-i-parapheur.afi-sa.net/ws-iparapheur?wsdl',
            'iparapheur_user_certificat_password' => 'pdanne@afi-sa.fr',
            'iparapheur_login' => $AFIEntite->login_parapheur_tech,
            'iparapheur_password' => $AFIEntite->password_parapheur_tech,
            'iparapheur_type' => $AFIEntite->type_ip_pdf_generique
        ]);
        $donneesFormulaire->addFileFromCopy('iparapheur_user_certificat', "pdanne@afi-sa.fr.p12", __DIR__ . "/../../../temp/pdanne@afi-sa.fr.p12");
        $actionExecutorFactory->executeOnConnecteur($id_ce, 0, 'update-certificate', 1);

        $fluxEntiteSQL->addConnecteur($AFIEntite->id_e, 'pdf-generique', 'signature', $id_ce);
    }



}

$sql = "DELETE FROM entite where denomination='Entite provisoire'";
$sqlQuery->query($sql);
