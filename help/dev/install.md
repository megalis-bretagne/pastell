# Installation de l'environnement de d�veloppement

cd [SOURCE DE PASTELL]

```bash
docker-compose up -d
docker-compose exec web php /var/www/pastell/ci-resources/init-docker.php
```

Acc�s au site : 
- http://localhost:8000
- login : admin
- mot de passe : admin

Acc�s � PhpMyAdmin:
- http://localhost:8001



