# Entités

# Liste des entités

Liste les entités accessibles via l'utilisateur authentifié

```
GET /entite
``` 

## Détail d'une entité

```
GET /entite/:id_e
``` 


## Création d'une entité

```
POST /entite
```

Paramètre:
- denomination Libellé de l'entité (ex : Saint-Amand-les-Eaux)
- type Le type de l'entité
- siren Numéro SIREN de l'entité (si c'est une collectivité ou un centre de gestion)
- entite_mere Identifiant numérique (id_e) de l'entité mère de l'entité (par exemple pour un service)

	 
## Modification d'une entité

```
PATCH /entite/:id_e
```

## Suppression d'une entité

```
DELETE /entite/:id_e
```
