# Guide de migration des extensions


## Passage en UTF-8

1) Les extensions doivent utiliser un encodage UTF-8 au niveau du format de fichier (PHP, YML, txt, ...)
Le script pastell/script/migration/2.0.0/convert-iso-to-utf8.php permet de modifier l'encodage des fichiers d'un répertoire

2) Les fonctions php de chaine de charactère str* ne doivent plus être utilisé. A la place il convient d'utiliser 
les fonction mb_str*
Exemple : strlen doit être remplacé par mb_strlen

3) Les fonctions preg_* doivent savoir qu'elle doivent utiliser des chaînes en UTF-8 avec le modificateur *u*
Exemple : 
    preg_match("#/école/#,$var) devient preg_match("#/école/u#,$var)  

4) Toutes les fonctions de l'API Pastell doivent :
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
    <form action='connecteur/external-data-controler.php' method='post'>

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

