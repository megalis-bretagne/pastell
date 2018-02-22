# [2.0.5]

## Corrections

- Correction d'un bug rendant impossible la modification d'une entité de base d'un utilisateur #328
- Correction d'un bug permettant de supprimer une entité référencé dans comme entité de base d'un utilisateur #329
- Correction du fichier de configuration Apache de l'installation pour Libersign #311
- Connecteurs de dépôt: Correction du test d'éxistence de répértoire ou fichier ~Connecteur
- [SEDA NG] Correction des balises repeat ajoutées à la fin des enfants du noeud parent plutot qu'immédiatement après le noeud en question
- Génération du bordereau SEDA PES: Date du PES AQUIT/NACK, si inexistante (flux antérieurs à 2014) date du PES_Aller #343
- Suppression de l'encode en ISO-8859 lors de la synchronisation LDAP
- retrait des 'Expérimental' pour les développements en cours #345
- Correction d'un warning lors de la génération d'un bordereau SEDA PES ne contenant pas de PJ.
- Bug sur la fréquence des connecteurs sur ie11 #342
  
## Évolutions

- Ajout d'un contôle sur la page système pour vérifier que Curl est compilé avec OpenSSL et pas NSS #322
- Ajout d'un contrôle sur la page système pour vérifier que l'encodage pour accéder à la base de données est bien UTF-8 #293
- Légère modification de la présentation des vérifications systèmes
- Possibilité d'envoyer les archives sur le connecteur as@lae par morceaux (pour dépasser la limite des 2Go des versions 1.6) #339 
- flux pdf-generique: Le champs is_recupere (mail récupéré) est maintenant mis à jour après l'état "Reçu" (égale à 1). Il est donc renseigné avec les métadonnées envoyées en GED2 #341 ~Evolution ~Flux
- Ajout de la possibilité de supprimer le document Pastell une fois archivé sur le SAE pour les flux helios-generique et helios-automatique
- La surveillance du démon prend en compte les jobs uniquement si ceux-ci sont en retard et qu'ils ont tourné au moins une fois
- Génération SEDA des PES : si le LibelleCodBud n'est pas disponible, on mets le CodCol à la place

## Ajouts

- Le CHANGELOG est disponible pour l'administrateur #336
- [SEDA NG] - Possibilité de mixer les annotations repeat avec les autres annotations au sein du même commentaire #340

# [2.0.4] - 2018-02-08

## Corrections

- Bug sur les fichiers de méta-données non traité correctement par le connecteur glaneur doc
- Suppression d'un bouton utilisé dans le développement apparu en 2.0.3 sur le connecteur dépôt CMIS
- Bordereau SEDA incorrect sur le parsing des gros fichier PES
- Bug sur le connecteur mailsec qui ne prenait pas en compte le return-path du connecteur UndeliveredMail
- Bug sur les fichiers envoyés en GED qui étaient considérés comme des fichiers de type "texte"
- Bug sur les métadonnées incorrectes (en XML) lors de l'envoi en GED avec le connecteur depot-cmis
- Correctif sur la compatibilité du retour des fonctions de l'API V1 :
    - action-connecteur-entite.php "1" à la place de true
    - les réponses ne sont plus en mode pretty-print (pour les appels V1)


## Évolutions

- Le flux commande générique peut être automatique
- Possibilité de choisir un type de dépôt "Fichiers à la racine" pour les connecteurs de dépôt #334 ~Evolution ~Connecteur


## Ajouts

- Connecteur creation-pes-aller #332 ~Connecteur
- Connecteur glaneur-local permettant de glaner n'importe quel fichier sans manifest
- Flux préversement actes permettant avec l'utilisation du glaneur précédent de faire du versement à partir d'un export SRCI ou FAST
- force-delete-connecteur et force-delete-module pour la suppression des éléments et documents obsolètes (test du système) lors du passage 1.4 -> 2


# [2.0.3] - 2017-12-13

## Corrections

- Correctif majeur sur la compatibilité du retour des fonctions de l'API V1 :
    - action.php:result "1" à la place de true
    - modif-document.php:formulaire_ok "1" à la place de 1
    - renvoi d'une erreur 400 à la place d'une erreur 200
- modification menu gauche sur "nouveau utilisateur" #247
- Correction fichier avec des caractères accentué (compatibilité V1)
- Typo fonctionnement libersign actes et helios 
- Notice sur envoi i-Parapheur si la chaine métadata est mal formée #325
- Notice sur envoi s2low si pas de droit sur s2low #324 

## Évolutions

- Modification des droits lors du dépot d'un fichier SSH (ancien connecteur)
- Ajout de Monolog pour la gestion des logs (https://github.com/Seldaek/monolog)
- Logs des actions, des workers, des appels de l'API et du démon

## Ajouts
- Constante LOG_LEVEL
- Connecteur Glaneur de document
- Flux Document PDF (Générique)


# [2.0.2] - 2017-11-24

## Corrections

- Prise en compte du paramètre action_param pour l'appel API de l'action d'un connecteur
- Correction sur la bibliothèque de mail HTML
- Correction de la signature locale (actes et helios) qui n'était pas fonctionnelle
- La mise à jour automatique de la page démon est à nouveau fonctionnelle 
- Problème archivage i-Parapheur en cas de full disk (uniquement pour les flux standard) #313
- Problème de selection des action sur la fréquence des connecteurs 
- Compatibilité de l'API V1 : la clé action-possible n'était plus générée sur la fonction detail-document.php

## Évolutions

- Journalisation de la consultation unitaire des documents (mail sécurisé)
- Ajout de la compatibilité Libersign V1 dans le docker

## Ajout

- Possibilité d'envoyer n'importe quel métadonnée au i-Parapheur (flux à modifier) #309
- Support partiel du traitement par lot sur une recherche avancé (les redirections ne retourne pas sur la recherche) #312

# [2.0.1] - 2017-11-08

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
- L'API V1 retournait un code d'erreur 201 au lieu de 200 qui n'était pas attendu par les client V1
- Bug dans le flux changement d'email (impossible de créer un flux changement d'email)
- Bug sur l'API V1 : les données doivent être passé en latin1 pour faire comme sur une V1 

## Évolutions

- La taille du libellé des connecteurs est porté de 32 caractères à 128 caractères
- Ajout de la clé de premier niveau "heritage" dans le fichier YAML des connecteurs d'entité. 
    Cette clé permet de merge le fichier avec un autre fichier défini dans le repertoire common-yaml (Expérimental)  
- Les exceptions RecoverableException et UnrecoverableException ont leur propre fichier pour une utilisation plus simple
- Les actions de connecteurs peuvent être partagé entre connecteurs 
        (soit dans le répertoire action de Pastell, soit dans n'importe quel connecteur)

## Elements dépréciés

- La majorité des fonctions de GEDConnecteur sont dépréciées et seront retiré dans la prochaine version mineur