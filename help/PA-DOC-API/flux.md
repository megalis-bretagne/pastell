# Flux

## Liste des flux

Récupérer la liste des flux disponibles sur la plateforme et accessible à l'utilisateur connecté.

```
GET /flux
```

Exemple de réponse:

```json
{
    "actes-generique": {
        "type": "Flux G\u00e9n\u00e9raux",
        "nom": "Actes (g\u00e9n\u00e9rique)"
    },
    "helios-generique": {
        "type": "Flux G\u00e9n\u00e9raux",
        "nom": "Helios (g\u00e9n\u00e9rique)"
    },
    "mailsec": {
        "type": "Flux G\u00e9n\u00e9raux",
        "nom": "Mail s\u00e9curis\u00e9"
    },
    "mailsec-destinataire": {
        "type": "Flux G\u00e9n\u00e9raux",
        "nom": "Mail s\u00e9curis\u00e9 (destinataire)"
    },
    "test": {
        "type": "Flux G\u00e9n\u00e9raux",
        "nom": "Test"
    },
    "changement-email": {
        "type": "Flux d'administration",
        "nom": "Changement d'email"
    },
    "actes-cdg": {
        "type": "Flux CDG",
        "nom": "Actes (CDG)"
    }
}
```

## Détail d'un flux

Récupère les informations sur un flux

```
GET /flux/:id_flux
``` 

```
curl -u admin:admin https://pastell.example.com/api/v2/flux/mailsec
```

Exemple de réponse:

```json
{
    "to": {
        "name": "Destinataire(s)",
        "requis": true,
        "type": "mail-list",
        "autocomplete": "MailSec\/getContactAjax"
    },
    "cc": {
        "name": "Copie \u00e0",
        "type": "mail-list",
        "autocomplete": "MailSec\/getContactAjax"
    },
    "bcc": {
        "name": "Copie cach\u00e9e \u00e0",
        "type": "mail-list",
        "autocomplete": "MailSec\/getContactAjax"
    },
    "password": {
        "name": "Mot de passe",
        "type": "password",
        "may_be_null": true
    },
    "password2": {
        "name": "Mot de passe (confirmation)",
        "type": "password",
        "may_be_null": true,
        "is_equal": "password",
        "is_equal_error": "Les mots de passe ne correspondent pas"
    },
    "objet": {
        "name": "Objet",
        "requis": true,
        "title": true
    },
    "message": {
        "name": "Message",
        "type": "textarea"
    },
    "document_attache": {
        "name": "Document attach\u00e9",
        "type": "file",
        "multiple": true
    },
    "key": {
        "no-show": true,
        "name": "key"
    }
}
```


## Action sur un flux

Liste les actions d'un flux

```
GET /flux/:id_flux/action
```

```
curl -u admin:admin https://pastell.example.com/api/v2/flux/mailsec/action
```

Exemple de réponse

```json
{
    "creation": {
        "name-action": "Cr\u00e9er",
        "name": "Cr\u00e9\u00e9",
        "rule": {
            "no-last-action": "",
            "droit_id_u": "mailsec:edition"
        }
    },
    "modification": {
        "name-action": "Modifier",
        "name": "En cours de r\u00e9daction",
        "rule": {
            "last-action": [
                "creation",
                "modification"
            ],
            "role_id_e": "editeur",
            "droit_id_u": "mailsec:edition"
        }
    },
    "supression": {
        "name-action": "Supprimer",
        "name": "Supprim\u00e9",
        "rule": {
            "last-action": [
                "creation",
                "modification"
            ],
            "role_id_e": "editeur",
            "droit_id_u": "mailsec:edition"
        },
        "action-class": "Supprimer"
    },
    "envoi": {
        "name-action": "Envoyer",
        "name": "Envoy\u00e9",
        "rule": {
            "last-action": [
                "creation",
                "modification"
            ],
            "role_id_e": "editeur",
            "droit_id_u": "mailsec:edition",
            "document_is_valide": true
        },
        "action-class": "StandardAction",
        "connecteur-type": "mailsec",
        "connecteur-type-action": "MailsecEnvoyer"
    },
    "renvoi": {
        "name-action": "Envoyer \u00e0 nouveau",
        "name": "Renvoy\u00e9",
        "rule": {
            "last-action": [
                "envoi",
                "reception-partielle",
                "reception"
            ],
            "role_id_e": "editeur",
            "droit_id_u": "mailsec:edition",
            "document_is_valide": true
        },
        "action-class": "StandardAction",
        "connecteur-type": "mailsec",
        "connecteur-type-action": "MailsecRenvoyer"
    },
    "reception-partielle": {
        "name": "Re\u00e7u partiellement",
        "rule": {
            "role_id_e": "no-role"
        }
    },
    "reception": {
        "name": "Re\u00e7u",
        "rule": {
            "role_id_e": "no-role"
        }
    }
}
```
