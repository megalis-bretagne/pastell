# Utilisateurs

## Liste des utilisateurs


```
GET /utilisateur?id_e=0
```

Paramètres : 

- id_e Identifiant de l'entité (0 par défaut)

Sortie :
```
[
    {
        "id_u": "1",
        "login": "admin",
        "email": "eric@sigmalis.com"
    },
    {
        "id_u": "2",
        "login": "col1",
        "email": "col1@sigmalis.com"
    },
    {
        "id_u": "3",
        "login": "fournisseur1",
        "email": "eric@sigmalis.com"
    }
]   
```


- id_u identifiant de l'utilisateur
- login
- email

## Détail d'un utilisateur

```
GET /utilisateur/:id_u
```

Résultat :
```
{
    "id_u": "1",
    "login": "admin",
    "nom": "Pommateau",
    "prenom": "Eric",
    "email": "eric@sigmalis.com",
    "certificat": "",
    "id_e": "0"
}
```


## Création d'un utilisateur

```
POST /utilisateur
```


```
 curl -u admin:admin http://localhost/phpstorm/pastell/web/api/v2/utilisateur -X POST -d "id_e=0&login=toto&nom=toto&prenom=eric&email=toto@sigmalis.com&password=toto" -D-
```

Paramètre:


- login requis Login de l'utilisateur
- password requis Mot de passe de l'utilisateur
- prenom requis Prénom de l'utilisateur
- nom requis Nom de l'utilisateur
- id_e Identifiant de la collectivité de base de l'utilisateur (défaut 0)
- email Email de l'utilisateur



## Modification d'un utilisateur

```
PATCH /utilisateur/:id_u
```


## Supression d'un utilisateur

```
DELETE /utilisateur/:id_u
```


# Rôles associés à un utilisateur

## Liste des rôles

```
GET /utilisateur/:id_u/role
```

```
[
    {
        "id_u": "2",
        "role": "aucun droit",
        "id_e": "0"
    },
    {
        "id_u": "2",
        "role": "admin",
        "id_e": "1"
    }
]
```


## Ajouter un rôle

```
POST /utilisateur/:id_u/role
```

Paramètre:

- role  Role à ajouter
- id_e  Identifiant de l'entité

## Supprimer un rôle


```
DELETE /utilisateur/:id_u/role?role=?&id_e=?
```

```
curl -u admin:admin "http://localhost/phpstorm/pastell/web/api/v2/utilisateur/1/role?role=citoyen&id_e=1" -X DELETE 
```

Paramètre:

- role  Role à ajouter
- id_e  Identifiant de l'entité