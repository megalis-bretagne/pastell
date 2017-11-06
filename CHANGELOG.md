# [2.0.1]

## Ajout

- Connecteur d'entité permettant les purges (Expérimental) 
- Connecteur de dépot "Nouvelle génération" (remplace les connecteurs GED) (Expérimental)
- Connecteur de dépôt local (Expérimental)
- Connecteur de dépôt WebDAV (Expérimental)
- Connecteur de dépôt CMIS (Expérimental)
- Connecteur de dépôt FTP (Expérimental)
- Connecteur de dépôt SFTP (Expérimental)
- Détail des connecteurs dans la partie configuration

## Corrections

- En mode console, on affiche les erreurs sorties de ChoiceActionExecutor
- Ajout d'une fonction du model DocumentActionEntite::getDocumentOdlerThanDay
- l'API V1 retournait un code d'erreur 201 au lieu de 200 qui n'était pas attendu par les client V1

## Evolutions

- La taille du libellé des connecteurs est porté de 32 caractères à 128 caractères
- Ajout de la clé de premier niveau "heritage" dans le fichier YAML des connecteurs d'entité. 
    Cette clé permet de merge le fichier avec un autre fichier défini dans le repertoire common-yaml (Expérimental)  
- Les exceptions RecoverableException et UnrecoverableException ont leur propre fichier pour une utilisation plus simple
- Les actions de connecteurs peuvent être partagé entre connecteurs 
        (soit dans le répertoire action de Pastell, soit dans n'importe quel connecteur)

## Elements déprécié

- La majorité des fonctions de GEDConnecteur sont dépréciées et seront retiré dans la prochaine version mineur