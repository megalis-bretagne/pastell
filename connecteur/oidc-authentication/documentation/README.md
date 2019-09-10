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
            - ./ci-resources/keycloak_realmWA.json:/tmp/keycloak.json
        ports:
            - 9090:8080
```

Voir : https://gitlab.libriciel.fr/webACTES/webACTES/tree/master
