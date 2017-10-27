# [2.0.1]

## Ajout

- Connecteur GED local (Expérimental)
- Connecteur type GED "Nouvelle génération" (Expérimental)
- Connecteur d'entité permettant les purges (Expérimental) 

## Corrections

- En mode console, on affiche les erreurs sorties de ChoiceActionExecutor
- Ajout d'une fonction du model DocumentActionEntite::getDocumentOdlerThanDay

## Evolutions

- La taille du libellé des connecteurs est porté de 32 caractères à 128 caractères
- Ajout de la clé de premier niveau "heritage" dans le fichier YAML des connecteurs d'entité. 
    Cette clé permet de merge le fichier avec un autre fichier défini dans le repertoire common-yaml (Expérimental)  
- Les exceptions RecoverableException et UnrecoverableException ont leur propre fichier pour une utilisation plus simple
- Les actions de connecteurs peuvent être partagé entre connecteurs

## Elements déprécié

- La majorité des fonctions de GEDConnecteur sont dépréciées et seront retiré dans la prochaine version mineur