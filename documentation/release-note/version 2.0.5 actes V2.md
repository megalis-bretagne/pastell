# Intégration des actes V2

## Ajout de l'envoi papier

```bash
curl -k -X PATCH -u admin:admin https://192.168.1.10:8443/api/v2/entite/10/document/7XShfm3 -d'document_papier=1'
```


## Typologie


### Récupérer la typologie du document courant

```bash
curl -k -X GET -u admin:admin https://192.168.1.10:8443/api/v2/entite/10/document/7XShfm3/externalData/type_piece
```

```json
{
    "actes_type_pj_list": {
        "41_NC": "Notification de cr\u00e9ation ou de vacance de poste",
        "41_DE": "D\u00e9lib\u00e9ration \u00e9tablissant la liste de postes \u00e0 pourvoir",
        "41_CA": "Avis de commission administrative paritaire",
        "41_IC": "Information du centre de gestion",
        "41_AT": "Attestation",
        "41_CM": "Avis de la commission mixte paritaire",
        "99_AI": "Acte individuel",
        "99_AU": "Autre Document"
    },
    "pieces": [
        "actes_principale.pdf",
        "annexe1.pdf",
        "annexe2.pdf"
    ]
}
```

Le résultat contient deux tableaux : 


- actes_type_pj_list : la liste des types possibles avec l'identifiant à envoyer au Tdt en clé et le libéllé en valeur
- pieces : la liste des pièces à typer, la première pièce est la pièce principale, les suivantes sont les annexes

### Ajouter la typologie

````bash
curl -k -X PATCH -u admin:admin https://192.168.1.10:8443/api/v2/entite/10/document/7XShfm3/externalData/type_piece -d'type_pj[]=99_AI' -d'type_pj[]=99_AU' -d'type_pj[]=41_NC'
````
