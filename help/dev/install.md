# Installation de l'environnement de développement

cd [SOURCE DE PASTELL]

```bash
docker-compose up -d
docker-compose exec web php /var/www/pastell/ci-resources/init-docker.php
```

Accès au site : 
- http://localhost:8000
- login : admin
- mot de passe : admin

Accès à PhpMyAdmin:
- http://localhost:8001



