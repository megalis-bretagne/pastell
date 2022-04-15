#! /usr/bin/php
<?php
//TODO à supprimer

/**
 * Ce script permet de mettre automatiquement à jour toutes les extensions d'une liste de la forme :
 * nom_du_repertoire:url du svn
 *
 * Le script est indépendant du Pastell et devrait être copié en dehors du code de celui-ci
 *
 */

//Supprimer la ligne suivante après avoir configuré le script
//if (true) exit;

//Indiquer le répertoire avec les extensions de Pastell
$extensions_path = "/data/extensions/";

//Fin de la configuration

$extensions_path = rtrim($extensions_path, "/");

$extensions_svn_list_path  = $extensions_path . "/extensions_svn_list.txt";

if (!file_exists($extensions_svn_list_path)) {
    echo "Le fichier $extensions_svn_list_path n'existe pas : celui-ci doit contenir la liste des URL SVN sous la forme:\n";
    echo "repertoire-extension:url_svn\n";
    echo "exemple: \n";
    echo "ptl-actes:https://scm.adullact.net/anonscm/svn/ptl-actes/trunk\n";
    echo "ptl-helios:https://scm.adullact.net/anonscm/svn/ptl-helios/trunk\n";
    exit;
}

$svnWrapper = new SVNWrapper();

$content = file_get_contents($extensions_svn_list_path);
$extension_list = explode("\n", $content);

foreach ($extension_list as $extension_properties) {
    if (trim($extension_properties) == '') {
        continue;
    }

    list($directory_name,$svn_url) = explode(":", $extension_properties, 2);

    echo "[$directory_name] Récupération des info sur $svn_url\n";
    $info = $svnWrapper->getInfo($svn_url);
    if (!$info) {
        echo "[ERREUR] Aucune info récupéré !\n";
        continue;
    }

    preg_match("#Revision: (\d+)#", $info, $matches);
    if (empty($matches[1])) {
        echo "[ERREUR] impossible de trouver le numéro de révision du chemin SVN !\n";
        continue;
    }
    $svn_revision = $matches[1];

    echo "[$directory_name] Numéro de révision SVN : $svn_revision\n";

    $extension_directory = "{$extensions_path}/{$directory_name}-rev{$svn_revision}";

    if (file_exists($extension_directory)) {
        echo "[$directory_name] le répertoire $extension_directory existe déjà !\n";
        continue;
    }

    echo "[$directory_name] Récupération depuis le SVN\n";
    $svnWrapper->export($svn_url, $extension_directory);

    echo "[$directory_name] Correction du fichier manifest.yml\n";
    $manifest_content = file_get_contents($extension_directory . "/manifest.yml");
    $new_manifest_content = preg_replace('#\$Rev: \d+ \$#', "\$Rev: $svn_revision \$", $manifest_content);
    file_put_contents($extension_directory . "/manifest.yml", $new_manifest_content);




    $symlink = $extensions_path . "/" . $directory_name;
    echo "[$directory_name] Mise à jour du lien symbolique $symlink -> $extension_directory\n";
    if (file_exists($symlink)) {
        unlink($symlink);
    }
    symlink($extension_directory, $symlink);

    echo "[$directory_name] Déploiement OK\n";
    echo "***\n\n";
}




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
