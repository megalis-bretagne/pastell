<?php
require_once(dirname(__FILE__)."/../init.php");

require_once( PASTELL_PATH . "/lib/base/Recuperateur.class.php");
require_once( PASTELL_PATH . "/lib/utilisateur/Utilisateur.class.php");
require_once( PASTELL_PATH . "/lib/utilisateur/UtilisateurListe.class.php");
require_once( PASTELL_PATH . "/lib/base/PasswordGenerator.class.php");
require_once( PASTELL_PATH . "/lib/base/ZenMail.class.php");

$recuperateur = new Recuperateur($_POST);

$login = $recuperateur->get('login');
$email = $recuperateur->get('email');


$utilisateurListe = new UtilisateurListe($sqlQuery);
$id_u = $utilisateurListe->getByLoginOrEmail($login,$email);

if (!$id_u){
	$lastError->setLastError("Aucun compte n'a �t� trouv� avec ces informations");
	header("Location: oublie-identifiant.php");
	exit;
}
$passwordGenerator = new PasswordGenerator();
$mailVerifPassword = $passwordGenerator->getPassword();

$utilisateur = new Utilisateur($sqlQuery, $id_u);
$info = $utilisateur->getInfo();
$utilisateur->reinitPassword($mailVerifPassword);


$zenMail = new ZenMail($zLog);
$zenMail->setEmmeteur("Pastell","pastell@sigmalis.com");
$zenMail->setDestinataire($info['email']);
$zenMail->setSujet("[Pastell] Proc�dure de modification de mot de passe");
$infoMessage = array('mail_verif_password'=>$mailVerifPassword);
$zenMail->setContenu(PASTELL_PATH . "/mail/changement-mdp.php",$infoMessage);
$zenMail->send();

$journal->addActionAutomatique(Journal::MODIFICATION_UTILISATEUR,$info['id_e'],0,'mot de passe modifi�',"Proc�dure initi�e pour {$info['email']}");


$lastMessage->setLastMessage("Un email vous a �t� envoy� avec la suite de la proc�dure");
header("Location: connexion.php");
