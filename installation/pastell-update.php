#! /usr/bin/php
<?php

/**
 * Ce script permet de mettre � jour automatiquement un Pastell en fonction d'une branche du SVN
 * Le script doit �tre copier en dehors du code Pastell, il est ind�pendant du reste du code Pastell
 * Le script se base sur le num�ro de r�vision pr�sent sur le SVN et celui pr�sent � la fin du r�pertoire contenant Pastell
 * Le script v�rifie que la base de donn�es est bien ok (avec dbupdate.php), il ne fait rien sinon
 * Le script met � jour le num�ro de r�vision dans le fichier manifest.yml
 *
 */

//Supprimer la ligne suivante apr�s avoir configur� le script
if (true) exit;

//Indiquer le lien symbolique du Pastell
$symlink_to_current_pastell = "/var/www/pastell";

//Indiquer ici la branche � partir de laquelle mettre � jour
$svn_pastell_branche_url = "https://scm.adullact.net/anonscm/svn/pastell/trunk";

//Fin de la configuration


echo "Lecture du num�ro de r�vision courant sur $symlink_to_current_pastell\n";
$symlink_content = readlink($symlink_to_current_pastell);

if(! $symlink_content){
	throw new Exception("Le lien $symlink_content ne pointe sur rien ou n'est pas un lien");
}

if ($symlink_content[0] != '/'){
	throw new Exception("Le lien $symlink_content ne pointe pas sur un chemin absolu");
}

echo "R�pertoire r�el de l'installation courante : $symlink_content\n";

preg_match("#^(.*)-rev(\d+)$#",$symlink_content,$matches);
if (empty($matches[2])){
	throw new Exception("Le nom $symlink_content ne contient pas le num�ro de r�vision � la fin XXXX-revYYYY");
}
$local_revision = $matches[2];
$path_start = $matches[1];
echo "Num�ro de r�vision local: $local_revision\n";



echo "R�cup�ration du num�ro de r�vision sur le chemin SVN : $svn_pastell_branche_url\n";
$svnWrapper = new SVNWrapper();
$info = $svnWrapper->getInfo($svn_pastell_branche_url);
preg_match("#Revision: (\d+)#",$info,$matches);
if (empty($matches[1])){
	throw new Exception("Impossible de trouver le num�ro de r�vision du chemin SVN $svn_pastell_branche_url");
}
$svn_revision = $matches[1];
echo "Num�ro de r�vision SVN : $svn_revision\n";


if ($svn_revision < $local_revision){
	throw new Exception("Le num�ro de r�vision SVN est plus PETIT que le num�ro de r�vision locale !!!!!????");
}
if ($svn_revision == $local_revision){
	echo "Le num�ro de revision du SVN correspond au num�ro local : le logiciel est � jour\n";
	exit(0);
}

$new_path = $path_start."-rev{$svn_revision}";

if (file_exists($new_path)){
	echo ("Le r�pertoire $new_path existe d�j� !\n");
} else {
	echo "R�cuperation SVN de $svn_pastell_branche_url vers $new_path\n";
	$svnWrapper->export($svn_pastell_branche_url, $new_path);
	echo "R�cuperation termin�\n";
}

echo "Copie du fichier LocalSettings.php\n";
if (! copy($symlink_content."/LocalSettings.php",$new_path."/LocalSettings.php")){
	throw new Exception("Impossible de copier le fichier LocalSettings.php");
}


echo "V�rification de la base de donn�es\n";
$db_update_script = $new_path."/installation/dbupdate.php";
exec("php $db_update_script",$output,$return_var);

if ($return_var != 0){
	echo "La base de donn�es install� diff�re de la base de donn�e attendu ! \n";
	echo "Faire un $db_update_script afin de visualiser les diff�rences\n";
	exit(-1);
}
echo "La base de donn�es est � jour !\n";

echo "Correction du fichier manifest.yml\n";
$manifest_content = file_get_contents($new_path."/manifest.yml");
$new_manifest_content = preg_replace('#\$Rev: \d+ \$#',"\$Rev: $svn_revision \$",$manifest_content);
file_put_contents($new_path."/manifest.yml",$new_manifest_content);



echo "Mise � jour du lien symbolique\n";
unlink($symlink_to_current_pastell);
symlink($new_path,$symlink_to_current_pastell);
echo "D�ploiement termin�\n";


class SVNWrapper {

	private function exec($commande){
		$commande .= " 2>&1";
		exec($commande,$out,$ret);

		if ($ret){
			throw new Exception("La Commande >$commande< a �chou� ; R�sultat : ".implode("\n",$out));
		};

		return implode("\n",$out);
	}

	public function export($url,$path){
		$result = $this->exec("svn export --non-interactive --no-auth-cache $url $path");
		return $result;
	}

	public function getInfo($url){
		return $this->exec("export LANG=C; svn info --non-interactive --no-auth-cache $url");
	}

}