# Instance de connecteurs

## Liste des instances de connecteurs

Toutes les instances de connecteurs de la plateforme :
```
http://localhost/phpstorm/pastell/web/api/v2/Connecteur/all
```

Toutes les instances de connecteurs de la plateforme d'un connecteur particulier:
```
http://localhost/phpstorm/pastell/web/api/v2/Connecteur/all/s2low
```


Toutes les instances de connecteurs globaux :
```
http://localhost/phpstorm/pastell/web/api/v2/Connecteur
```

Tous les instances de connecteurs d'une entité :
```
http://localhost/phpstorm/pastell/web/api/v2/Connecteur/1
```



## Créer un connecteur

```
POST http://localhost/api/v2/Entite/1/Connecteur/
```
Entré : libelle, id_connecteur

## Modifier les propriétés d'un connecteur
 
```
PATCH /Entite/1/Connecteur/$id_ce_creation/content/  
```

## Voir les valeurs possible d'un élement de type external Data
```
GET /Entite/1/Connecteur/42/externalData/foo
```

## Modifier un champs de type external data 
```
PATCH /Entite/1/Connecteur/$id_ce_creation/externalData/connecteur_recup
```

## Poster un fichier

```
POST http://localhost/api/v2/Entite/1/Connecteur/42/classification
```

## Récupérer un fichier
```
GET /Entite/1/Connecteur/42/file/classification
```