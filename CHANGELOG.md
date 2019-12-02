# [2.0.15]

## Ajouts

- Le champ `verrou` dans le connecteur de purge qui permet de lancer les jobs créés avec un verrou spécifique 
    (à la deuxième tentative le job prend le paramétrage des fréquences) #973

## Correction

- Correction de l'arbre des entités incorrectes dans certain cas (backport pastell 3.0) #664
- Les mails textes avec attachement provoquaient l'ajout d'une pièce jointe fantôme sur un serveur Outlook #893 
- Correction pour les appels api patch externalData #905
- Les caractères multioctets pouvaient être tronqués lors de l'envoi au iparapheur #944
- Les actions automatiques des documents sont verrouillées si il n'y a pas de connecteur associé #947

## Evolution

- Ajout de la constante NB_JOB_PAR_VERROU (à éviter) #924

# [2.0.14] - 2019-09-03

## Correction

- En cas d'envoi de dates trop précises pour la date de l'acte, la génération du bordereau SEDA ne peut pas se faire #751
- Les bordereaux PES étaient mal générés s'il y avait un accent dans LibelleCodCol #755
- Les PES retour contenant des accents étaient mal récupérés #861
- L'export CSV des utilisateurs n'utilisait pas le rôle sélectionné #862
- Le nombre d'utilisateurs trouvés lors d'une recherche ne correspondait pas au nombre d'utilisateurs retournés #862
- Les fichiers Word ne pouvait pas être transformés en PDF dans actes-generique et actes-automatiques #870 

## Evolution

- Possibilité de supprimer tous les agents avant leur import (id_e=0) #646

# [2.0.13] - 2019-06-13

## Ajouts

- Support du parapheur FAST pour les flux `actes-generique` et `actes-automatique` (nécessite l'installation de l'extension
    `pastell-docapost-fast`) #661
- Ajout d'un glaneur SFTP dont le fonctionnement est identique au glaneur local #650
- Possiblité de télécharger un fichier sur un serveur webdav via la fonction `get()` de la classe `WebdavWrapper`
- Possibilité d'ajouter des headers lors de l'envoi de documents via `WebdavWrapper::addDocument()`

## Evolution

- S2low Global: ne plus se baser sur 'nom_flux_actes' pour la récupération de la classification #693
- Le connecteur de purge permet de modifier les propriétés éditables du document (ex: cocher la case envoi SAE) #692

## Correction

- Implémenter `SAEConnecteur::getLastErrorCode()` pour assurer la rétrocompatibilité
- Le script crontab n'était pas correct #649
- flux `document-a-signer` : si le document n'est pas archivé sur le parapheur à la première tentative, le document ne peut plus poursuivre son chemin normalement #698
- flux `commande-generique` : si le document n'est pas archivé sur le parapheur à la seconde tentative, le document ne peut plus poursuivre son chemin normalement #698
- Il était possible d'uploader des fichiers sur des documents via API alors que les documents n'étaient pas éditables #662

# [2.0.12] - 2019-04-16

## Evolution

- Implémentation de la nouvelle notice Actes 2.2 #657 : 
    - La liste des type ne dépend plus que de la nature
    - On supprime le code 99_AU
    - La liste est ordonnée suivant l'ordre alphabétique du libellé
    - On mets en tête les code 99_XX
     

## Ajout

- Ajout du script installation/bulk-set-etat.php permettant de changer en masse l'état de document #660
- Ajout d'un script supervision/workspace_size_by_entite.php permettant d'obtenir la taille des documents par entité #663

## Correction

- La classe CurlWrapper pouvait accepter plusieurs fois le même header #656
- mailsec html: l'utilisation de %LINK% avec plusieurs utilisateurs ne renvoyait que le lien du premier destinataire #671

# [2.0.11] - 2019-03-14

***Cette version nécessite une modification de la base de données***

## Correction

- Refactoring du mail sécurisé afin de permettre l'ajout de fichier dans les réponses à des mails sécurisés #525
- La typologie des actes pouvait être incorect quand on supprimait un fichier après avoir selectionné la typologie #569
- Le démon peut verouiller des jobs dans des cas exceptionnels #571
- Reprise du calcul des fréquences #632
- Les documents helios n'étaient pas supprimables en état `info-tdt` #636
- Le filtre sur le rôle lors de la recherche d'utilisateur n'était pas conservé lors d'un changement de page #638
- Il n'y a plus besoin de s'abonner aux notifications Mail sécurisé pour les flux utilisant ce connecteur #642
    - **Les utilisateurs abonnés aux notifications "reception" et "reception-partielle"  de flux hors mailsec (pdf-generique, flux spécifique...) doivent changer leurs notifications pour sélectionner le bon flux.**
- flux actes: permettre la modification de la typologie des pièces après la récupération i-parapheur #634
- Rester sur la page d'information après la création d'une entité #643
- Le script de migration a pu "oublier" d'encoder des tables en UTF-8, 
ce qui posait des problèmes de performance sur les jointures sur deux tables avec des encodages différents.
Le script script/bug/set-database-encoding-to-utf8.php permet de palier au problème. #613
- Ajout de la vérification de l'encodage des tables sur la page de test du système. #613

## Évolutions

- Sharepoint est maintenant utilisable via le connecteur depot-webdav #610
- Ajout de max_execution_time dans la configuration PHP à vérifier #647

# [2.0.10] - 2018-12-12

***Cette version nécessite le passage du script script/plateform-update/2.0.x/to-2.0.10.php***

## Correction

- bugfix si doublon PES sur le Tdt #496
- Correction du hash de la politique de signature de la DGFip pour la signature locale des fichiers PES Aller. #475
- Correction des erreurs de lecture de fichier YAML sous Windows #455
- Problème de timezone dans SQLQuery #452
- Correction d'une page blanche lors du versement en GED via webdav qui échoue #440
- Correction d'une notice bloquante sur la création de document échangé avec la préfecture #486
- Correction du champ "passé par l'état" qui affichait tous les états de tous dans les documents dans la recherche avancée #441
- Inversion des champs "Expressions rationnelles pour associer les fichiers" et "Métadonnées du formulaire" dans le glaneur local pour plus de clarté #471
- Typo sur les flux helios (PES Retour -> PES Acquit) #470
- Si la taille d'un rôle dépassait les 32 caractères, les droits n'étaient pas attribués #501
- Correction du retour de l'API /api/v2/entit/X/connecteur/Y/action/action-name en cas d'erreur sur l'appel #509 
- Correction d'un bug sur le flux commande : si le document n'est pas archivé sur le parapheur à la première tentative, le document ne peut plus poursuivre son chemin normalement #508
- Lorsque l'actes est en erreur sur s2low, on ne récupérait pas la raison de l'erreur #504 
- Ajout de la colonne Verrou sur les connecteurs et les documents de la zone "Travaux programmés" #510
- Le script de purge du journal vers l'historique pouvait échouer de manière silencieuse #513
- La partie `Configuration PHP` du test du sytème ne comparait pas correctement les valeurs attendues et réelles #514
- La notification de rejet d'un pdf générique dans le parapheur n'était pas déclenchée sur la bonne action #515

## Évolutions

- Ajout de la variable d'environnement docker AUTHENTICATION_WITH_CLIENT_CERTIFICATE permettant d'activer l'authentification par certificat client (désactivée par défaut) #507
- Possibilité d'ajouter une barre de progression pour l'upload des fichiers (propriété progress_bar) #17


## Ajouts

- Actions des connecteur-type: mise à jour des actions Signature et ajout des actions SAE #484
- Ajout de la constante JOURNAL_MAX_AGE_IN_MONTHS permettant de savoir ce qu'il faut verser sur la table journal_historique #512
- Ajout de tests et d'information sur la page "Test du système" sur le journal #512
- Ajout de la constante UPLOAD_CHUNK_DIRECTORY pour le téléchargement partiel des fichiers
- Check de la base de données sur la page système #519


# [2.0.9] - 2018-10-29

## Correction

- Il y avait un warning sur le bouton "Suivant" #480
- Il y avait un problème d'encodage sur le champ "reponse" du Mailsec #478
- Confirmation de la supression des mails sécurisés #443
- Passage du test de génération des empreintes de bordereau PES en sha256 #442
- Pose d'un index sur la table agent (siren,matricule) 
- Recherche avancée : Le champ `Dernier état` affichait tous les états de tous les documents lorsque l'entité ne possédait pas d'entité fille
- Le test d'enregistrement d'un warning se fait dans pastell.log et plus dans le log d'Apache
- Problème lors de l'envoi des mail sécurisé en HTML (pas de reception de la NDR) 
- Il manquait la fonction getPESRetourListe() pour la classe FakeTdT #460
- Il manquait connecteur-type: TdT sur l'action verif-tdt du flux actes-automatique (du coup la fréquence n'était pas prise en compte) #462
- Annuaire MailSec: Sur le détail d'un contact le bouton supprimer retournait une erreur et il fallait des droits sur l'entité racine pour modifier un contact #467

## Ajouts

- Ajout de la notification tdt-error dans le cas "Une erreur est survenu lors de l'envoi..." #449
- Ajout du domaine PES_Marche pour la génération du bordereau SEDA PES #479
- Connecteur S2low (necessite la version 3.0.15 de S2low): Récupération des réponses de la préfecture (alimente le flux actes-reponse-prefecture de l'extension pastell-supplement-v2) #397
- Connecteur i-Parapheur : possibilité d'archiver les documents après leur récupération plutôt que de les effacer #457
- Connecteur Mail sécurisé : Gérer la substitution des mots clés référençant des données dans un fichier json lors de la création des mail (body & subject) #454
- Flux PDF générique : ajout d'un fichier de méta-données pour l'envoi au mail sécurisé
- Script permettant de récupérer une preuve au format texte d'une entrée du `journal_historique` #476
- Ajout de l'action commune ./action/CommonExtractionAction.class.php et de la librairie ExtractZipStructure.class.php #483

# [2.0.8] - 2018-08-21


***Cette version nécessite une modification de la base de données***

***Cette version nécessite le passage du script script/plateform-update/2.0.x/to-2.0.8.php***


## Correction 

- Il manquait connecteur-type: SAE sur l'action validation-sae du flux actes-automatique
- Correction de l'expression PES Retour par PES Acquit  dans helios-generique et helios-automatique #427
- Problème de retour sur la bonne page dans la navigation des documents
- Correction du lien de retour lorsque l'on ordonne la télétransmission des actes par lot
- Impossibilité de récupérer les classifications sur d'autres flux qu'actes générique sur le connecteur s2low global. 
- La règle AR048 s'applique désormais aux actes de nature "contrat, conventions et avenants" et dont la classification commence par 4 #433 
- La récupération d'un journal d'une taille importante utilisait un résultat bufferisé entrainant une forte consommation mémoire
- Lien url lors de la notification d'un acte acquitté
- En cas de fichier uploadé incorrectement, l'erreur n'apparaissait pas immédiatement et était donc difficile à tracer #376
- Flux PDF Générique : création d'une action pre-orientation qui permet d'avoir une action automatique vers orientation #435
- Flux Actes-* : ajout de l'action automatique sur la récupération de l'AR d'annulation #257
- Connecteur SEDA NG : les noms de fichier contenant un & généraient des bordereaux invalides
 
## Ajouts

- Ajout du caractère - comme séparateur de mot pour la recherche dans les champs select de collectivités #410
- Ajout d'un script pour modifier le mot de passe d'un utilisateur sur le serveur (update-password.php)
- Ajout de la fonction de l'API /document/count permettant de compter le nombre de documents par entites, types et actions #432
- Ajout de répertoire d'erreur pour les connecteur GlaneurLocal #421
- Ajout d'un connecteur global GlaneurLocal permettant de vérifier les répertoires d'erreurs des connecteurs #421
- Ajout de l'ADMIN_EMAIL dans le test du système
- Ajout des élements importants du php.ini dans le test du système
- Script d'extraction de la configuration extract-conf.php
- Action automatique LDAP de synchronisation des utilisateurs #430
- Script d'installation des fréquences par défaut #425
- Fonction MemoryCache::FlushAll() permettant de vider le cache
- Ajout de la constante CACHE_TTL_IN_SECONDS (10 secondes par défaut)
- Un cache de CACHE_TTL_IN_SECONDS secondes est mis sur les élements (connecteur, flux, connecteur-type, rôles) récupérés des extensions #418 #419 #420
- API : la fonction /Utilisateur/Role/:id_u renvoi maintenant la liste des droits en plus (modification v1 : list-role-utilisateur.php) #391 
- API : ajout de l'API de fréquence de connecteurs #318
- Connecteur de purge : possibilité de programmer une autre action que Supprimer #399
- Connecteur de purge : déclenchement de l'action de manière asynchrone 
- Connecteur de purge : possibilité de selectionner les document qui sont passé par un certain état #389 
- Log : ajout du contexte (id_e,id_d,id_verrou,...) sur les messages de logs #317



# [2.0.7] - 2018-07-18


***Cette version nécessite une modification (potentiellement longue, ajout d'un index) de la base de données***


## Ajouts 

- Flux hélios: ajout de opération comptable (<Fonction V>) et nature comptable (<Nature V). Profil_seda_pes_v3.1.0 #409
- Ajout d'un index sur document_index(name,value) et réduction de 128 à 64 octets du champs field_name #411
- Possibilité de supprimer le job d'un document
- Possibilité de supprimer les documents en fatal-error
- le CHANGELOG des extensions est disponible

## Evolutions

- Mail sécurisé : Possibilité d'envoyer un mail en HTML, possibilité de modifier la position du lien, possibilité de mettre des données provenant du flux #408
- Connecteur iParapheur: envoi de fichier de signature avec reconnaissance du format par iParapheur (pour la co-signature) #412
- Connecteur ged-ssh : les droits de dépot sont fixé à 0666
- Connecteur seda-ng : ajout de la commande connecteur_info (la valeur est passé au générateur, mais n'affiche rien) #407
- Module actes : possibilité d'avoir un producteur variable sur les bordereau SEDA en fonction de la présence de données à caractère personnel #407 
- Module helios : si le fichier est en doublon sur le tdt, on passe le document en erreur
- Mail sécurisé : Possibilité d'envoyer un mail en HTML, possibilité de modifier la position du lien, possibilité de mettre des données provenant du flux #408
- Les fichiers copié via SFTP sur le connecteur de dépot peuvent être déposé avec un suffixe (ex: .part) #405
- Ajout du loggeur standard dans les classes connecteurs et dans les classe d'actions (flux ou connecteur) #398
- Flux actes-automatique et actes-generique : les objets peuvent avoir plusieurs lignes 
- Améliorations des performances #423 #424 
- Affichage de statistique sur le systeme de fichier du workspace #422
- Connecteur Libersign : passage de la signature en sha256 #416

## Correction

- BugFix: GlaneurLocalDocumentCreator: En cas de création de document non valide on ne retourne pas l'id_d alors on ne supprime pas l'élément glaner. Maintenant on intercepte UnrecoverableException et on stop le traitement automatique.
- Connecteur i-Parapheur : test du retour du parapheur pour l'archivage, si l'archivage n'est pas ok, on ne fait pas l'action #406
- Correction d'un problème d'encodage de fichier dans la fonction DonneesFormulaire::copyFile #404
- Flux actes générique : suppression d'une erreur fatale si l'AR Actes n'est pas un fichier XML #401
- Actes générique : Erreur de nommage des fichiers revenant du Tdt quand le nom de l'objet comporte un / #236 
- Connecteur de purge : on ne fait pas le traitement si l'action supression n'est pas possible #388
- Il n'était pas possible de poster des fichiers avec le même nom sur le même élément Pastell #234
- Bugfix: correction de la modification du champs externalData connecteur_info qui n'enregistrait pas les information en POST 
- Docker : mise à jour de libersign
- Il manquait connecteur-type: SAE sur l'action validation-sae du flux actes-automatique


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
