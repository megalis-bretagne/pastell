# Guide de migration des extensions

## Suppression de dépendance

Certaine extension ont été réintroduite dans le coeur de Pastell, les dépendances ne sont donc plus nécessaire :

* ptl-actes
* ptl-helios
* ptl-commande (flux commande de ptl-demat)
* ptlc-signature
* ptlc-tdt
* ptlc-sae
* ptlc-ged
* ptlc-glaneur
* ptlc-office
* ptlc-opensign
* ptlc-signature
* pastell-seda-ng

Attention, ptlc-seda n'existe plus et doit être remplacé par le connecteur SEDA NG



## Passage en UTF-8

1) Les extensions doivent utiliser un encodage UTF-8 au niveau du format de fichier (PHP, YML, txt, ...)
Le script pastell/script/plateform-update/2.0.0/convert-iso-to-utf8.php permet de modifier l'encodage des fichiers d'un répertoire

2) Rechercher et supprimer les utf8_encode, utf8_decode

3) Les fonctions php de chaine de charactère str* ne doivent plus être utilisé. A la place il convient d'utiliser 
les fonction mb_str*
Exemple : strlen doit être remplacé par mb_strlen

4) Les fonctions preg_* doivent savoir qu'elle doivent utiliser des chaînes en UTF-8 avec le modificateur *u*
Exemple : 
    preg_match("#/école/#,$var) devient preg_match("#/école/u#,$var)  

5) Toutes les fonctions de l'API Pastell doivent :
- accepter les chaines au format UTF-8
- renvoyer les données au format UTF-8
- travailler avec le format UTF-8


## Nom des clés

Les clés des élements et des actions du formulaire DOIVENT être renommé avec uniquement des minusucles, des chiffres et 
le caractère souligne \_. Afin d'assurer la compatibilité, il est nécessaire de se rendre sur la définition 
du flux(environnement système) et de copier le bon identifiant.


## Nom des connecteurs
La clé **name** est remplacé par la clé **nom** afin d'avoir des noms de clés identiques sur les flux et sur les connecteurs


## Protection contre le CSRF

Pour les champs de type external data sur les connecteurs, il est nécessaire de procéder aux modifications suivantes :

Les élements **form** de ce type :
    <form action='Connecteur/external-data-controler.php' method='post'>

doivent être remplacés par ce qui suit :

	<form action='<?php $this->url("Connecteur/doExternalData") ?>' method='post'>
		<?php $this->displayCSRFInput();?>
	
Il est toujours possible de mettre au début des script : 
    <?php
 		/** @var Gabarit $this */
 	?>
 
afin d'éviter des erreurs lors du parsing pas les EDI (notamment par PhpStorm). 	

Il est également nécessaire de faire la même chose en ce qui concerne les documents : 

Les élements **form** de ce type : 

    <form action='document/external-data-controler.php' method='post'>

sont à remplacées par : 

    <form action='Document/doExternalData' method='post'>
		<?php $this->displayCSRFInput() ?>


## Modification des chemins 
Les URL de type document/list.php ne fonctionnent plus et son remplacé par Document/list : le premier terme étant le
nom du Controler dans [pastell]/controler/DocumentControler.class.php et le second terme le nom de la fonction Action (ici : listAction())

Cela peut avoir des effets de bords dans les connecteurs ci ceux-ci utilisent des redirection.

###Oasis
Les chemins ayant changé, les connexion depuis Oasis doivent être modifié.

## Modification des propriétés accessibles sur la clé "rule" (fichiers de configuration YML):

- **SUPPRESSION** : properties
- **SUPPRESSION** : herited-properties


## Possibilité d'exposer des fichiers via HTTP sur les extensions

Il est possible d'exposer des fichiers qui pourront être transmis via HTTP (image, applet java, script php particulier) 
directement dans les extensions. Pour cela il suffit d'ajouter un répertoire /web/ dans l'extension.

Le fichier est alors accessible via URL_Pastell/Extension/web/identifiant_extension/

## i-Parapheur:

- **Ajout des métadonnées sur le connecteur i-Parapheur** :
Ajouter $signature->setSendingMetadata($donneesFormulaire); aux Classes IparapheurEnvoie.class.php

- **Problème archivage i-Parapheur en cas de full disk** pour les classes IparapheurRecup.class.php:
Remplacer getSignature($dossierID) par getSignature($dossierID,false)
en ajoutant en fin de méthode:

        if (! $signature->archiver($dossierID)){
            throw new RecoverableException(
                "Impossible d'archiver la transaction sur le parapheur : " . $signature->getLastError()
            );
        }

- **Uniformiser la récupération iparapheur** (ex: bordereau en cas de rejet)

# Annexe : portage d'un projet subversion vers git


Les commandes se basent sur ce billet de blog : http://www.yterium.net/Migrer-un-projet-SVN-vers-GIT

## Récupérer la liste des auteurs

```bash
authors=$(svn log -q https://scm.adullact.net/anonscm/svn/ptl-cpp/ | grep -e '^r' | awk 'BEGIN { FS = "|" } ; { print $2 }' | sort | uniq)
for author in ${authors}; do
  echo "${author} = ${author%@*} <${author}>";
done
```

## Créer le git à partir du SVN
```bash
git svn --authors-file=authors clone https://scm.adullact.net/anonscm/svn/ptl-cpp/ --trunk=trunk --branches=branches --tags=tags
```

## Création des branches et des tags
```bash
git branch -a
* master
  remotes/origin/BL
  remotes/origin/tags/BL
  remotes/origin/tags/V1.1
  remotes/origin/tags/V1.2
  remotes/origin/trunk

git tag -l
git branch BL remotes/origin/BL
git tag BL remotes/origin/tags/BL
git tag V1.1 remotes/origin/tags/V1.1
git tag V1.2 remotes/origin/tags/V1.2
```

## Pousser la branche
```bash
git remote add origin https://gitlab.libriciel.fr/pastell/pastell-cpp.git

# Attention, la commande suivante détruit la branche master sur origin si elle existe ! 
git push --force --set-upstream  origin master
git push --all
git push --tags
```








