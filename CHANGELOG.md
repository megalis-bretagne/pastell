# [2.0.7]

## Evolutions

- Module helios : si le fichier est en doublon sur le tdt, on passe le document en erreur
- Connecteur ged-ssh : les droits de dépot sont fixé à 0666
- Connecteur seda-ng : ajout de la commande connecteur_info (la valeur est passé au générateur, mais n'affiche rien) #407
- Module actes : possibilité d'avoir un producteur variable sur les bordereau SEDA en fonction de la présence de données à caractère personnel #407 
- Mail sécurisé : Possibilité d'envoyer un mail en HTML, possibilité de modifier la position du lien, possibilité de mettre des données provenant du flux #408


# [2.0.6] - 2018-06-06

## Ajouts 

- Fonction DonnesFomulaire::getFileNumber() permettant d'obtenir le nombre de fichier un champs fichier multiple

## Evolutions

- Connecteur i-Parapheur
    - Fonction du connecteur parapheur permettant de récupérer les annexes ajoutés sur le parapheur après l'envoi

- Récupération des annexes de sortie du connecteur de signature pour les flux du coeur utilisant le parapheur

- Glaneur local: adaptation pour permettre l'utilisation des $matches au niveau des métadonnées

## Corrections

- Interface:
    - Correction du bug rendant impossible le changement de fréquence des notifications
- Librairie:
    - classe SSH2: suppression du test file_exists qui renvoi toujours false (depuis php7) pour la suppression du fichier glané #396
- Démarrage:
    - le démon redémarre correctement après un redémarrage de MySQL    
- Connecteur as@lae:
    - correction d'un bug empechant la récupération d'un identifiant de transfert contenant des espaces
- API:
    - Correction de l'inversion des APIs `modif-connecteur-entite` et `edit-connecteur-entite` #402


# [2.0.5] - 2018-04-30

## Corrections

- Interface:
    - le lien suivant sur la liste des utilisateurs renvoyait sur le détail de l'entité
    - un bug rendait impossible la modification d'une entité de base d'un utilisateur #328
    - un bug permettait de supprimer une entité référencé comme entité de base d'un utilisateur #329
    - le champ dernier état de la recherche avancée n'affiche que les états liés au type du document sélectionné #187
    - suppression du bouton *modifier* sur les connecteurs ci ceux-ci ne contiennent pas de formulaires #371
    - suppression du message d'erreur et ajout de la redirection vers la page demandée lors de l'authentification CAS #363
    - adullact-projet -> libriciel dans le commentaire du connecteur Libersign #349
- Installation:
    - correction du fichier de configuration Apache de l'installation pour Libersign #311
    - le script installation/bulk-action-auto.php nettoie maintenant les action déjà en cours #326
    - Fix de l'installatin sous CentOS : la configuration de cloudoo prend en compte l'utilisateur apache défini dans DAEMON_USER #370
- Compatibilité API V1:
    - le tableau JSON est systématiquement encodé en string #338
    - décoder les données issues de l'API avant d'appliquer les filtres de contrôle #362
    - vérification systématique du droit d'édition pour les actions (ce faisait via l'API ou via des rules explicite) #347
    - les entrées de receive-file.php était incorrecte (field => field_name et num=>file_number)
    - la recherche de documents par type ne renvoyait plus d'erreur lorsque l'utilisateur n'avait pas les droits de lecture #394
- Démon Pastell:
    - bug sur la fréquence des connecteurs sur ie11 #342   
    - supression des jobs sur les documents si on en réinscrit un nouveau #305
    - la surveillance du démon prend en compte les jobs uniquement si ceux-ci sont en retard et qu'ils ont tourné au moins une fois
    - poser d'un verrou avant la lecture ou l'écriture d'un fichier YML, cela pouvait entrainer des disparitions de données en cas de forte charge #330
- Connecteur LDAP: ~Connecteur
    - suppression de l'encode en ISO-8859 lors de la synchronisation LDAP
    - modification de la description de l'attribut pour le connecteur LDAP (sensibilité des attributs à la casse) #374
- Connecteurs de dépôt: ~Connecteur
    - correction du test d'éxistence de répértoire ou fichier
    - retrait des 'Expérimental' pour les développements en cours #345
- Connecteur glaneur-local : #346  ~Connecteur
    - désactivation du traitement du glaneur en cas d'erreur lors de la suppression ou du déplacement du fichier récupéré
    - lister le contenu des répértoires
    - permettre le test via un fichier exemple
    - les propriété multiple n'étaient pas prise en compte 
- Connecteur SEDA NG:  ~Connecteur
    - correction des balises repeat ajoutées à la fin des enfants du noeud parent plutot qu'immédiatement après le noeud en question
    - possibilité de mixer les annotations repeat avec les autres annotations au sein du même commentaire #340
    - correction d'un bug si on essaye de mettre des caractère de contrôle XML dans un noeud texte (&) #236
    - correction d'un problème de comptage du nombre de propriété dans le connecteur SEDA-NG #304
    - correction autorisant les fichiers commençant par `-` lors du versement au SAE #381
    - la commande pastell:now du connecteur SEDA-NG prend en compte un paramètre de formatage de date. Le format est celui de la [fonction PHP date](http://php.net/manual/fr/function.date.php). #379
    - possibilité de traiter le cas des repeat dans les repeat.   
    - possibilité de traiter les sous-repertoire pour la génération d'archive
- Génération du bordereau SEDA PES:
    - date du PES AQUIT/NACK, si inexistante (flux antérieurs à 2014) date du PES_Aller #343    
    - correction d'un warning lors de la génération d'un bordereau SEDA PES ne contenant pas de PJ.
    - si le LibelleCodBud n'est pas disponible, on mets le CodCol à la place
- Flux Hélios: ~Flux
    - l'objet du PES ne disparaît plus s'il est déjà mis #373
    - correction de l'ordre des champs de recherche avancée pour les modules helios #372
    - récupération de l'erreur Helios en cas d'erreur sur le TdT #375
    - helios-automatique: il manquait l'action prepare-iparapheur #395
- Flux Actes: ~Flux
    - correction du bouton "Transmettre au TdT" présent alors que le doc a été envoyé #306
    - Actes : Si le certificat de dépot est sans login/mot de passe alors il y a une limitation sur le certificat de télétransmission qui doit aussi être sans login/passe #385 
    - Actes-preversement-seda : passage en majuscule du numéro interne pour les versement vers actes-automatiques
- Flux Commande: #276 ~Flux
    - possibilité de choisir l'envoi en GED alors que le document a commencé le cheminement
    - le bouton d'envoi au i-parapheur était de nouveau visible en cas de modification
    - si le libéllé de la commande contenait des caractères de controles, on ne pouvait pas envoyer le document au parapheur

## Évolutions

- Interface:
    - les entités mères et filles ne sont plus au même niveau dans "Navigation dans les collectivités" #368
    - prise en compte du filtre lors du traitement par lot lorsqu'il est défini #369
    - mails sécurisés : amélioration de l'affichage demande des mots de passe #358
- Connecteur as@lae:  ~Connecteur
    - possibilité d'envoyer les archives sur le connecteur as@lae par morceaux (pour dépasser la limite des 2Go des versions 1.6) #339  
- Flux Hélios: ~Flux
    - ajout de la possibilité de supprimer le document Pastell une fois archivé sur le SAE pour les flux helios-generique et helios-automatique
- Flux PDF générique: ~Flux
    - le champs is_recupere (mail récupéré) est maintenant mis à jour après l'état "Reçu" (égale à 1). Il est donc renseigné avec les métadonnées envoyées en GED2 #341
    - les annexes sont maintenant transmises au i-Parapheur #360
    - changement du libellé du lien "Liste des sous-types" sur pdf-generique et doc-a-faire-signer #357
    - redirection sur le flux PDF Générique vers un onglet lorsqu'on clique sur enregistrer #359

## Ajouts

- Interface:
    - le CHANGELOG est disponible pour l'administrateur #336
- Installation:
    - script add-action-connecteur.php pour déclencher l'action d'un type de connecteur
    - contrôle sur la page système pour vérifier que Curl est compilé avec OpenSSL et pas NSS #322
    - contrôle sur la page système pour vérifier que l'encodage pour accéder à la base de données est bien UTF-8 #293
- API V2:
    - fonction de l'API PATCH /entite/:id_e/document/:id_d/externalData/:field oublié jusqu'ici
- Flux Actes: ~Flux
    - ajout des actes V2 (envoi papier + typologie des pièces)
- Divers:
    - nouvelle action DefautNotify permettant de passer par l'état et notifier
    - fonction CurlWrapper:getLastOutput() pour récupérer la derniere sortie de curl


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