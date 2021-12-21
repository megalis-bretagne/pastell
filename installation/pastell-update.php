#! /usr/bin/php
<?php

/**
 * Ce script permet de mettre à jour automatiquement un Pastell en fonction d'une branche du SVN
 * Le script doit être copier en dehors du code Pastell, il est indépendant du reste du code Pastell
 * Le script se base sur le numéro de révision présent sur le SVN et celui présent à la fin du répertoire contenant Pastell
 * Le script vérifie que la base de données est bien ok (avec dbupdate.php), il ne fait rien sinon
 * Le script met à jour le numéro de révision dans le fichier manifest.yml
 *
 */

//Supprimer la ligne suivante après avoir configuré le script
if (true) {
    exit;
}

//Indiquer le lien symbolique du Pastell
$symlink_to_current_pastell = "/var/www/pastell";

//Indiquer ici la branche à partir de laquelle mettre à jour
$svn_pastell_branche_url = "https://scm.adullact.net/anonscm/svn/pastell/trunk";

//Fin de la configuration


echo "Lecture du numéro de révision courant sur $symlink_to_current_pastell\n";
$symlink_content = readlink($symlink_to_current_pastell);

if (! $symlink_content) {
    throw new Exception("Le lien $symlink_content ne pointe sur rien ou n'est pas un lien");
}

if ($symlink_content[0] != '/') {
    throw new Exception("Le lien $symlink_content ne pointe pas sur un chemin absolu");
}

echo "Répertoire réel de l'installation courante : $symlink_content\n";

preg_match("#^(.*)-rev(\d+)$#", $symlink_content, $matches);
if (empty($matches[2])) {
    throw new Exception("Le nom $symlink_content ne contient pas le numéro de révision à la fin XXXX-revYYYY");
}
$local_revision = $matches[2];
$path_start = $matches[1];
echo "Numéro de révision local: $local_revision\n";



echo "Récupération du numéro de révision sur le chemin SVN : $svn_pastell_branche_url\n";
$svnWrapper = new SVNWrapper();
$info = $svnWrapper->getInfo($svn_pastell_branche_url);
preg_match("#Revision: (\d+)#", $info, $matches);
if (empty($matches[1])) {
    throw new Exception("Impossible de trouver le numéro de révision du chemin SVN $svn_pastell_branche_url");
}
$svn_revision = $matches[1];
echo "Numéro de révision SVN : $svn_revision\n";


if ($svn_revision < $local_revision) {
    throw new Exception("Le numéro de révision SVN est plus PETIT que le numéro de révision locale !!!!!????");
}
if ($svn_revision == $local_revision) {
    echo "Le numéro de revision du SVN correspond au numéro local : le logiciel est à jour\n";
    exit(0);
}

$new_path = $path_start . "-rev{$svn_revision}";

if (file_exists($new_path)) {
    echo ("Le répertoire $new_path existe déjà !\n");
} else {
    echo "Récupération SVN de $svn_pastell_branche_url vers $new_path\n";
    $svnWrapper->export($svn_pastell_branche_url, $new_path);
    echo "Récupération terminé\n";
}

echo "Copie du fichier LocalSettings.php\n";
if (! copy($symlink_content . "/LocalSettings.php", $new_path . "/LocalSettings.php")) {
    throw new Exception("Impossible de copier le fichier LocalSettings.php");
}


echo "Vérification de la base de données\n";
$db_update_script = $new_path . "/installation/dbupdate.php";
exec("php $db_update_script", $output, $return_var);

if ($return_var != 0) {
    echo "La base de données installé diffère de la base de donnée attendu ! \n";
    echo "Faire un $db_update_script afin de visualiser les différences\n";
    exit(-1);
}
echo "La base de données est à jour !\n";

echo "Correction du fichier manifest.yml\n";
$manifest_content = file_get_contents($new_path . "/manifest.yml");
$new_manifest_content = preg_replace('#\$Rev: \d+ \$#', "\$Rev: $svn_revision \$", $manifest_content);
file_put_contents($new_path . "/manifest.yml", $new_manifest_content);



echo "Mise à jour du lien symbolique\n";
unlink($symlink_to_current_pastell);
symlink($new_path, $symlink_to_current_pastell);
echo "Déploiement terminé\n";


class SVNWrapper
{
    private function exec($commande)
    {
        $commande .= " 2>&1";
        exec($commande, $out, $ret);

        if ($ret) {
            throw new Exception("La Commande >$commande< a échoué ; Résultat : " . implode("\n", $out));
        };

        return implode("\n", $out);
    }

    public function export($url, $path)
    {
        $result = $this->exec("svn export --non-interactive --no-auth-cache $url $path");
        return $result;
    }

    public function getInfo($url)
    {
        return $this->exec("export LANG=C; svn info --non-interactive --no-auth-cache $url");
    }
}
