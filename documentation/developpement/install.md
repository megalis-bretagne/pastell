# Installation de l'environnement de développement avec Docker



```bash
cp .env.exemple .env
docker login gitlab.libriciel.fr:4567
docker-compose run  --entrypoint "composer install" web     
docker-compose up -d
```

 
Accès au site : 
- https://localhost:8443
- login : cf .env
- mot de passe : cf .env

Accès à PhpMyAdmin:
- http://localhost:8001






# Lancer les tests d'intégrations
```bash
docker-compose exec web composer test
```