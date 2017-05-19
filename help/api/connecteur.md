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


## Poster un fichier

```
POST http://localhost/api/v2/Entite/1/Connecteur/42/classification
```

## Récupérer un fichier
```
GET /Entite/1/Connecteur/42/file/classification
```