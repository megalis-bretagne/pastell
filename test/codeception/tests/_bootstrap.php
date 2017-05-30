<?php


require_once __DIR__."/../../../ci-resources/define-from-environnement.php";

/** TODO réinitialiser la base de données... */

require_once __DIR__."/../../../init.php";


$sqlQuery = new SQLQuery(BD_DSN_TEST,BD_USER_TEST,BD_PASS_TEST);

$sqlQuery->query(file_get_contents(__DIR__."/truncate_all.sql"));

require_once __DIR__."/../../../ci-resources/init-docker.php";


/** @var InternalAPI $internalAPI */
$internalAPI = $objectInstancier->getInstance('InternalAPI');
$internalAPI->setCallerType(InternalAPI::CALLER_TYPE_SCRIPT);

/* Création de l'entité Bourg-en-Bresse*/
$info = $internalAPI->post(
    "/Entite",
    array(
        "type"=>Entite::TYPE_COLLECTIVITE,
        "denomination"=>"Bourg-en-Bresse",
        "siren"=>'000000000')
);

$id_e = $info['id_e'];

/* Création d'un connecteur bouchon de signature */
$info = $internalAPI->post(
    "/Entite/$id_e/Connecteur",
    array (
        'libelle'=>'Bouchon de signature',
        'id_connecteur'=>'fakeIparapheur'
    )
);
$id_ce = $info['id_ce'];

$internalAPI->patch("/Entite/$id_e/Connecteur/$id_ce/content",
    array(
        'iparapheur_type'=>'Actes',
        'iparapheur_envoi_status' => 'ok',
        'iparapheur_retour' => 'Archive'
    )
);

$info = $internalAPI->post(
    "/Entite/$id_e/Flux/actes-generique/connecteur/$id_ce",
    array("type"=>"signature")
);

/* Création d'un connecteur bouchon Tdt */
$info = $internalAPI->post(
    "/Entite/$id_e/Connecteur",
    array (
        'libelle'=>'Bouchon Tdt',
        'id_connecteur'=>'fakeTdt'
    )
);
$id_ce = $info['id_ce'];

$internalAPI->post(
    "/Entite/$id_e/Connecteur/$id_ce/file/classification_file",
    array(
        'file_name'=>'classification.xml',
        'file_content'=>file_get_contents(__DIR__."/_data/classification.xml")
    )
);

$info = $internalAPI->post(
    "/Entite/$id_e/Flux/actes-generique/connecteur/$id_ce",
    array("type"=>"TdT")
);

/* Création d'un connecteur bouchon GED */
$info = $internalAPI->post(
    "/Entite/$id_e/Connecteur",
    array (
        'libelle'=>'Bouchon GED',
        'id_connecteur'=>'FakeGED'
    )
);
$id_ce = $info['id_ce'];
$info = $internalAPI->post(
    "/Entite/$id_e/Flux/actes-generique/connecteur/$id_ce",
    array("type"=>"GED")
);

/* Création du connecteur bouchon SAE */
$info = $internalAPI->post(
    "/Entite/$id_e/Connecteur",
    array (
        'libelle'=>'Bouchon SAE',
        'id_connecteur'=>'fakeSAE'
    )
);
$id_ce = $info['id_ce'];
$info = $internalAPI->post(
    "/Entite/$id_e/Flux/actes-generique/connecteur/$id_ce",
    array("type"=>"SAE")
);

/* Créationd d'un connecteur SEDA */
/*$info = $internalAPI->post(
    "/Entite/$id_e/Connecteur",
    array (
        'libelle'=>'Actes SEDA standard',
        'id_connecteur'=>'actes-seda-standard'
    )
);
$id_ce = $info['id_ce'];
$info = $internalAPI->post(
    "/Entite/$id_e/Flux/actes-generique/connecteur/$id_ce",
    array("type"=>"Bordereau SEDA")
);*/
