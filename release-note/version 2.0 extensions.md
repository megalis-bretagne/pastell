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










