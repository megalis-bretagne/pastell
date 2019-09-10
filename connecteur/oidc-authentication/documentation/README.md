# Docker keycloak

```
    keycloak:
        image: jboss/keycloak:4.5.0.Final
        tty: true
        environment:
            - KEYCLOAK_USER=${KEYCLOAK_USER:-admin}
            - KEYCLOAK_PASSWORD=${KEYCLOAK_PASSWORD:-password}
            - KEYCLOAK_IMPORT=${KEYCLOAK_IMPORT:-/tmp/keycloak.json}
        volumes:
            - ./ci-resources/keycloak_pastell.json:/tmp/keycloak.json
        ports:
            - 9090:8080
```

Voir : https://gitlab.libriciel.fr/webACTES/webACTES/tree/master


# Configuration du connecteur

* Provider url (url avant le `.well-known/openid-configuration`):  http://keycloak.tld:port/auth/realms/pastell/
* client id :  `account`
* client secret : `132ff39b-eedd-4fa6-993d-a3ffe63b6b0b`. Sur keycloak : Menu Clients > chosir le client id > onglet Credentials
* Attribut pour le login : `preferred_username` pour keycloak

# Users existants dans le realm pastell

* admin:admin
* s.blanc:Aqwxsz123!er
* m.vert:Aqwxsz123!er
* j.orange:Aqwxsz123!er
* r.violet:Aqwxsz123!er

# Erreurs possibles

## Invalid parameter: redirect_uri

Cela veut dire que l'URL du pastell n'est pas autorisée par le client keycloak utilisé.
Il faut se rendre dans le menu Clients > choisir le client id et ajouter l'url dans le champ "Valid Redirect URIs" : https://pastell.tld/*
