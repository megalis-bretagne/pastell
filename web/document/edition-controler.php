<?php
require_once(dirname(__FILE__)."/../init-authenticated.php");

require_once( PASTELL_PATH . "/lib/base/Recuperateur.class.php");

require_once( PASTELL_PATH . "/lib/FileUploader.class.php");

require_once (PASTELL_PATH . "/lib/formulaire/Formulaire.class.php");
require_once( PASTELL_PATH . "/lib/formulaire/DonneesFormulaire.class.php");
require_once (PASTELL_PATH . "/lib/document/DocumentEntite.class.php");
require_once (PASTELL_PATH . "/lib/document/Document.class.php");
require_once (PASTELL_PATH . "/lib/action/DocumentAction.class.php");
require_once (PASTELL_PATH . "/lib/base/ZenMail.class.php");
require_once (PASTELL_PATH . "/lib/notification/Notification.class.php");
require_once (PASTELL_PATH . "/lib/notification/NotificationMail.class.php");


//R�cup�ration des donn�es
$recuperateur = new Recuperateur($_POST);
$id_d = $recuperateur->get('id_d');
$form_type = $recuperateur->get('form_type');
$id_e = $recuperateur->get('id_e');
$objet = $recuperateur->get('objet');


$formulaire_file = PASTELL_PATH . "/form/$form_type.yml" ;
if ( ! file_exists($formulaire_file)){
	header("Location: index.php");
	exit;
}
$formulaire = new Formulaire( $formulaire_file );
$formulaire->setTabNumber(0);


$documentAction = new DocumentAction($sqlQuery,$journal,$id_d,$id_e,$authentification->getId());


$zenMail = new ZenMail($zLog);
$notification = new Notification($sqlQuery);
$notificationMail = new NotificationMail($notification,$zenMail,$journal);

$document = new Document($sqlQuery);
$info = $document->getInfo($id_d);
if (! $info){
	$document->save($id_d,$form_type);
	$documentAction->addAction('Cr�er',$notificationMail);
} else {
	$documentAction->addAction('Modifier',$notificationMail);
}

$fileUploader = new FileUploader($_FILES);


$donneesFormulaire = new DonneesFormulaire( WORKSPACE_PATH  . "/$id_d.yml");
$donneesFormulaire->setFormulaire($formulaire);
	

$donneesFormulaire->save($recuperateur,$fileUploader);

foreach($fileUploader->getAll() as $filename => $orig_filename){
	$url = WORKSPACE_PATH . "/$id_d" . "_" . $filename;
	$fileUploader->save($filename,$url);
}

$titre_field = $formulaire->getTitreField();

$titre = $donneesFormulaire->get($titre_field);

$document->setTitre($id_d,$titre);

/* ??? */
$documentEntite = new DocumentEntite($sqlQuery);
$documentEntite->addRole($id_d,$id_e,"editeur");





header("Location: " . SITE_BASE . "document/detail.php?id_d=$id_d&id_e=$id_e");
