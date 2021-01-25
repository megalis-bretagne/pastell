# Docker CAS


Pour récupérer le certificat du serveur CAS à mettre dans le connecteur :

```bash
openssl s_client -connect 127.0.0.1:9092 2>/dev/null </dev/null |  sed -ne '/-BEGIN CERTIFICATE-/,/-END CERTIFICATE-/p' > /tmp/cert.pem
```

Si l'authentification reste impossible, le plus simple est de ne plus vérifier le certificat en remplaçant la ligne :
```
phpCAS::setCasServerCACert($this->ca_file);
```
par 
```
phpCAS::setNoCasServerValidation();
```

dans `CASAuthentication::setClient()`


# Configuration du connecteur

* Serveur CAS : ip locale (192.168.x.x)
* Port : 9092
* Context : /cas

# Users

* admin / admin
