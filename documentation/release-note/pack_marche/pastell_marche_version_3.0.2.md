## Pastell Marchés

# [3.0.2] - 2020-11-30

- Les action de dépôt en GED se basent sur connecteur-type/GEDEnvoyer car il y a un risque de dépôts multiples si on attrape pas correctement les exceptions émisent par les connecteurs #60
- Un nouvel onglet "Retour GED" est disponible après l'envoi en GED avec les connecteurs `depot-cmis` et `depot-pastell`, il affiche les identifiants des documents déposés sur la GED #73
- piece-marche, retour parapheur: afficher le dernier message #72
- PES marché et Pièces de marché: Lorsque le traitement est terminé il est possible d'utiliser le connecteur de purge pour cocher la case envoi_sae et programmer l'action "Verser au SAE" #59

# [3.0.1] - 2019-10-21

- Correction: Les modifications n'étaient plus possible après l'extraction PES pour le type de dossier pes-marche et après "affectation" pour le type de dossier piece-marche #74

# [3.0.0] - 2019-10-14

- Adaptation Pastell V3

# [1.2.0]

## Ajout

- Dossier de marché:
        - Ajout d'un JSON de métadonnées #52
        - Ajout de mots-clés "libres" (PROFIL_DOSSIERS_MARCHES_LS_V1.2) #63
        - Permettre de spécifier l'information sur le producteur via le fichier JSON (PROFIL_DOSSIERS_MARCHES_LS_V1.2) #64
- Pièces de marché:
        - Ajout de type de pièces liées à l'éxécution #49
        - Contrôle "Nombre décimal sans espace" du Montant (estimatif ou notifié) #38
        - Ajout de journal.json pour l'envoi en GED et SAE (PROFIL_PIECES_MARCHES_LS_V.1.4) #4

## Évolution

- Dossier de marché: MAX_RECURSION_LEVEL passe de 10 à 20. Au delà de 20 niveaux de sous-dossiers la génération du bordereau génère une erreur (PROFIL_DOSSIERS_MARCHES_LS_V1.1) #47
- Pièces par étape: dézippage sur onchange Fichier ZIP des pièces (À partir de Pastell3) #31
- Pièces de marché:
        - Primo-signature détachée peut être multiple (PROFIL_PIECES_MARCHES_LS_V.1.3) #55
        - Envoie des signatures détachées par mail sécurisé #39
- PES marché et Pièces de marché: Lorsque le traitement est terminé il est possible d'utiliser le connecteur de purge pour cocher la case envoi_sae et programmer l'action "Verser au SAE" (si le document n'a pas été envoyé au SAE). (À partir de Pastell 2.0.13) #59

## Retrait

- Pièces de marché: "to":"" est supprimé du paramétrage par défaut parametrage-piece-marche.json #49

# [1.1.2] - 2019-01-28

## Correction

- Il manquait la description (contentDescription) dans le profil du "Dossier de marché" #50
- Nettoyage des commentaires des annotations Pastell

# [1.1.1] - 2019-01-03

## Correction

- Typo dans une annotation Pastell empechant le mot clé "Récurrent" d'aparaître correctement #44
- Modification de l'identifiant du champs "recurent" -> "recurrent" sur le flux Dossier de marché

# [1.1.0] - 2018-12-12

## Ajout

- Création du Flux Pièces de marché par étape, celui-ci permet de renseigner plusieurs fichiers de pièce pour une étape du marché. Il est alors possible de typer les pièces et de créer les documents Pièce de Marché correspondant (Pastell version 2.0.10).
- Ajout du flux dossier de marché (archivage) #16 #34
- Ajout des profils annotés directement dans le projet pastell-marché
- Ajout du champs montant dans les flux Pièce de marché et Pièce de marché par étape #14
- Ajout des types de pièces "Rapport de Présentation" et "Récépissé de dépôt de pli" #12 #19

## Évolution

- Utilisation de la progress_bar dans les flux
- Classement des "Types de pièces" par libellé #28
- Contrôle du type de fichier (zip) sur le flux "Dossier de marché" #21
- Modification du commentaire du champ "Fichier ZIP" sur le flux "Dossier de marché" #22
- Le champs "Métadonnées d'affectation et d'envoi au i-Parapheur" devient "Métadonnées complémentaires i-Parapheur" et est déplacé dans l'onglet i-Parapheur #29
- Automatisation dans tous les cas de l'extraction des informations du PES Marché #25
- Le Numéro de consultation devient obligatoire pour les flux Pièces de marché et Pièces de marché par étape à la place du Numéro de marché qui devient facultatif #11
- Les types de marché récurrents (SR) sont maintenant définis par une case à cocher et la classification évolue pour faire apparaître les types de marché TIC, MO et PI #9
- L'étape "Etude préalable" (EP) devient "Estimation des besoins" (EB) afin d'éviter toutes confusion avec le document juridique d'étude préalable #27
- Typo sur le flux Pièce de marché et Pièce de marché par étape #13 #26
- Modification de la liste des types de marché : supression de l'appel d'offre européen (redondant avec l'appel d'offre ouvert) et ajout des marchés subséquents, des procédures concurentielles et des marchés sans mise en concurence #15
- Modification de l'ordre de champs des flux relatifs aux pièces de marchés #23 

## Correction

- Correction d'une typo sur le flux "Dossier de marché" #24 


## Retrait
- Le contenu du champs "Métadonnées d'affectation et d'envoi au i-Parapheur" n'est plus extrait dans le document lors de l'affectation des valeurs par défaut



# [1.0.0] - 2018-09-24

## Ajout

- Flux Pièces de marché
    - Création du flux par copie de pdf-generique
    - Le cheminement est définit suivant le type_piece_marche (connecteur parametrage-flux-piece-parche). Action affectation
    - Nouveaux champs: type_marche, type_consultation, etape, soumissionnaire, numero_consultation
    - Nouveau champ "Champ libre" (metadonnee_iparapheur) pour envoyer une metadonnée supplémentaire au i-Parapheur
    - Possibilité d'ajouter un fichier json_metadata pour renseigner des métadonnées
    - Adaptation pour glaneur (Pastell version 2.0.6): Actions importation, importation-sans-envoi, importation-erreur
    - Adaptation pour la co-signature détachées (Pastell version 2.0.7 et i-Parapheur 4.6). Si la signature détachée n'est pas xml, alors elle est pkcs7.
    - Adaptation pour la génération du bordereau SEDA 1.0 et le versement As@lae
- Flux PES marché
    - Création du flux par copie de helios-generique
    - Extraction des pièces justificatives du PES Aller
    - Cheminement automatique
    - Adaptation pour glaneur (Pastell version 2.0.6): Actions importation, importation-sans-envoi, importation-erreur
    - Adaptation pour la génération du bordereau SEDA 1.0 et le versement As@lae
    