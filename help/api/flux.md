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