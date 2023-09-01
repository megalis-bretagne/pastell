# [4.0.7] - 2023-09-11

## Corrections

- Modification des noms des boutons d'actions du connecteur recup-parapheur #1877
- L'état ```send-tdt-erreur``` permet maintenant la modification du ```Numéro de l'acte``` et la suppression pour les flux studio #1691
- Il n'était pas possible de supprimer des dossiers en état "Erreur lors de la vérification du statut de signature" #1894

## Suppressions

- Connecteur `Dépôt local` qui n'est plus fonctionnel depuis la 4.0.0 #1861

# [4.0.6] - 2023-08-14

## Évolutions

- Uniformisation des noms de dossiers d'archivage #1802
- Les types de dossiers sont triés par pack dans la configuration (association) d'un nouveau type de dossier #1802
- Le script `installation/force-delete-module.php` est remplacé par la commande `app:module:force-delete-module`. Les associations aux connecteurs sont maintenant bien supprimées même s'il n'existe pas de document pour le type de dossier #1284
- Seul l'utilisateur peut gérer ses notifications, l'administrateur n'a plus les droits #1886
- Le script de changement d'état d'un ensemble de document (3.1) est disponible en commande `app:module:set-new-status-document-batch` #1893

## Corrections

- Problème d'affichage des métadonnées dans les propriétés spécifiques des générateurs SEDA #1879
- Des dépréciations pouvaient s'afficher par API lors de l'utilisation de CAS en mode debug #1881
- Correction d'une faute d'orthographe dans le message après avoir re-tamponné un acte #1875
- Uniformisation du formulaire de création d'entité avec le formulaire de création d'utilisateur #1681
- Dans un flux studio, la propriété `is_equal` de plusieurs étapes de même type prend en compte le numéro de l'étape #1891

# [4.0.5] - 2023-07-10

## Évolutions

- L'action `Extraction de l'archive` étant automatique, le bouton n'est plus visible pour le flux `Pièces de marchés par étapes` #1835
- Il est possible de générer des UUID à la place des 7 caractères actuels pour les identifiants des nouveaux dossiers #1098
- Le script installation/test-last-job.php est remplacé par la commande `bin/console app:daemon:notify-check`
  (Modification des notifications d'alerte du gestionnaire des tâches automatiques) #1810

## Corrections

- La saisie de texte dans un champ de type `fichier` à la création d'un dossier sur API provoque un message d'erreur #1780
- Il n'était pas possible d'envoyer un fichier de métadonnées JSON à iparapheur avec le flux `Document PDF` #1868

# [4.0.4] - 2023-06-23

## Corrections

- Gestion des URL non standards des mails sécurisés de Pastell v3

# [4.0.3] - 2023-06-12

***Cette version nécessite une mise à jour du générateur SEDA en 1.0.1***

## Évolutions

- Le démon et les workers sont lancés par une commande Symfony #1854
- Lorsqu'un document pdf générique est dans l'état traitement terminé, la modification reste possible pour envoi au SAE #1840
- L'utilisateur peut gérer ses tokens via l'API #1785
- L'url des générateurs SEDA de la 3.1 sont remplacés par l'adresse http://seda-generator #1853
- Sur un flux avec une étape de mail sécurisé, le champ `Destinataire(s)` n'est plus obligatoire, 
  il faut au minimum remplir un des champs suivant : `Destinataire(s)`, `Copie à` ou `Copie cachée à` #1629

## Corrections

- Le job programmé des actions ne devant pas lancer l'action automatique suivante (updateJobQueueAfterExecution à false) n'était pas supprimé après l'execution (ex: traitement par lot de réouverture) #1828 
- L'action `Transmettre au TdT` n'était pas possible lorsque l'on cochait l'étape dans le formulaire après réception de la signature sur `Helios générique` #1740
- L'action `Verser à la GED` n'était pas possible lorsque l'on cochait l'étape dans le formulaire après transmission au Tdt sur `Helios générique` #1736
- Lors du paramétrage d'un dossier associé à un connecteur parapheur, s'il n'y a aucun sous type, une exception n'est plus levée #1631
- L'API externalData renvoie une erreur si le sous-type du parapheur est invalide #1805
- À la suppression d'un dossier dont le cheminement contient une étape de mailsec avec réponse, les réponses sont également supprimées #1728
- Une URL webdav ne finissant pas par un `/` dépose aussi les fichiers dans le répertoire attendu #1603
- Les descriptions des destinataires d'un mailsec peuvent contenir un `@` #1588
- Si envoi en signature FAST sans circuit ou configuration du circuit à la volée, le dossier rentre dans l'état `Erreur lors de l'envoi du dossier à la signature`
et l'utilisateur peut modifier les champs manquants #1850
- La récupération des factures avec le connecteur FakeCPP ne fonctionnait plus #1715
- Les rattachements sur des unités d'archives de sous-niveau ne fonctionnaient pas avec le SAE Vitam

# [4.0.2] - 2023-05-15

## Corrections

- Il n'est plus possible de créer un document par API sur une entité désactivée #1702
- La création d'un rôle nécessite une valeur dans les champs rôle et libellé #1816
- Il n'était pas possible de créer un utilisateur à partir d'une entité #1833
- La variable d'environnement `PASTELL_SITE_BASE` ne nécessite plus de terminer par un / #1830
- Le paramétrage des notifications de l'utilisateur n'est plus soumis au droit `utilisateur:edition` #1834
- Un utilisateur peut ajouter et supprimer ses propres notifications en toutes circonstances et modifier celles dont il a les droits #1834
- Les entités dans le Breadcrumb sont affichées par ordre alphabétique #1658
- Il n'était plus possible d'avoir plusieurs adresses emails pour les notifications système #1842
- La génération d'un bordereau de test SEDA ne génère plus une page d'erreur si celui-ci contient un fichier zip #1800
- Lorsque la liste des contacts des groupes hérités dépasse trois contacts, l'excédent est masqué et la liste est dépliable #1501
- Lors d'un ajout d'un élément à un flux studio, si l'identifiant a été oublié, l'utilisateur est alerté avant l'envoi des données #1316
- Le connecteur LDAP n'utilisait pas les certificats de l'OS en LDAPS #1844
- Permettre l'utilisation d'un certificat et d'une clé différente pour le domaine mail sécurisé #1826

## Ajouts

- Récupération de l'Accusé de réception dans le cas d'annulation de transaction TDT préfecture #639
- Gestion de l'état "RejetSignataireExterne" d'iparapheur V5.0.12 #1776
- Dans les recherches avancées, le champ `Non passé par l'état` est disponible pour tous les types de dossier #1851
- Envoi d'un email de test vers ADMIN_EMAIL dans les paramètres système #1838

# [4.0.1] - 2023-04-10

## Corrections

- Les actions de connecteurs sont toutes accessibles uniquement avec le droit `connecteur:edition` (au lieu de `entite:edition`) #1818
- L'ajout de connecteur est possible avec uniquement `connecteur:edition`
- Il n'était pas possible de créer un flux studio avec plusieurs étapes SAE #1820
- Le cursor se place automatiquement dans les barres de recherche utilisant `select2` #1595

## Ajouts

- Variable d'environnement `OPENSSL_CIPHER_STRING_SECURITY_LEVEL` qui permet de définir le niveau de sécurité d'OpenSSL #1782
- Ajout d'un conteneur cacerts permettant d'ajouter des certificats locaux au magasin du conteneur web #1827

## Évolutions

- La partie mails sécurisés doit utiliser une entrée DNS différente de Pastell #1822

# [4.0.0] - 2023-03-21

***Cette version nécessite une modification de la base de données***
***Cette version nécessite une mise à jour du générateur SEDA en 1.0.0***

## Corrections

- Pour voir les utilisateurs d'une entité, le droit "utilisateur:lecture" est désormais nécessaire #1528
- Les accents sont pris en compte à la création d'une entité si une erreur survient à la validation du formulaire #1103
- Il n'est plus possible de créer des entités filles pour une entité désactivée #1708
- Les valeurs "true" ou "false" ne sont plus remplacées par "1" ou "0" sur les champs texte #303
- Quand un document est supprimé ou mis en état erreur fatale, les jobs associés sont supprimés #1723
- A l'édition d'un utilisateur, son mot de passe n'est plus écrasé #1725

## Ajouts

- Permettre d'utiliser des jetons d'authentification pour utiliser l'API #1572
- Connecteur Vitam #1251
- Contrôle sur les identifiants du connecteur Transformation #1426
- Onglet de visualisation des transformations après l'étape #1426
- Choix de l'algorithme de calcul d'empreinte des fichiers pour les connecteurs utilisant le générateur SEDA #1668
- Création d'un connecteur de générateur SEDA 2.2 pour Asalae #1634
- Ajout d'un droit de création d'utilisateurs pour différencier de l'édition #1156
- Ajout de la commande ```app:module:force-send-ged-and-delete [-i|--includeSubEntities] [--dry-run] [--] [<sourceModule> [<entityId>]]``` #1562
- Valeur par défaut disponible (texte, textarea, checkbox et liste déroulante) dans les flux studio #1065
- Archives composites sur le connecteur de génération SEDA Asalae 2.1 #1753
- Ajout du Pack RH #1773

## Évolutions

- Les extensions doivent charger leurs fichiers PHP dans un fichier `autoload.php` à la racine de l'application ou via
  composer avec un fichier `vendor/autoload.php`
- Le champ SIREN n'est plus obligatoire sur la définition d'une entité #1009
- La constante PASSWORD_MIN_ENTROPY passe par défaut à 80 suivant les recommandations de l'ANSSI
- Studio: La génération du bordereau et de l'archive est indépendant de l'envoi au SAE grâce à un nouvel état
  intermédiaire : "Paquet d'archive (SIP) généré" #1357
- La bibliothèque de lecture YAML Spyc est remplacée par la bibliothèque Symfony YAML plus performante.
  Le script console dev:fix-yaml permet de modifier ce qui était permis (de manière incorrect) par Spyc #1515
- Possibilité de supprimer ou de désactiver un utilisateur #112
- Ajout du connecteur help-url qui permet de définir l'URL dune page d'aide #1547
- Modification du vocabulaire concernant les transferts au SAE #1552
- Possibilité de revenir en arrière sur les flux studio sur les étapes non réalisées #1539
- Possibilité de retamponner les actes et les annexes (flux studio, actes generique et automatique ) #627
- SAEConnecteur::sendArchive() devient SAEConnecteur:sendSIP()
- Amélioration de la présentation de l'association des connecteurs #1607
- Possibilité d'ajouter un fichier database.json dans les extensions pour ajouter des tables sur la base de données Pastell (voir le format sur installation/pastell.json). #1557
- Lors de la création d'un administrateur, le mot de passe est directement généré par Pastell ce qui garanti un mot de passe fort
- La base de données par défaut est mariadb version 10.9.3
- Passage du jeu de caractère par défaut de la base de données à utf8mb4 (possibilité de mettre des caractères UTF8 étendus)
- Actes et Helios automatique: Lorsque le traitement est terminé, il est possible de modifier le document pour cocher la case envoi_sae et faire l'action "Verser au SAE" (c'était déjà possible via le connecteur de purge) #1627
- Vérification de la force du mot de passe sur les requêtes API de type POST ou PATCH
- Un mot de passe est généré automatiquement pour l'exportation d'un connecteur #1597
- Un mot de passe est généré automatiquement pour l'exportation de la configuration #1705
- La fréquence des connecteurs passe à 10 minutes par défaut #1633
- Possibilité de mettre un filtre sur 'is_active' dans la recherche des entités sur l'API #1648
- Possibilité d'activer et de désactiver un compte utilisateur par API #1536
- Possibilité d'activer et de désactiver une entité par API #1624
- Possibilité de modifier un flux studio en paramétrant l'état des dossiers associés en erreur fatale #1007
- Filtre sur les types de fichiers lors de l'upload directement dans le formulaire #1654
- Le dernier message des travaux programmés est visible et leur ID mène vers leur détail #1676
- Les droits entite:lecture et journal:lecture sont cochés par défaut à la création d'un rôle #1580
- Permettre `generate-sip` en état `erreur-envoie-sae` #1709
- Les champs de type date ne sont plus initialisés par défaut à la date du jour #1692
- Le type de dossier sur le connecteur Glaneur est sélectionnable via une liste déroulante #1698
- Les fichiers de classe n'ont plus l'extension .class #1743
- A la création d'une notification, l'utilisateur est redirigé vers sa configuration #1610
- Les éléments texte (multi-ligne) sont disponibles à la sélection de l'objet pour l'envoi à la préfecture dans le studio #1690
- Les ancêtres d'une entité sont indiqués dans les rôles de la liste des utilisateurs et les ancêtres restent visibles
  dans la recherche d'une entité pour l'ajout d'un rôle à un utilisateur #1680
- La possibilité de se connecter via un certificat X509 client est une fonctionnalité activable désactivée par défaut

## Suppressions

- Suppression des méthodes magiques `__get()` et `__set()` sur la class `ObjectInstancier`, il convient d'utiliser respectivement
  `getInstance()` et `setInstance()`
- Suppression de la directive de configuration LIBERSIGN_INSTALLER, Libersign est fourni via un container docker
- Suppression de documentations et de scripts obsolètes concernant les versions de Pastell inférieures à 4.0.0
- Suppression du chargement automatique des fichiers PHP des extensions
- Suppression du endpoint divers/receive-ocre.php et des constantes OCRE_RECEIVE_PASSPHRASE et OCRE_INPUT_DIRECTORY
- Suppression de la constante NB_ENTITE_BEFORE_COLLAPSE
- Suppression de l'authentification par certificat au services Chorus Pro #1453
- Suppression de la constante CONNECTEUR_DROIT. Il faut maintenant ajouter les droits 'connecteur:lecture' et 'connecteur:edition'
  afin de gérer les connecteurs et les associations de types de documents!! #1136
- Suppression de l'authentification OASIS/OPENID #1459
- Suppression de LastUpstart UPSTART_TIME_SEND_WARNING - Suppression de `batch/action-automatique.php` et `installation/pastell-upstart.conf` #1461
- Suppression de la classe SSH2 et de la dépendance à l'extension php ssh2
- Suppression des fonctions GEDConnecteur::{send, sendDonneesFormulaire, createFolder, addDocument, getRootFolder, listFolder, getSanitizedFolder, getSanitizedFilename, forceAddDocument, forceCreateFolder}
- Suppression de la fonction PastellTestCase::loadExtension
- Suppression du script create-default-horodatage.php
- Suppression des classes PDFGeneriqueSendGED, PDFGeneriqueSAEVerif, PDFGeneriqueSAEValidation,
  PDFGeneriqueReceptionIParapheur, PDFGeneriqueEnvoieIParapheur, EnvoieSAEChange, IParapheurEnvoie, SAEVerif,
  TedetisAnnulation, TedetisRecupAnnulation, TedetisSendReponsePref, TedetisVerifReponsePref,
  FournisseurCommandeEnvoiGED, FournisseurCommandeEnvoiSAE, GEDEnvoiDocumentASigner, IParapheurEnvoieDocumentASigner,
  IParapheurRecupDocumentASigner, HeliosEnvoieSAEChange, HeliosGEDEnvoi, HeliosGeneriqueSAEValidation,
  HeliosGeneriqueSAEVerif, IparapheurEnvoieHelios, PDFGeneriqueRelance, TedetisRecup
- Suppression de la propriété Controler::lastError
- La récupération des acquittements du flux pes-marché est standardisé avec les autres flux (modification du nom du fichier de l'acquittement)
- Suppression de la constante MODE_MUTUALISE. Lors de l'envoi d'un mail sécurisé, mailsec_from prend la valeur de PLATEFORME_MAIL.
  Il faut lancer la commande `app:force-update-field connector mailsec mailsec_reply_to "{% if mailsec_reply_to == '' %}{{mailsec_from}}{% else %}{{mailsec_reply_to}}{% endif %}"`
  pour reporter l'ancien mailsec_from à mailsec_reply_to (s'il n'est pas déjà renseigné) #1465
- Suppression de la fonction Controler::exitToIndex
- Suppression des méthodes magiques ChoiceActionExecutor::__set, Gabarit::__set
- Suppression des scripts permettant une installation hors d'un environnement Docker.
  En particulier les scripts de création de connecteurs initiaux et de fréquences sont gérés par la séquence
  d'initialisation du conteneur.
- Suppression du modèle `Document`, remplacé par `DocumentSQL`
- Suppression du modèle `Utilisateur`, remplacé par `UtilisateurSQL`
- Suppression du modèle `Entite`, remplacé par `EntiteSQL`
  (suppression du type d'entité "service" et des classes dépréciées AccuserReception, Envoyer)
- Suppression des fonctions :
  - SAEConnecteur::getAcuseReception
  - SAEConnecteur::getReply
  - SAEConnecteur::getLastErrorCode
  - SAEConnecteur::generateArchive
  - SAEConnecteur::getTransferId
  - SAEConnecteur::getURL
  - SAEConnecteur::getErrorString
- Suppression de TdtConnecteur::postHelios(), remplacé par TdtConnecteur::sendHelios()
- Suppression de la méthode magique `Controler::__get`
- Suppression de TdtConnecteur::postActes(), remplacé par TdtConnecteur::sendActes()
- Suppression de SignatureConnecteur::sendDocument() et SignatureConnecteur::sendHeliosDocument(), remplacés par
  SignatureConnecteur::sendDossier()
- Suppression de la classe d'action `FournisseurCommandeEnvoieIparapheur`
- L'envoi au iparapheur du flux Commande générique est standardisé avec les autres flux
- Suppression de la plupart des scripts dans batch/ installation/ et script/. Ceux qui restent ne devrait plus être utilisés car ils doivent être remplacés par un script console
- La méthode SEDAConnecteur::getBordereau() prend la signature de SEDAConnecteur::getBordereauNG() qui est supprimée
- Suppression de DAEMON_USER, DAEMON_LOG_FILE, PID_FILE
- Suppression de la fonction util.php::mail_wrapper(), utilisé symfony/mailer à la place
- Suppression du fichier de sous-type qui ne servait plus dans le connecteur i-Parapheur #1505
- Suppression de lib/ZenMail, utilisé symfony/mailer à la place
- Suppression de SMTP_SERVER, SMTP_PORT, SMTP_USER, SMTP_PASSWORD, utilisé MAILER_DSN à la place. Voir https://symfony.com/doc/current/mailer.html#using-built-in-transports
- Suppression du service versant dans le connecteur asalae #1504
- Suppression du certificat utilisateur sur le connecteur i-Parapheur ; Suppression du connecteur global i-Parapheur #1476
- Suppression de AIDE_URL remplacé par le connecteur help-url
- Suppression de Visionneuse.php, les visionneuses doivent désormais implémenter l'interface Viewer
  - XMLVisionneuse devient Pastell\Viewer\XmlViewer
  - TypologieActesVisionneuse devient Pastell\Viewer\TypologyActesViewer
  - RawVisionneuse devient Pastell\Viewer\RawViewer
  - ReponsePrefectureLinkVisionneuse devient Pastell\Viewer\ReponsePrefectureLinkViewer
  - GedIdDocumentsVisionneuse devient Pastell\Viewer\GedIdDocumentsViewer
- Suppression de la fonction SEDAConnecteur::getInfoARActes()
- Suppression du script script/plateform-update/database-update.php, remplacé par un script console app:database:update
- Suppression du script installation/filedbupdate.php, remplacé par un script console dev:database:update-definition
- Suppression de la constante DATABASE_FILE
- Suppression de la possibilité de mettre la règle 'role_id_e: no-role' sur les connecteurs. Il faut remplacer par la règle 'automatique: true' pour obtenir le même comportement
- Suppression des éléments relatifs au centre de gestion sur la version standard. Il est possible de les remettre avec la feature flag CDGFeature.
- Suppression de l'URL dans l'onglet de retour du SAE
- Suppression du support MySQL. La seule base supportée est MariaDB.
- Suppression de la création d'une entité par API via PATCH avec le paramètre 'create' et suppression du test 'testCreateWithEditAction()'
- Suppression de la classe `UtilisateurCreator`, remplacée par `UserCreationService`
- Suppression de la constante AUTHENTICATION_WITH_CLIENT_CERTIFICATE remplacé par la constante TOGGLE_CertificateAuthentication

## Limitations

- Les dates sont enregistrées en heure locale (TIMEZONE), de fait, il n'est pas possible de changer de fuseau horaire après une installation initiale
- Le connecteur *Récupération dossiers iparapheur* ne fonctionne qu'avec la version 5.0.7 d'iparapheur.

## Dépréciations

- Le support de la version 0.2 du SEDA est déprécié. 

# [3.1.20] - 2023-07-27

## Corrections

- Statut Facture Chorus Pro: le statut ```04 : Rejetée par lordonnateur pour erreur données acheminement``` correspond au statut A_RECYCLER #1866
- Modification de statut CPP: Le motif du statut cible était mal encodé #1863

# [3.1.19] - 2023-02-06

***Cette version nécessite une modification de la base de données***

## Corrections

- Correction du contrôle lors de la création d'un type de dossier #1675
- Il n'était pas possible de télécharger tous les fichiers d'une réponse mail #1703
- Il y a eu une regression lors de la correction en 3.1.9 #1365, on perdait les informations du mail sécurisé en passage en non-recu #1646
- OIDC : Le client_id n'est plus envoyé dans le body lors de l'utilisation de l'authentification basic #1741

## Ajouts

- CPP: Ajout d'un contrôle sur le token PISTE en cas de retour vide #1682
- Prise en compte des constantes HTTP_PROXY_URL et NO_PROXY pour le connecteur depot-cmis #1671

# [3.1.18] - 2022-11-21

## Corrections

- Authentification OpenID Connect: L'URL de redirection après connexion est désormais fixe : https://pastell.tld/Connexion/oidc #1653

# [3.1.17] - 2022-09-12

## Corrections

- Le glaneur pouvait se verrouiller et bloquer sur le même fichier lorsque le fichier n'est pas valide #1568

## Ajouts

- CPP: Permettre de choisir les factures à récupérer et synchroniser selon leur état "Téléchargé" sur Chorus #1438

## Évolutions

- Le composant PES viewer passe en version 1.5.1 et les PJ sont enregistrées dans le répertoire `/data/pesPJ` #1551

# [3.1.16] - 2022-07-18

***Cette version nécessite une mise à jour du générateur SEDA en 0.8.3***

## Corrections

- Il manquait la récupération de l'identifiant de l'archive sur le SAE dans le cas du SEDA 2.1 #1518
- Il manquait le connecteur CPP pour les fréquences par défaut #1540
- Refactoring de ConnecteurFactory::getManquant() #1485

## Ajouts

- Générateur SEDA: ajout des balises OriginatingAgencyArchiveIdentifier et TransferringAgencyArchiveIdentifier #1463
- Ajout des flux "Actes publication" `ls-actes-publication` et "Document PDF" `ls-document-pdf` #1517

## Évolutions

- Permettre de définir les fréquences en secondes #1553

# [3.1.15] - 2022-06-13

***Cette version nécessite une modification de la base de données***

## Corrections

- Correction du workflow du flux `facture-cpp` en mode automatique #1502
- Il n'y avait plus d'accès au fichier réponse d'un mail sécurisé sans les droits NOM_DU_FLUX-reponse,
  c'est rétablit avec les droits NOM_DU_FLUX #1486
- Généralisation de la correction "Il n'est plus possible pour un destinataire de visualiser un mail sécurisé si celui-ci est passé dans l'état "Non reçu" #1365

## Ajouts

- Ajout de la date de réponse à un Mail sécurisé #1535
- Ajout de la taille totale dans le connecteur de statistiques #1534

# [3.1.14] - 2022-05-16

## Corrections

- Il manquait des cas d'erreur pour la mise en état 'create-statut-facture-cpp-error' du flux `statut-facture-cpp` #1506

## Évolutions

- L'entropie minimum du mot de passe (PASSWORD_MIN_ENTROPY) est fixée à 80 par défaut #1499
- Connecteur S2low, Récupérer les réponses de la préfecture: 
  Si un document acte avec `acte_unique_id` correspondant à la réponse de la préfécture `unique_id` est présent sur Pastell
  et que le connecteur s2low est associé au flux de cet acte d'origine 
  alors le document `actes-reponse-prefecture` est créé sur la même entité que cet acte (Sinon, c'est sur l'entité du connecteur S2low) #1513

# [3.1.13] - 2022-04-19

## Corrections

- Actes générique/automatique : Si l'étape de signature est faite avec un connecteur parapheur fast,
  le document envoyé n'est pas celui signé mais celui d'origine #1482
- l'API POST /entite/:id_e/flux/:flux/connecteur/:id_ce?type=:type renvoyait un int depuis la version 3.1.9.
  C'est maintenant rétablit avec un string #1479
- Il manquait le contrôle de la force de mot de passe pour la page "Mot de passe oublié" #1478

## Évolutions

- Pack Chorus Pro: Prise en compte du nouveau schéma de données CPPFacturePivot_V2_02.xsd #1494
- Le glaneur permet l'extraction des données d'un PES avec le flux Hélios automatique en appelant l'action importation #711
- Il faut lancer la commande `app:module:helios-add-extraction-pes-aller` suivie de `helios-automatique` ou `helios-generique`
  pour extraire les informations des fichier_pes (dans le cas où elles seraient manquantes) #1484

# [3.1.12] - 2022-02-16

***Cette version nécessite une mise à jour du générateur SEDA en 0.8.2***
- Mise à jour des dépendances. Twig security release v3.3.8

## Corrections

- Une action synchrone sur un dossier peut faire planter une action automatique #1468

# [3.1.11] - 2022-02-14

***Cette version nécessite une mise à jour du générateur SEDA en 0.8.1***

## Ajouts

- Commande `app:module:actes-add-type-piece-fichier` permettant de construire type_piece_fichier avec type_acte et type_pj #1174
- Un indicateur permet de visualiser la force des mots de passe saisie lorsqu'on essaye de modifier le sien. #1421
- Possibilité de fixer une entropie de mot de passe minimum au niveau de la plateforme PASSWORD_MIN_ENTROPY.
  Celle-ci est à 0 par défaut pour assurer la compatibilité. #1421
- Ajout du parametre periodeDateHeureEtatCourantAu pour la récupération des factures Chorus (Factures ayant changé de statut avant les X derniers jours, jusqu'à aujourd'hui si non renseigné) #1444

## Corrections

- Les rejets parapheur à l'étape mail sécurisé parapheur n'étaient pas pris en compte #1227
- Amélioration de l'affichage des erreurs des templates twig #1434
- Complexification du token servant au changement de mot de passe lors d'un oubli de celui-ci #1441
- Utilisation de la version 0.8.1 du generateur SEDA afin de supprimer la balise Agency des bordereaux SEDA 2.1 (qui n'existe pas pour asalae) #1450
- La directive NO_PROXY n'était pas prise en compte lors de l'intialisation des requêtes SOAP, entrainant un dysfonctionnement dans certain cas. #1454
- L'API v2 pouvait renvoyer une erreur si l'identifiant du dossier terminait par `php` #1460

## Évolutions

- L'accusé de reception technique devient facultatif pour l'envoi au SAE sur les flux actes-generique et actes-automatiques #1435
- Le nombre de connexions échouées par minute est limité à 5 tentatives infructueuses #1423
- Les flux GFC ne font plus partie du pack GFC et sont directement accessibles dans l'interface #1446
- L'appel pour le test de connectivité Chorus se fait maintenant par "transverses/v1/recuperer/tauxtva" #1211

# [3.1.10] - 2022-01-10

## Ajouts

- Commande `app:connector:delete-by-type` permettant de supprimer tous les connecteurs d'un type #1432

## Corrections

- L'utilisation d'un groupe global lors de l'envoi d'un mail sécurisé était considéré comme invalide #1133
- la reprise d'historique d'actes antérieurs à la réforme actes2 provoquait une erreur si le flux actes-automatiques n'était pas associé à de connecteur bouchon TdT #1290

## Dépréciations

- script `installation/force-delete-connecteur.php` au profit de la commande `app:connector:delete-by-type` #1432

# [3.1.9] - 2021-12-13

***Cette version nécessite une modification de la base de données***
***Cette version nécessite une mise à jour du générateur SEDA***

## Corrections

- Il n'est plus possible pour un destinataire de visualiser un mail sécurisé si celui-ci est passé dans l'état "Non reçu"
  (uniquement sur les étapes "mail sécurisé" des flux studios) #1365
- Utilisation de la version 0.7.1 du generateur SEDA afin de positionner correctement les méta-données de gestion du SEDA 2.1 #1389
- Les expressions twig renvoyant vide sur les AppraisalRules et AccessRestrictionRule du connecteur generateur SEDA provoquaient une erreur #1410
- Il manquait la métadonnée "has_signature" sur les flux actes et hélios après récupération d'une signature via le parapheur Fast #1418
- Le token permettant la modification de son mot de passe via son email expire au bout de 30 minutes #1422

## Évolutions

- Possibilité d'ajouter des méta-données des entités, utilisateurs et dossiers dans le connecteur de transformation #1397

## Ajouts

- Ajouts pour permettre les fonctionnalités optionnelles (features flag)
- États pour les connecteurs (~ journalisation de la modification et association/dissociation de connecteur) #1346
- Ajout du pack GFC #1427

# [3.1.8] - 2021-11-08

***Cette version nécessite une modification de la base de données***

## Ajouts

- Ajout des états pour les connecteurs (~ journalisation de la création de connecteur) #1346
- Ajout du Pack urbanisme #1405

## Évolutions

- cloudooo devient un service docker
- il est à nouveau permis de créer des actes avec un numéro d'acte à un seul caractère #1386
- Possibilité pour le bouchon SAE de générer une erreur lors de la récupération de l'ACK #1359
- Il est possible de déposer en GED en utilisant l'identifiant du document Pastell comme nom de répertoire #1388
- Il est possible de configurer le connecteur Fast parapheur pour ne pas supprimer le dossier sur le parapheur après un refus #1390
- Simplification de l'utilisation du docker pes-viewer et mise à jour de celui-ci #1371 #1373
- ajout de la commande `app:studio:make-module` 

## Corrections

- La classification issue du connecteur Fast TdT était encodée en UTF8 #1381
- Fast Parapheur: Le répertoire temporaire n'était pas supprimé correctement lors de l'utilisation du circuit à la volée
avec des annexes #1394
- Le message d'aide de saisie du nom d'une entité était incorrecte (128 caractères maximum au lieu de 60) #1378  
- Correction d'erreur 500 sur le connecteur LDAP en cas de mauvaise configuration #1380
- Le répertoire temporaire n'était pas nettoyé lors d'une erreur lors de la génération d'une archive #1358
- Le dossier ne passait pas en état `erreur-envoie-sae` lors d'une erreur pendant l'envoi sur Asalae #1355
- Les fichiers ZIP ne permettaient pas la génération correcte de bordereau SEDA en version 2.1 #1395
- Il n'était pas possible de mettre la typologie par API, glaneur ou connecteur de transformation sur un flux studio #1407

## Suppression

- Suppression de la configuration de l'url du connecteur pes-viewer

# [3.1.7] - 2021-10-11

## Corrections

- si le système de fichier /var/www est configuré à noexec, il n'était pas possible d'installer l'horodateur #1361
- la récupération de Libersign ne passait pas par le proxy #1361
- le bouton créer n'apparaissait pas pour les utilisateurs sans droit sur l'entité racine #1372

## Ajout

- Ajout d'un connecteur de purge globale uniquement pour la suppression en masse de vieux documents

## Évolution

- Possibilité de générer plusieurs mots-clés sur une seule ligne avec Twig #1360

# [3.1.6] - 2021-09-06

***Cette version nécessite une modification de la base de données***
***Cette version nécessite la version 0.6.0 du générateur SEDA***

## Évolutions

- Possibilité de mettre une liste de content-type sur les éléments de type file dans la définition d'un formulaire d'un flux
- Possibilité d'ajouter un contrôle sur le type de fichier sur les formulaires des flux studio #1235

## Corrections

- Correction du script installation/update-all-connecteur-field-value.php qui n'était pas opérationnel #1026
- Précisions dans le formulaire des données du générateur SEDA (sort final => DUA) #1300
- Ajout de commentaire dans le formulaire des données du générateur SEDA (correspondance SEDA) #1303 
- la directive content-type n'était prise en compte que pour le premier fichier des éléments de type file dans la définition d'un formulaire d'un flux 
- Aller sur la liste des dossiers d'un flux sur l'entité racine générait une requête en base de données inutile #1347 

## Ajouts

- Un connecteur global de statistiques pour déterminer le nombre de dossiers créés sur une période ainsi que l'espace utilisé #1342
- Commande `app:module:copy-associations`: Permet de copier les associations d'un flux vers un autre
- Commande `app:connector:replace-ged-ssh-with-depot-sftp`: Permet de remplacer les connecteurs `ged-ssh` par des connecteurs `depot-sftp`
- Ajout d'un connecteur global generateur SEDA afin de ne positionner l'URL de l'outil qu'une seule fois #1320 
- Ajout du `ServiceLevel` (Niveau de service demandé) dans les bordereaux de transfert du générateur SEDA #1344
- Colonne qui récapitule le nombre de mails sécurisés envoyés, lus et répondus sur la liste des flux "Mail sécurisés" et "Mail sécurisés avec réponse" #1345
- Ajout des états pour type de dossier #1246

# [3.1.5] - 2021-08-04

***Cette version nécessite une modification de la base de données***

## Évolutions

- Permettre la taille de l'identifiant d'un type de dossier studio à 32 caractères suivis de -destinataire ou -reponse #1331
- Connecteur generateur seda : ajout de la fonction xpath_array permettant de récupérer des listes issues des expressions xpath #1315
- Ajout du filtre ls_unique permettant de dédoublonner les tableaux sortant de commande xpath_array dans les expressions twig #1335 

## Corrections

- Connecteur générateur SEDA : Les propriétés spécifiques de l'unité d'archivage utilisée dans le cadre d'un ZIP ne permettait pas une utilisation complète des expressions XPATH #1340
- Les versements en SEDA 2.1 n'étaient pas fonctionnels sur la récupération des ACK et des ATR #1334
- Suppression du double encodage des caractères (apostrophe notamment) lors de l'utilisation du connecteur générateur SEDA #1323
- Les caractères spéciaux sur un champ de type "liste déroulante" tronquent la valeur #1333

## Ajout

- ajout des fonctions abstraites SAEConnecteur::getAck et SAEConnecteur::getAtr sur la classe abstraite SAEConnecteur

## Suppression

- Le service versant est devenu inutile sur le connecteur as@lae (Pastell utilise directement le bordereau de transfert pour le retrouver) #468

# [3.1.4] - 2021-07-05

## Corrections

- Le breadcrumb pouvait faire planter le navigateur s'il y avait trop d'entités #1321
- Correction d'un appel déprécié dans FluxDataSedaHelios #1308
- Connecteur de transformation #1318:
    - N'exécuter que les onchange des champs modifiés
    - Passage en état `transformation-error` si le dossier est invalidé par la transformation
- Fast TdT : Permettre la récupération d'un acquittement PES lorsque le dernier état est "Classé" ou "Archivé" #1325
- Fast Parapheur : Permettre la récupération de la signature lorsque le dernier état est "Signé" ou "Archivé" #1325
- Fast parapheur : L'état "Visa désapprouvé" n'était pas considéré comme un état rejeté #1327
- Le connecteur cloudooo n'était plus fonctionnel suite à un bug dans une bibliothèque externe #1332

## Évolutions

- Connecteur generateur-seda: ajout du choix "Ne pas inclure le MimeType lors de la création du bordereau" pour les fichiers (nécessite générateur SEDA v>0.5.0) #1305
- Le test de connexion du connecteur generateur-seda retourne la version (nécessite générateur SEDA v>0.4.0) #1311
- Connecteur de bouchon signature :
  - Ajout d'un type "Custom" pour lequel la liste des sous types est personnalisable
  - Si une signature ou un bordereau est déposé par API avant l'exécution de l'action, les fichiers ne seront pas écrasés

# [3.1.3] - 2021-06-07

## Ajouts

- Ajout de la constante NO_PROXY permettant d'exclure les appels à certains hôtes quand un proxy est utilisé via PROXY_HTTP_URL #1287
- Ajout du test de connexion du connecteur generateur-seda (nécessite générateur SEDA v>0.3.1) #1293
- Visualisation de l'empreinte sha256 des types de dossiers et des connecteurs #1292

## Corrections

- Mail sécurisé #1260:
  - Lors de l'envoi, mailsec_from prend la valeur de PLATEFORME_MAIL en mode saas/mutualisé (MODE_MUTUALISE à true dans le settings, visible dans le test du système)
  - ajout de mailsec_reply_to (lors de l'envoi, prend la valeur de l'emetteur si non renseigné)
- Vérification que les PES Acquit sont bien formés sur le connecteur s2low #1248
- Le script de mise à jour de la base de données échoue si le fichier de définition de la base n'existe pas #690
- Lors du test d'envoi de mail dans le test du système, la redirection était sur une mauvaise page #1289
- Possibilité d'utiliser des espaces de noms dans les expressions XPath (exemple: `{{ xpath( 'aractes' , '/actes:ARActe/@actes:DateReception' ) }}`) #1288

# [3.1.2] - 2021-05-03

## Corrections

- les annexes des actes n'étaient pas envoyées dans le bon ordre à s2low lorsqu'il y avait plusieurs fichiers avec le même nom #1271
- Ajout d'un test lors du retour des annexes tamponnées sur s2low pour vérifier qu'elles sont dans le bon ordre #1242
- Lorsqu'un utilisateur n'avait pas de droit entite:lecture, il pouvait récupérer la liste des entités de premier niveau #1272
- La récupération du multi-document i-Parapheur était incomplète s'il n'y avait qu'un document supplémentaire #1273
- Ajout d'un commentaire sur les champs to, cc et bcc des types de dossier contenant du mail sécurisé (mailsec, mailsec-bidir et studio) #1219
- Les mails n'étaient pas toujours reçus si le nom du destinataire était accentué #1274
- La récupération de la signature d'un document venant du connecteur Fast Parapheur entraînait une erreur #1275
- Les connecteurs dépréciés parametrage-flux-doc et parametrage-flux-pdf-generique sont supprimés du coeur Pastell #1023
  - (Si ces connecteurs étaient associés à des types de dossier et qu'ils apparaissent comme manquants, les commandes `/installation/force-delete-connecteur.php parametrage-flux-doc` et `/installation/force-delete-connecteur.php parametrage-flux-pdf-generique` permettent de les supprimer)
- Afficher la visionneuse avec le droit de lecture #1261
- Sur le générateur SEDA, il n'était pas possible de mettre des virgules sur les mots-clés (rendant entre autre impossible l'utilisation de XPath sur les mots-clés) #1267  
- Modification de la taille des champs sur les formulaires de document et de connecteur, ainsi que sur les spécificités des connecteurs de transformation et génération SEDA #1230
- Correction du script de vidange du journal (vider-journal-to-historique.php) pour que celui-ci n'échoue plus en cas d'arrêt brutal de la base de données. #1134

# [3.1.1] - 2021-04-06

## Évolutions

- Le libellé de l'état `document-transmis-tdt` pour les actes est désormais "En attente du certificat RGS**" #1226
- Passage en état `send-signature-error` s'il y a une erreur lors de l'envoi d'un dossier au parapheur FAST (flux studio et actes/helios) #967
- Ajout d'un bouton dans le test du système permettant de vider le cache Redis #1257
- Ajout des informations facultatives lors de l'utilisation du circuit à la volée pour signature avec le connecteur fast-parapheur #1259
- Connecteur i-Parapheur: Ajout du choix "Appliquer le multi-document". Si le sous-type i-Parapheur le permet (Signature multi-document, 6 par défaut), alors les autres documents envoyés seront des multi-documents signés #1032
  - Implémenté pour les types de dossier studio, document-a-signer, pdf-generique, facture-cpp, piece-marche
  
## Corrections

- Type de dossier mailsec et mailsec-bidir: La suppression est maintenant possible en état reception ou non-recu #1141
- Permettre d'utiliser l'étape transformation avec le glaneur #1253
- Générateur SEDA : c'est bien le nom de l'unité d'archivage qu'il est possible de configurer directement dans la liste des unités d'archivage #1254
- Générateur SEDA : Ajout de la description dans les unités d'archivage (nécessite générateur SEDA v>0.3.0)
- Ordre des onglets dans `pdf-generique` : L'onglet `signature` vient après l'onglet `parapheur` et non à la fin #1217
- L'utilisation de deux transformations dans un type de dossier studio provoquait un warning #1262
- Lors d'une transformation, le titre n'était pas mis à jour s'il était modifié par la transformation #1263
- Toutes les erreurs du PES acquit n'étaient pas affichés dans la visionneuse #873

# [3.1.0] - 2021-03-01

***Cette version nécessite de supprimer pastell-marche et pastell-chorus-pro de la liste des extensions (le cas échéant, activer les packs dans le settings)***

## Évolutions

- Intégration du pack_marche (pastell-marche 3.0.2) #1056
- Intégration du pack_chorus_pro (pastell-chorus-pro 3.1.3) #1169
- Ajout d'une étape studio de transformation (création de meta-données ou de fichiers supplémentaires)
- Ajout d'un connecteur de transformation générique, permettant d'utiliser du Twig, du XPath, du jsonpath ainsi que du parcours CSV pour créer de nouvelles métadonnées #994
- Changement de licence vers AGPL v3 #1132
- Utilisation du nouveau logo et favicon pastell #1077
- Permettre le changement d'entité de même niveau sans repasser par l'entité racine #1072 
- Les exports de connecteurs sont désormais sécurisés par un mot de passe #310
- Possibilité de s'abonner à la notification des réponses sur les flux construits autour du mail sécurisé.
- Possibilité de corriger les problèmes d'encodage des caractères sur la génération SEDA dans les fichiers d'archive via une expression régulière sur le connecteur SEDA NG #720
- Pose d'un verrou sur l'exécution des actions des connecteurs et des documents #676  
- Ajout du connecteur Générateur SEDA (utilisation de twig dans le bordereau, version 1.0 et 2.1 du SEDA) #946
- Les liens des champs textarea sont désormais cliquable #1202
- Il est possible de cocher une étape d'un flux studio par défaut sans pour autant la rendre obligatoire #1201
- Permettre de donner un libellé aux étapes des flux studios #1170
- L'API de récupération du détail d'un document permet de récupérer les informations sur les mails sécurisés envoyés ainsi que les réponses dans le cadre du mail sécurisé bi-directionnel #1223
- Ajout de la commande `bin/console app:system:healthcheck` qui permet de récupérer le test du système #1244

## Corrections

- Il pouvait y avoir une erreur lors de la génération d'un archive avec plusieurs milliers de fichiers #1225
- Le dernier message du parapheur ne s'affiche pas lorsqu'il y a plusieurs étapes de signature #1231
- La génération d'un bordereau SEDA avec la commande `extract_zip` ne purgeait pas correctement la copie temporaire des documents #1236
- Le total des fichiers "Acte" et "Autre document attaché " `actes-generique` et `actes-automatique` est maintenant de 157286400 octets (150 * 1024 * 1024) #1240

# [3.0.13] - 2021-02-01

## Correction

- Affichage de certaines erreurs du connecteur fast-parapheur
- Lors du passage en version 3 le DossierID n'était pas retrouvé sur le i-parapheur pour document-a-signer #1208
- fast-parapheur: L'utilisation du circuit à la volée n'était plus possible à cause d'un changement de spécification #1212
- Renommer un id de type de dossier ne fonctionnait pas #1175
- default peut prendre la valeur "empty" pour spécifier de ne pas renseigner une date (sinon now par défaut) #1152
- ajout de la classe SignatureRemord (pas encore implémenté pour les types de dossiers) #1218
- Suppression de la redirection des appels API en passant par CAS #1171

# [3.0.12] - 2020-12-07

## Correction

- La vérification des tables crashées était trop gourmande en ressources et a donc été corrigée #1163
- Lors de certains appels API, les permissions "entite:xxx" étaient encore vérifiées alors que la constante "CONNECTEUR_DROIT" était renseignée #1139
- Suppression de la vérification de la typologie dans le cas où il n'y a pas de TDT quand on fait le l'acte (générique, automatique et studio) (appel API uniquement) #1150

## Évolutions

- Mise à jour des fréquences par défaut à l'installation #1164

# [3.0.11] - 2020-11-02

## Ajout

- Ajout de la vérification des tables marquées comme craché dans la page de vérification du système

## Correction

- Le dernier état d'un document pouvait être incorrect à cause d'un problème de date #1105

# [3.0.10] - 2020-10-05

## Ajout

- La commande `bin/console  app:truncate-journal-historique` permet de supprimer le contenu de la table journal_historique #1130 

## Correction

- Ajout des vérifications sur la sécurité des cookies dans les tests de l'environnement système #1137
- Helios automatique: Lorsque le traitement est terminé, il est possible d'utiliser le connecteur de purge pour cocher la case envoi_sae et programmer l'action "Verser au SAE" #1140

# [3.0.9] - 2020-09-07

## Correction

- Erreur lors de la création d'un flux studio quand le titre est positionné sur un fichier #1096

## Évolutions

- Permettre l'utilisation d'un proxy authentifié sur la constante HTTP_PROXY_URL #1107


# [3.0.8] - 2020-08-03

## Correction

- Studio : Les dossiers en état "Signature refusée" ou "Archive rejetée par le SAE" ne pouvaient pas être supprimés #1115
- Conservation de l'url de redirection lors de l'authentification OIDC #1116

# [3.0.7] - 2020-07-06

## Correction

- L'étape de "Vérification du statut de la transaction" n'était pas automatisée suite au retour sur Pastell après envoi de
 la transaction par rebond sur s2low (mode "Utiliser l'authentification par certificat sur S2low pour la télétransmission") #1109
- Chaque action sur un document pouvait verrouiller le job si une action automatique était en cours #1110

## Ajout

- Possibilité (limité) d'envoyer des bordereaux SEDA 2.1

## Évolutions

- Le caractère `-` des noms de fichier n'est plus remplacé par `_` lors de l'envoi d'un PES au tdt #1111  

# [3.0.6] - 2020-06-01

## Correction

- Il n'était pas possible de supprimer un pdf-generique sur l'état terminé #1092
- Correction des libellés Pastell sur l'envoi TDT via S2low et passage en état return-teletransmission-tdt #1091
- Le glaneur ne passait pas par le onChange de l'action de modification #1093
- Studio: contrôle sur le nom de l'onglet principal #1039
- Il était possible d'importer un type de dossier studio avec un id_type_dossier pastell existant #1069
- La typologie n'était pas supprimée lors d'un changement de nature sur les actes studio et actes-automatique #1097
- La relance de Mail sécurisé sur les types de dossier studio se faisait toutes les minutes #1099
- L'onglet de signature n'était pas affiché si aucun connecteur n'était associé (studio) #1024
- Generation bordereau SEDA: par défaut renseigner la valeur (et non la clé) des champs select #1086

## Ajout

- Journalisation des modifications des types de dossier studio (Refactoring des services TypeDossier) #1006
- Il manquait les commentaires des valeurs par défaut du connecteur pdf-relance #1029

# [3.0.5] - 2020-05-04

## Correction

- Libersign ne fonctionnait pas correctement avec les certificats présentant un accent dans leur CN.
- Les flux helios-generique et helios-automatique partait en état acquiter-tdt au lieu de info-tdt
- la modification via api de envoi_signature ne permettait pas de selectionner libersign ou fast pour l'envoi en signature (helios-generique et helios-automatique)

## Ajout

- Ajout de la commande `bin/console app:create-pes-viewer-connecteur` permettant de créer automatiquement un connecteur PES Viewer #1058
- Nouvelle URL (/Connexion/sessionLogout) pour déconnecter uniquement la session utilisateur (SSO) #1060
- Ajout du script installation/force-delete-job.php permettant de supprimer tous les jobs d'un même flux 

## Évolutions

- Possibilité de lister et d'exporter l'ensemble des connecteurs manquants #1018  

# [3.0.4] - 2020-04-06

## Corrections

- Le filtre du connecteur LDAP ne fonctionnait plus s'il était entouré de parenthèse #1034
- L'utilisateur n'était pas correctement enregistré dans le journal lors de certain appel API #1014
- Correction d'un dysfonctionnement de la mise à jour des certificats dans les connecteurs globaux si plus de 1000 connecteurs #1025
- On pouvait envoyer plusieurs fichiers sur un champ non-multiple via API #738
- Certain fichier était modifiable (à tort) via l'API #740
- La fonction modif-document.php (api v1) ne permettait plus de modifier un fichier #438 
- Supprimer une entité ou un utilisateur par API génère désormais une entrée dans le journal des événements #972
- Les entrées du journal des événements génèrent désormais une ligne de log de niveau info
- Ajout de logrotate dans le docker #745
- Lors de l'envoi d'un mail sécurisé, on vérifie que la liste des mails destinataire n'est pas vide (possible avec un groupe vide par exemple) #911
- Les fréquences mises lors de l'installation ne correspondaient pas aux recommandations #820
- Si l'on avait un rôle sur l'entité racine et une entité fille, la liste des collectivités ne s'affichait pas correctement dans l'administration #826
- Certain mails (démon, glaneur) dont le sujet était accentué ne respectait pas la RFC 1342 (impactant un nombre limité de serveur SMTP)  #784

## Évolutions

- Connecteur de dépot : permettre de renommer les fichiers via l'utilisation de méta-données du flux #1037
- Ajout de la constante de configuration HTTP_PROXY_URL permettant l'utilisation d'un proxy pour entre autre les connecteurs S2LOW et i-Parapheur #1004

## Ajout

- Ajout de la constante CONNECTEUR_DROIT (par défaut non activé) qui permet d'ajouter les droit 'connecteur:lecture' et 'connecteur:edition' a ajouter dans les rôles afin de gérer les connecteurs et les associations de types de documents. #1055

# [3.0.3] - 2020-03-02

## Corrections

- Les bordereaux SEDA en version 0.2 ne pouvait plus être accepté par Pastell #1030

# [3.0.2] - 2020-02-06

## Ajouts

- Le champ `verrou` dans le connecteur de purge qui permet de lancer les jobs créés avec un verrou spécifique
(à la deuxième tentative le job prend le paramétrage des fréquences) #973
- Ajout d'un connecteur global PES Viewer et utilisation de celui-ci dans les flux helios studio #1013

## Évolutions

- SignatureRecuperation : récupérer les iparapheur_metadata_sortie #971
- Actes automatique : Lorsque le traitement est terminé il est possible d'utiliser le connecteur de purge pour cocher la case envoi_sae et programmer l'action "Verser au SAE" #701
- `fast-parapheur` : Il est possible d'uploader un fichier JSON pour créer un circuit à la volée pour les
types de dossier compatibles #986
- studio : Permettre d'envoyer la valeur du choix dans une liste déroulante #974
- studio : Possibilité de modifier les types de dossier si tous les dossiers sont dans l'état terminé ou bien erreur fatale #985
- Le nom des fichiers téléchargés n'était pas encodé correctement #1015
- studio : Possibilité d'ajouter un droit spécifique pour la télétransmission des actes en préfecture #1012
- Connecteur OIDC: nouveau champ permettant l'utilisation d'un proxy #1021

## Corrections

- Les actions automatiques des documents sont verrouillées s’il n'y a pas de connecteur associé #947
- studio : Le premier élément d'une liste n'était pas pris en compte #951
- La création des `actes-preversement-seda` via un glaneur bloquait lors de la génération des dossiers `actes-automatique` #981
- Il n'était pas possible de charger plusieurs images dans la configuration du mail sécurisé #976
- Recherche avancée : Il n'y avait que les types de dossier génériques qui apparaissaient #983
- Quand une étape Tdt actes et une étape signature étaient obligatoire dans un type de dossier studio,
l'onglet du parapheur n'apparaissait jamais #977
- L'identifiant du bordereau de signature passe de `bordereau` à `bordereau_signature` pour tous les types de dossier
studio, le champ faisait doublon avec le bordereau d'acquittement #987
- studio : Les actes ne pouvaient pas être annulés #988
- La page de changement de mot de passe (avant connexion) n'était pas charté #1002
- Les dossiers rejetés dans le i-Parapheur sur un cachet serveur n'étaient pas correctement traités #1003 
- Le bouchon SAE ne permettait pas le rejet correct d'un transfert #996
- Envoi d'une notification sur l'action à déclencher en cas d'import réussie ou en cas d'échec après l'importation d'un document par le glaneur SFTP #998
- Erreur lors de la création d'une entité avec un siren de 9 caractères non numériques #1005
- Studio : Possibilité de surcharger un champs créé par une étape par un champs du formulaire principal (cela conduisait à un comportement indéfini) #1010
- Correction du lien dans le mail emis suite à l'arrêt du démon. #1019
- L'état send-tdt-erreur d'une étape du studio bloquait le document 

# [3.0.1] - 2019-11-18

## Évolutions

- Glaneur SFTP: Ajout d'une case à cocher "Déclencher l'action d'import réussie même si le dossier n'est pas valide" #950
- Harmonisation de la base de code, passage en [PSR12](https://www.php-fig.org/psr/psr-12/) #863
- Les documents `actes-automatique` générés par `actes-preversement-seda` ont désormais la typologie définie dans l'enveloppe métier ou une typologie par défaut 

## Ajouts

- Intégration de l'extension `pastell-docapost-fast` dans le coeur #945
- Dépendance à l'extension PHP XSLT (pour des développements futurs)
- Ajout d'une expression régulière pour valider le format d'un champ texte dans le studio #949
- Possibilité de se connecter à une instance sentry #888

## Corrections

- Les étapes obligatoires des types de dossier étaient désactivées lors de l'enregistrement de l'onglet cheminement #939
- Ajout des informations de retour lors de l'utilisation du connecteur `depot-pastell` #930
- Les étapes n'étaient pas générées correctement lorsque l'on passait de 1 à 2 ou de 2 à 1 étapes identiques #925
- Autoriser la valeur de la clé `boundary` à ne pas être entourée par des double quotes lors des retour SOAP multi part #948
- Le test du `glaneur-sftp` avec un fichier d'exemple ne fonctionnait pas #718
- L'API ne répondait pas correctement lors de la modification de cheminement sur helios-generique #952  

# [3.0.0] - 2019-10-14

## Évolutions

- Modification des libellés et des icônes des boutons suivant la charte Libriciel #494
- Modification de la présentation de l'en-tête et du pied de page
- Modification de la page de login #488
- Connecteur i-Parapheur : Ajout d'une action pour vider le cache WSDL #464 
- Page d'informations supplémentaire sur les travaux regroupés par verrou et par état #459
- Francisation et homogénéisation des noms des connecteurs bouchon #466
- Il est maintenant nécessaire de saisir les informations complémentaires pour l'envoi direct en GED sans passage par le TDT (actes, helios) #481 #437   
- Les TDT peuvent maintenant utiliser Pastell pour le versement GED   
- Amélioration de la navigation lors de la modification d'un document (onglet, champs de données externes) #136
- Modification des noms de fichier retour du Tdt pour actes #151
- Envoi du nom original du fichier actes au parapheur #133
- Ajout d'un bouton pour télécharger tous les fichiers d'un champ "fichier multiple" en même temps #185
- La caractère de séparation des fichiers exportés est désormais le point-virgule (;) au lieu de la virgule (,) pour être cohérent avec l'import #23 
- Généralisation de la barre de progression sur l'ensemble des téléchargements de fichier (dossier et connecteur) #527
- La propriété visionneuse est disponible sur les connecteurs
- Les bibliothèques javascript JQuery, Jquery-ui sont désormais gérées par composer
- La bibliothèque javascript de gestion des select zelect est remplacé par select2 (et géré par composer)
- Passage à bootstrap 4 (géré par composer)
- L'action onchange est déclenchée également lorsque l'on envoie des données via l'API 
- Optimisation/refactoring de la classe ActionAutomatique afin de supprimer des appels à la base de données inutiles #490
- Optimisation de l'indexation des documents #526
- Passage en PHP 7.2 #630
- Possibilité pour un flux d'utiliser plusieurs fois la même famille de connecteur #16
- Ajout d'une nouvelle propriété num-same-connecteur pour une action dans le fichier YML de définition d'un flux, permettant de spécifier le numéro d'ordre du connecteur parmi plusieurs connecteurs du même type.
- Il est possible de créer une autre classe pour la création des dossiers #699
- Les valeurs par défaut sont affectées à la création du dossier, quelque soit la méthode de création (web, api, glaneur, ...) #699
- Lorsqu'un dépôt (en ged, sur un autre Pastell, ...) échoue, le dossier passe en erreur dans certain cas non récupérables. #702
- Les actions onChange sont réalisées dans la plupart des cas (modification via la console, via l'API, ajout et supression de fichier) #329
- Le fichier PES des types de dossier `helios-generique` et `helios-automatique` est maintenant limité à une taille de 128 MB #809
- Le total des fichiers "Acte" et "Autre document attaché " `actes-generique` et `actes-automatique` est maintenant limité à une taille de 150 MB #809
- Rationnalisation du vocabulaire du gestionnaire de tâches (was: Démon Pastell) #708  
- Affichage du commentaire du SAE sur l'accusé de reception et sur la réponse ainsi que de l'identiant de l'archive #815 
- Connecteur SEDA NG : ajout de la commande size {{pastell:size:id_element_fichier}} permettant d'obtenir la taille en octet #821
- Connecteur SEDA NG : ajout de la commande extract_zip {{pastell:extract_zip:zip_file}} permettant d'ajouter le contenu d'un fichier ZIP dans l'archive #869
- Un nouvel onglet "Retour GED" est disponible après l'envoi en GED avec le connecteur `depot-cmis`, il affiche les identifiants des documents déposés sur la GED #791
- Lors de la création d'un dossier, les valeurs par défaut des champs sont maintenant écrites et plus interprétées #906
- Lors de la création d'un dossier, les actions `onchange` des champs ayant une valeur par défaut sont exécutés #906
- Le cheminement est toujours visible sur les types de dossiers créés par le studio #906

## Ajouts

- Création d'un connecteur Bouchon SEDA #465
- Création d'un connecteur de dépôt Pastell afin de faire des communications "Pastell 2 Pastell" #472
- Ajout de la classe StandardChoiceAction permettant d'utiliser des actions de connecteur type pour les choix externes
- Ajout de la constante RGPD_PAGE_PATH permettant de mettre un fichier markdown contenant la politique vis à vis du RGPD #588
- Api de supression de fichier #329
- Ajout de la propriété `edit-only` afin de permettre l'affichage d'éléments uniquement en mode édition.
- Ajout de la propriété `visionneuse-no-link` afin de permettre de supprimer le lien dans la présentation du dossier
- Ajout de la clé `max_file_size` qui permet de définir la taille maximale d'un fichier #809
- Ajout de la clé `max_multiple_file_size` qui permet de définir la taille maximale de l'ensemble des fichiers d'un champ multiple, cumulable avec `max_file_size` #809
- Ajout de la clé `threshold_size` qui permet de définir la taille limite acceptée de tous les fichiers du dossier cumulés #809
- Ajout de la clé `threshold_fields` qui permet de définir les champs de type `file` qui seront compatabilisés pour le calcul du seuil défini par `threshold_size` #809
- Possibilité de supprimer et exporter un connecteur dont la definition a été retirée #868
- Ajout du flux actes-reponse-prefecture #651
- Ajout du type de dossier mail sécurisé bi-directionnel 

## Corrections

- La propriété read-only ne fonctionnait pas sur les champs de type textarea #492
- Homogénéisation du cheminement d'un acte #155 #178 #174
- Lorsqu'une date n'était pas renseignée, alors on la remplaçait par 01/01/1970. On met désormais le champs à "vide" #278
- Correction d'un problème d'échappement de caractère sur le formulaire d'édition d'une entité #528
- Lorsque le résultat de la synchronisation LDAP ne retourne pas d'utilisateur, on l'indique clairement #518
- Correction d'une fuite mémoire sur les processeurs de logs lors de l'execution d'action sur les connecteurs et les dossiers #555
- php 7.2, file_info renvoi "text/xml" à la place de "application/xml #665
- Les informations sécurisées sur les connecteurs ne sont plus accessibles via l'API #659
- Correction de l'arbre des entités incorrectes dans certain cas #664
- Les fichiers PES Acquit sont nommés correctement en fonction du nom du PES ALLER #750 
- Correction d'un warning dans le bordereau SEDA NG si le content-type d'un fichier était interdit dans le profil #821
- Correction retour d'erreur api lors de la modification des type_pj d'actes #889
- actes, helios: empêcher que le fichier signé porte le même nom que le fichier original #921

## Retraits

- Connecteur TDT : supression du champ "AC du certificat du TDT" #503
- Le type de dossier envoyé au SAE n'est plus mis en erreur quand on ne récupère pas l'AR au bout d'un certain temps (action de connecteur-type uniquement) #497
- La colonne "entité" est supprimée sur la liste des dossiers (sur la présentation par défaut)
- Retrait de la navigation d'entité qui fait doublon avec le fil d'ariane
- Dépendances Mail et Mail_mime #626
- Modules PHP dans le test du système #626
- Les connecteurs oasis-provisionning, openid-authentication ainsi que le module openid-authentication ont été mis dans l'extension pastell-compat-v2
- Le flux pdf-generique ne propose plus ni l'alimentation via le glaneur glaneur-doc, ni la supression automatique (remplacé par le connecteur de purge) #458
- Les classes spécifiques d'envoi en GED ne doivent plus être utilisées ou largement corrigées pour se baser sur connecteurt-type/GEDEnvoyer car il y a un risque de dépôts multiples si on attrape pas correctement les exceptions émisent par les connecteurs.
- Les connecteurs suivants ont été retirés du cœur de Pastell pour être mis dans l'extension pastell-compat-v2 : ged-ftp, ged-ssh, ged-webdav, smb, cmis, recuperation-fichier-local, recuperation-fichier-ssh, glaneur-local, glaneur-doc, creation-pes-aller, creation-document (la plupart ne sont plus utilisables avec les types de dossier du cœur Pastell) #672
- Les inscriptions "citoyen" et "fournisseur" sont retirées car non-utilisées
- Suppression de la case à cocher "Module activé" dans le connecteur i-Parapheur
- Suppression du champ `ldap_dn` dans le connecteur LDAP, il faut désormais utiliser `ldap_login_attribute` #857
- Suppression du connecteur `message-connexion`, remplacé par le message d'information sur la configuration de la page de connexion #593

## Dépréciations

Les fonctions suivantes sont dépréciées et seront retirées dans une prochaine version majeure
- EntiteContoler::fluxAction()
- FluxEntiteSQL::getAll() 
- FluxEntiteSQL::isUsed()
- Le script redis-flush-all.php est déprécié au profit de general-update.php
- la classe Document au profit de la classe DocumentSQL
- la table collectivite_fournisseur sera retirée dans la prochaine version
- Pour modifier la typologie des actes, il faut passer par le champs externalData `type_piece` et plus par les champs `type_acte` et `type_pj`
- FluxDataStandard à remplacer par FluxDataSedaDefault

# [2.0.15]

## Correction

- Correction de l'arbre des entités incorrectes dans certain cas (backport pastell 3.0) #664
- Les mails textes avec attachement provoquaient l'ajout d'une pièce jointe fantôme sur un serveur Outlook #893 
- Correction pour les appels api patch externalData #905
- Les caractères multioctets pouvaient être tronqués lors de l'envoi au iparapheur #944

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
- Ajout de l'action commune ./action/CommonExtractionAction.php et de la librairie ExtractZipStructure.php #483

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
