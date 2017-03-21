# Journal

## Liste des événements

Renvoi la liste des évenements

```
GET /journal
```

Paramètre :

tous les paramètre sont optionnels

- id_e Identifiant de l'entité (retourné par list-entite)
- recherche Champs de recherche sur le contenu du message horodaté
- id_user Identifiant de l'utilisateur
- date_debut Date à partir de laquelle les informations sont récupérées.
- date_fin Date au delà de laquelle les informations ne sont plus récupérées.
- id_d Identifiant du document
- type Type de document (retourné par document-type.php)
- format Format du journal : json(par défaut) ou bien csv
- offset Numéro de la première ligne à retourner (0 par défaut)
- limit Nombre maximum de lignes à retourner (100 par défaut)
- csv_entete_colonne indique si on doit afficher l'entete des colonne (false par défaut)

Réponse:

```
[
    {
        "id_j": "8060",
        "type": "1",
        "id_e": "1",
        "id_u": "1",
        "id_d": "dXceksg",
        "action": "verif-iparapheur",
        "message": "V\u00e9rification du retour iparapheur",
        "date": "2017-03-19 19:14:15",
        "date_horodatage": "2017-03-19 19:14:15",
        "message_horodate": "1 - 1 - 1 - dXceksg - verif-iparapheur - V\u00e9rification du retour iparapheur - 2017-03-19 19:14:15 - helios-generique",
        "document_type": "helios-generique",
        "titre": "TET3",
        "denomination": "Bourg-en-Bresse",
        "nom": "Pommateau",
        "prenom": "Eric",
        "siren": "120316427",
        "document_type_libelle": "Helios (g\u00e9n\u00e9rique)",
        "action_libelle": "V\u00e9rification de la signature"
    },
    {
        "id_j": "8059",
        "type": "7",
        "id_e": "1",
        "id_u": "1",
        "id_d": "dXceksg",
        "action": "Consult\u00e9",
        "message": "Eric Pommateau a consult\u00e9 le document",
        "date": "2017-03-19 19:14:10",
        "date_horodatage": "2017-03-19 19:14:10",
        "message_horodate": "7 - 1 - 1 - dXceksg - Consult\u00e9 - Eric Pommateau a consult\u00e9 le document - 2017-03-19 19:14:10 - helios-generique",
        "document_type": "helios-generique",
        "titre": "TET3",
        "denomination": "Bourg-en-Bresse",
        "nom": "Pommateau",
        "prenom": "Eric",
        "siren": "120316427",
        "document_type_libelle": "Helios (g\u00e9n\u00e9rique)",
        "action_libelle": "Consult\u00e9"
    }
]
```

Champs de la réponse: 

- id_j Numéro unique, auto-incrémentiel et sans trou du journal
- type
    1. Action sur un document
    2. Notification
    3. Modification d'une entité
    4. Modification d'un utilisateur
    5. Mail sécurisé
    6. Connexion
    7. Consultation d'un document"
- id_e Identifiant de l'entité
- id_u Identifiant de l'utilisateur
- id_d Identifiant du document
- action Action effectuée
- message Message
- date Date de l'ajout dans le journal (peut-être différents de l'horodatage)
- date_horodatage Date récupéré dans le jeton d'horodatage.
- message_horodate Message qui a été horodaté
- titre Titre du document
- document_type Type du document
- denomination Nom de l'entité
- nom Nom de l'utilisateur
- prenom Prénom de l'utilisateur

## Détail d'un événement

```
GET /journal/:id_j
```

Récupère l'ensemble des informations sur le journal. Le jeton d'horodatage (champs preuve) est encodé au format base64.

## Jeton d'horodatage

```
GET /journal/:id_j/jeton
```

Permet de récupérer le jeton au format TSR.









