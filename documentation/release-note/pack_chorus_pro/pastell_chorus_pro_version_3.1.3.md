# V3.1.3 - 2020-12-23

- correction de la version 3.1.2: La liste des factures Chorus récupérées était tronquée #162
- connecteur Chorus Pro: Récupération des factures ayant changé de statut depuis les 30 derniers jours (au lieu de 90 par défaut) #162

# V3.1.2 - 2020-11-16

- correction: il manquait des require pour CPPWrapper #160
- Le critère de récupération et synchronisation des factures `Date de dépôt` est remplacé par `Date de changement de statut` #143
- Ajout de l'utilisateur pour le message quand Chorus renvoi un code HTTP différent de 200 #158

# V3.1.1 - 2020-05-25

- Correction facture-cpp, il manquait les champs modifiables pour les états synchroniser-statut et termine

# V3.1.0 - 2020-04-02

- Adaptation du connecteur PortailFacture CPP pour l'authentification en mode Oauth PISTE #147
    - Prérequis:
        - Inscription PISTE (https://developer.aife.economie.gouv.fr/help-center/guide)
        - Raccordement à Chorus Pro API en mode Oauth (https://communaute.chorus-pro.gouv.fr/documentation/raccordement-a-chorus-pro)
        - Le serveur Pastell accède aux services (URL du service PISTE token, URL du service PISTE api Chorus)
    - Remarques:
        - Le raccordement par certificat est déprécié, l'AIFE permet cette authentification jusqu'à fin 2020
        - Si des éléments de l'authentification PISTE sont renseignés (par entité ou au niveau du connecteur global) alors ce sont eux qui sont pris en compte

- Récupération des factures de l'espace factures de travaux pour MOE, MOA #97
    - Prérequis:
        - Etre en raccordement en mode Oauth PISTE
        - Sélectionner le rôle de l'utilisateur MOE ou MOA
    - Remarques:
        - Les factures de l'espace factures de travaux sont intégrées comme les factures de l'espace reçues, via "récupérer et synchroniser les factures". Elles sont également synchronisées de la même manière.
        - Leur traitement spécifique n'est pas prévu (changement d'espace de mise à disposition, de statut, déposer, soumettre)

- PISTE API Chorus RECHERCHER_STRUCTURE: Au moins un des attributs critères de structure doit être renseigné #147
- Correction "Numéro de mandat envoyé lors de la demande en statut cible MANDATEE" #152
- Possibilité d'utiliser un serveur proxy pour accéder au portail Chorus Pro #153

# V3.0.3 - 2020-02-10

- PISTE API Chorus TRAITER_FACTURE_RECUE: le motif est tronqué à 255 caractères lors de la demande de modification de statut sur Chorus Pro #150
- Ajout du statut MISE_A_DISPOSITION dans la liste des statuts cibles autorisés #140

# V3.0.2 - 2020-01-14

*** Cette version nécessite la version 3.0.2 de Pastell ***

- PISTE API Chorus TRAITER_FACTURE_RECUE: Lorsqu'il n'y a pas de motif il doit être renseigné à '' #149
- L'action FactureCPPEnvoiGED hérite de GEDEnvoyer du connecteur type. Le "Format du nom du répertoire à créer pour le dépot en GED" n'est plus dans le connecteur parametrage-flux-facture-cpp, il est à renseigner au niveau du connecteur de dépôt #125
- Un nouvel onglet "Retour GED" est disponible après l'envoi en GED avec les connecteurs `depot-cmis` et `depot-pastell`, il affiche les identifiants des documents déposés sur la GED #125
- Les actions d'envoi et de récupération du parapheur héritent du connecteur type (nécessite Pastell 3.0.2: SignatureRecuperation: récupérer les iparapheur_metadata_sortie #971) #144
- Retour parapheur: afficher le dernier message et l'identifiant du dossier sur le parapheur #144
- Les actions d'envoi et de récupération SAE héritent du connecteur type #146
- Affichage du commentaire du SAE sur l'accusé de reception et sur la réponse ainsi que de l'identiant de l'archive #146

# V3.0.1 - 2019-11-18

*** Cette version nécessite de passer le script installation/reindex-document.php facture-cpp date_depot (release-note version-2.3.0-ou-3.0.1) ***

- La boucle de synchronisation générait trop d'appel API. #142
- Adaptation pour l'utilisation du glaneur-sftp pour alimenter le type de dossier facture-cpp par fichier pivot.xml (il faut appeler l'action importation-glaneur-pivot) #141

# V3.0.0 - 2019-10-28

*** Cette version nécessite la version 3.0 de Pastell ***

- Ajout des editable-content manquants #133
- Correction pour les appels api patch externalData #137
- Suppression du glaneur-pivot (les connecteurs CreationDocument et RecuperationFichier ne sont plus dans le coeur Pastell) #141
  Il faudra utiliser le glaneur-sftp (prochaine version)

# V2.2.1 - 2019-10-04

* Facture Chorus Pro:
    - permettre l'envoi en ged lorsque le document est en état send-ged-annule #126
    - glaneur-pivot: notifier l'ADMIN en cas d'arrêt de récupération #127
    - glaneur-pivot: ajouter l'option LIBXML_PARSEHUGE pour verifIsFormatPivot #128
* Facture Chorus Fournisseur:
    - param-chorus-fournisseur, nommage des champs: le yml interprète '-' en '_' #123
    - Envoi automatique mais pas systématique au SAE #124
* Connecteur global Chorus-Pro:
    - ajout de "Vérifier la connectivité des connecteurs Chorus Pro" #132


# V2.2.0 - 2019-04-11

***Cette version nécessite la version 2.0.12 de Pastell***

* Facture Chorus Pro:
    - synchroniser des factures en statut terminal même si le statut est vide #113
    - conserver la possibilité de changement de statut en état terminé #120
* Facture-chorus-fournisseur:
    - conserver fichier_facture_pdf original (et fichier_facture_pdf extrait du pivot) #114
    - il manquait une notification 'termine' #115
    - la synchronisation s'arrête si la facture ne change pas de statut pendant 30 jours (par défaut) #105

# V2.1.1 - 2019-03-26

## Correction
* La récupération des factures n'était plus opérationnelle pour les gros volumes.
* Correction pour le connecteur FakeCPP qui n'était plus opérationnel
* La sélection de service n'était pas possible dans le connecteur CPP #107
* Test que le statut courant dans l'historique de la facture fait partie de la liste des statuts #106
* Flux Facture Chorus Pro: Passage en état termine en fin de cheminement automatique #108
* Ajout d'un état "Versé à l'entrepôt annulé" lorsque le dépôt en GED n'est pas permis par le statut de la facture #108
* Flux Facture Chorus fournisseur: Envoi automatique au SAE après l'état "Terminé" #105
* Correction flux `statut-facture-cpp` : Il faut **impérativement** utiliser l'élément `CPPFactureStatuts` bien que l'élement `CPPFluxStatuts` soit autorisé par le schéma.

# V2.1.0 - 2019-01-04

## Ajout

* Création du flux `statut-facture-cpp` qui permet de modifier le statut d'une facture déjà présente sur chorus à partir du schéma CPPStatutPivot

# V2.0.11 - 2018-11-15

* flux facture-cpp: suppression de l'inscription dans le journal de la synchronisation #101
* flux facture-cpp: extraction du nom du destinataire du fichier pivot pour l'archivage (Profil_facture_Chorus_v1.2.0) #104

# V2.0.9 - 2018-09-17

* Correction flux chorus: on ne récupérait pas correctement l'erreur quand il y avait une erreur de recupération de bordereau sur le i-parapheur
* Log des requêtes et des temps de réponses de Chorus
* Utilisation de ConsulterCRDetaille afin d'analyser plus facilement les rejets de facture fournisseur par Chorus

# V2.0.8 - 2018-08-17

* Connecteur Chorus: Par défaut on ne récupère que les factures déposées depuis les 90 derniers jours (nouveau paramètre du connecteur) #93

# V2.0.7 - 2018-07-19

* Correction: les PJ zippées contenues dans le pivot.xml ne sont pas toujours nommées tel que dans la balise "NomPJ" #95
* Possibilité de supprimer les documents en fatal-error
* Ajout de "Récupérer et synchroniser les factures en asynchrone" #94
* Affichage de l'ordre du cheminement

# V2.0.6  - 2018-06-11

* Récupération des annexes de sortie du connecteur de signature pour le flux facture Chorus Pro
* Flux facture-chorus-fournisseur: Ajout de IN_DP_E2_CII_FACTURX pour la Syntaxe flux XML

# V2.0.5

* Correction d'une erreur sur le flux facture-chorus-fournisseur qui empechait le OnChange lors de la modification via l'api
* flux facture-cpp: ajout d'un test format pdf pour l'envoi au i-parapheur des pièces jointes

# V2.0.4

* Correction: Ajout de MISE_A_DISPOSITION_COMPTABLE pour la synchronisation en automatique des factures déja présentes sur Pastell
* Correction du test d'égalité de l'id_facture_cpp (cas où les factures recyclées peuvent être réintégrées sur la même entité)
* Adaptation des flux Facture Chorus Pro et Facture Chorus Fournisseur pour l'envoi au SAE
* Ajout de la récupération du fichier Pivot pour le flux Facture Chorus Fournisseur
* Correction d'un problème de droits lors de la purge automatique des factures
* Flux "Facture Chorus Fournisseur" - Ajouter une action pre-deposer-xml qui permette l'automatisation asynchrone de l'envoi

# V2.0.3

* Ajout du champ "Numéro de marché"
* Ajout du connecteur Chorus par CSV de type PortailFacture
* Ajout d'un timeout à 60 secondes sur les appels chorus
* Correction: La liste des sous type iParapheur n'était pas ramené (régression V2)

# V2.0.2

* Ajout du champ "La GF a indiqué avoir récupéré la facture"
* Optimisation de l'algorithme de l'action d'importation des factures
* Numéro de mandat envoyé lors de la demande en statut cible MANDATEE
