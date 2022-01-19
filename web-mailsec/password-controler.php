<?php

/**
 * @var ObjectInstancier $objectInstancier
 * @var DonneesFormulaireFactory $donneesFormulaireFactory
 */

require_once __DIR__ . '/../init.php';

$recuperateur = new Recuperateur($_POST);
$key = $recuperateur->get('key');
$password = $recuperateur->getNoTrim('password');


$documentEmail = $objectInstancier->getInstance(DocumentEmail::class);
$info = $documentEmail->getInfoFromKey($key);

if (!$info) {
    header("Location: invalid.php");
    exit;
}

$donneesFormulaire = $donneesFormulaireFactory->get($info['id_d'], 'mailsec-destinataire');

if ($donneesFormulaire->get('password') == $password) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $_SESSION["consult_ok_{$key}_{$ip}"] = 1;
    header("Location: index.php?key=$key");
    exit;
} else {
    $objectInstancier->getInstance(LastError::class)->setLastError("Le mot de passe est incorrect");
    header("Location: password.php?key=$key");
    exit;
}
