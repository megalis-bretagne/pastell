# Version

Retourne des informations sur la version de Pastell. Renvoie `200 OK` pour le utilisateurs identifiés.

```
GET /version
```

```
curl -u admin:admin https://pastell.example.com/api/v2/version
``` 

Exemple de réponse :
```json
{
    "version": "2.0.0",
    "revision": "1791",
    "last_changed_date": "$LastChangedDate: 2016-06-20 20:54:03 +0200 (Mon, 20 Jun 2016) $",
    "extensions_versions_accepted": [
        "2.0.0"
    ],
    "version_complete": "Version 2.0.0 - R\u00e9vision  1791"
}
```
